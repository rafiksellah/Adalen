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
        
        $actualities = $actualityRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $total = count($actualityRepository->findAll());
        $totalPages = ceil($total / $limit);
        
        return $this->render('admin/actuality/index.html.twig', [
            'actualities' => $actualities,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
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
