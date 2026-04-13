<?php

namespace App\Controller\Admin;

use App\Entity\Actuality;
use App\Form\ActualityType;
use App\Repository\ActualityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/actuality')]
class ActualityCrudController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger
    ) {
    }

    #[Route('/', name: 'app_admin_actuality_index', methods: ['GET'])]
    public function index(ActualityRepository $actualityRepository, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        ['search' => $search, 'published' => $published, 'q' => $q] = $this->resolveActualityAdminListFilters($request);

        $total = $actualityRepository->countForAdminList($search, $published);
        $totalPages = max(1, (int) ceil($total / $limit));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $actualities = $actualityRepository->findPageForAdminList($offset, $limit, $search, $published);

        return $this->render('admin/actuality/index.html.twig', [
            'actualities' => $actualities,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'searchQuery' => $q,
            'publishedFilter' => $published === null ? 'all' : $published,
        ]);
    }

    #[Route('/export', name: 'app_admin_actuality_export', methods: ['GET'])]
    public function export(ActualityRepository $actualityRepository, Request $request): Response
    {
        ['search' => $search, 'published' => $published] = $this->resolveActualityAdminListFilters($request);
        $rows = $actualityRepository->findAllForAdminExport($search, $published);
        $pubSlug = \in_array($published, ['1', '0'], true) ? '_pub' . $published : '';
        $filename = 'actualites' . $pubSlug . '_' . (new \DateTimeImmutable())->format('Y-m-d_His') . '.csv';

        $response = new StreamedResponse(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                'title_fr',
                'title_en',
                'title_ar',
                'description_fr',
                'description_en',
                'description_ar',
                'images',
                'video',
                'is_published',
                'created_at',
                'updated_at',
            ], ';');

            foreach ($rows as $a) {
                fputcsv($out, [
                    (string) ($a->getId() ?? ''),
                    (string) ($a->getTitleFr() ?? ''),
                    (string) ($a->getTitleEn() ?? ''),
                    (string) ($a->getTitleAr() ?? ''),
                    (string) ($a->getDescriptionFr() ?? ''),
                    (string) ($a->getDescriptionEn() ?? ''),
                    (string) ($a->getDescriptionAr() ?? ''),
                    (string) ($a->getImages() ?? ''),
                    (string) ($a->getVideo() ?? ''),
                    $a->isPublished() ? '1' : '0',
                    $a->getCreatedAt() ? $a->getCreatedAt()->format('Y-m-d H:i:s') : '',
                    $a->getUpdatedAt() ? $a->getUpdatedAt()->format('Y-m-d H:i:s') : '',
                ], ';');
            }
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * @return array{search: ?string, published: ?string, q: string}
     */
    private function resolveActualityAdminListFilters(Request $request): array
    {
        $q = trim((string) $request->query->get('q', ''));
        $search = $q === '' ? null : $q;
        $publishedRaw = (string) $request->query->get('published', 'all');
        if (!\in_array($publishedRaw, ['all', '1', '0'], true)) {
            $publishedRaw = 'all';
        }
        $published = $publishedRaw === 'all' ? null : $publishedRaw;

        return ['search' => $search, 'published' => $published, 'q' => $q];
    }

    #[Route('/new', name: 'app_admin_actuality_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $actuality = new Actuality();
        $form = $this->createForm(ActualityType::class, $actuality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les fichiers uploadés
            $imagesFiles = $form->get('images')->getData();
            $videoFile = $form->get('video')->getData();
            
            // Validation : au moins un champ doit être rempli (titre, description, image ou vidéo)
            $hasTitle = !empty($actuality->getTitleFr()) || !empty($actuality->getTitleEn()) || !empty($actuality->getTitleAr());
            $hasDescription = !empty($actuality->getDescriptionFr()) || !empty($actuality->getDescriptionEn()) || !empty($actuality->getDescriptionAr());
            $hasImage = !empty($imagesFiles);
            $hasVideo = !empty($videoFile);
            
            if (!$hasTitle && !$hasDescription && !$hasImage && !$hasVideo) {
                $this->addFlash('error', 'Vous devez remplir au moins un champ : titre, description, image ou vidéo.');
                return $this->render('admin/actuality/new.html.twig', [
                    'actuality' => $actuality,
                    'form' => $form,
                ]);
            }

            // Gestion des images (optionnel)
            if ($imagesFiles) {
                $imagePaths = [];
                foreach ($imagesFiles as $imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $this->slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('kernel.project_dir').'/public/uploads/actualities/images',
                            $newFilename
                        );
                        $imagePaths[] = 'uploads/actualities/images/'.$newFilename;
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : '.$e->getMessage());
                    }
                }
                if (!empty($imagePaths)) {
                    $actuality->setImages(implode(',', $imagePaths));
                }
            }

            // Gestion de la vidéo
            $videoFile = $form->get('video')->getData();
            if ($videoFile) {
                $originalFilename = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$videoFile->guessExtension();

                try {
                    $videoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/actualities/videos',
                        $newFilename
                    );
                    $actuality->setVideo('uploads/actualities/videos/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la vidéo : '.$e->getMessage());
                }
            }

            $actuality->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($actuality);
            $entityManager->flush();

            $this->addFlash('success', 'L\'actualité a été créée avec succès.');
            return $this->redirectToRoute('app_admin_actuality_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/actuality/new.html.twig', [
            'actuality' => $actuality,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_actuality_show', methods: ['GET'])]
    public function show(Actuality $actuality): Response
    {
        return $this->render('admin/actuality/show.html.twig', [
            'actuality' => $actuality,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_actuality_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Actuality $actuality, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActualityType::class, $actuality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les fichiers uploadés
            $imagesFiles = $form->get('images')->getData();
            $videoFile = $form->get('video')->getData();
            
            // Validation : au moins un champ doit être rempli (titre, description, image ou vidéo)
            $hasTitle = !empty($actuality->getTitleFr()) || !empty($actuality->getTitleEn()) || !empty($actuality->getTitleAr());
            $hasDescription = !empty($actuality->getDescriptionFr()) || !empty($actuality->getDescriptionEn()) || !empty($actuality->getDescriptionAr());
            $hasImage = !empty($imagesFiles) || !empty($actuality->getImages());
            $hasVideo = !empty($videoFile) || !empty($actuality->getVideo());
            
            if (!$hasTitle && !$hasDescription && !$hasImage && !$hasVideo) {
                $this->addFlash('error', 'Vous devez remplir au moins un champ : titre, description, image ou vidéo.');
                return $this->render('admin/actuality/edit.html.twig', [
                    'actuality' => $actuality,
                    'form' => $form,
                ]);
            }

            // Gestion des nouvelles images (optionnel)
            if ($imagesFiles) {
                $existingImages = $actuality->getImages() ? explode(',', $actuality->getImages()) : [];
                $newImagePaths = [];
                
                foreach ($imagesFiles as $imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $this->slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('kernel.project_dir').'/public/uploads/actualities/images',
                            $newFilename
                        );
                        $newImagePaths[] = 'uploads/actualities/images/'.$newFilename;
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : '.$e->getMessage());
                    }
                }
                
                // Fusionner les anciennes et nouvelles images
                $allImages = array_merge($existingImages, $newImagePaths);
                $actuality->setImages(implode(',', $allImages));
            }

            // Gestion de la nouvelle vidéo
            $videoFile = $form->get('video')->getData();
            if ($videoFile) {
                // Supprimer l'ancienne vidéo si elle existe
                if ($actuality->getVideo() && file_exists($this->getParameter('kernel.project_dir').'/public/'.$actuality->getVideo())) {
                    unlink($this->getParameter('kernel.project_dir').'/public/'.$actuality->getVideo());
                }

                $originalFilename = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$videoFile->guessExtension();

                try {
                    $videoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/actualities/videos',
                        $newFilename
                    );
                    $actuality->setVideo('uploads/actualities/videos/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la vidéo : '.$e->getMessage());
                }
            }

            $actuality->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'L\'actualité a été modifiée avec succès.');
            return $this->redirectToRoute('app_admin_actuality_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/actuality/edit.html.twig', [
            'actuality' => $actuality,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/image/delete', name: 'app_admin_actuality_image_delete', methods: ['POST'])]
    public function deleteImage(Request $request, Actuality $actuality, EntityManagerInterface $entityManager): JsonResponse
    {
        $image = (string) $request->request->get('image');
        $token = (string) $request->request->get('_token');

        if (!$image || !$this->isCsrfTokenValid('delete_image'.$actuality->getId().$image, $token)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $images = $actuality->getImages() ? array_map('trim', explode(',', $actuality->getImages())) : [];
        $images = array_values(array_filter($images, fn (string $img) => $img !== $image));

        $filePath = $this->getParameter('kernel.project_dir').'/public/'.trim($image);
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        $actuality->setImages(!empty($images) ? implode(',', $images) : null);
        $actuality->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/video/delete', name: 'app_admin_actuality_video_delete', methods: ['POST'])]
    public function deleteVideo(Request $request, Actuality $actuality, EntityManagerInterface $entityManager): JsonResponse
    {
        $token = (string) $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete_video'.$actuality->getId(), $token)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }

        if ($actuality->getVideo()) {
            $filePath = $this->getParameter('kernel.project_dir').'/public/'.$actuality->getVideo();
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $actuality->setVideo(null);
        $actuality->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}', name: 'app_admin_actuality_delete', methods: ['POST'])]
    public function delete(Request $request, Actuality $actuality, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actuality->getId(), $request->request->get('_token'))) {
            // Supprimer les fichiers associés
            if ($actuality->getImages()) {
                $imagePaths = explode(',', $actuality->getImages());
                foreach ($imagePaths as $imagePath) {
                    $filePath = $this->getParameter('kernel.project_dir').'/public/'.trim($imagePath);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            
            if ($actuality->getVideo()) {
                $filePath = $this->getParameter('kernel.project_dir').'/public/'.$actuality->getVideo();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $entityManager->remove($actuality);
            $entityManager->flush();
            $this->addFlash('success', 'L\'actualité a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_admin_actuality_index', [], Response::HTTP_SEE_OTHER);
    }
}
