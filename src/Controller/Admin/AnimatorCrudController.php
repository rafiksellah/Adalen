<?php

namespace App\Controller\Admin;

use App\Entity\Animator;
use App\Form\AnimatorType;
use App\Repository\AnimatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/animator')]
class AnimatorCrudController extends AbstractController
{
    #[Route('/', name: 'app_admin_animator_index', methods: ['GET'])]
    public function index(AnimatorRepository $animatorRepository, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        ['search' => $search, 'active' => $active, 'q' => $q] = $this->resolveAnimatorAdminListFilters($request);

        $total = $animatorRepository->countForAdminList($search, $active);
        $totalPages = max(1, (int) ceil($total / $limit));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $animators = $animatorRepository->findPageForAdminList($offset, $limit, $search, $active);

        return $this->render('admin/animator/index.html.twig', [
            'animators' => $animators,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'searchQuery' => $q,
            'activeFilter' => $active === null ? 'all' : $active,
        ]);
    }

    #[Route('/export', name: 'app_admin_animator_export', methods: ['GET'])]
    public function export(AnimatorRepository $animatorRepository, Request $request): Response
    {
        ['search' => $search, 'active' => $active] = $this->resolveAnimatorAdminListFilters($request);
        $rows = $animatorRepository->findAllForAdminExport($search, $active);
        $actSlug = \in_array($active, ['1', '0'], true) ? '_actif' . $active : '';
        $filename = 'animateurs' . $actSlug . '_' . (new \DateTimeImmutable())->format('Y-m-d_His') . '.csv';

        $response = new StreamedResponse(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                'name',
                'title',
                'description',
                'image',
                'category',
                'is_active',
                'created_at',
                'updated_at',
            ], ';');

            foreach ($rows as $animator) {
                fputcsv($out, [
                    (string) ($animator->getId() ?? ''),
                    (string) ($animator->getName() ?? ''),
                    (string) ($animator->getTitle() ?? ''),
                    (string) ($animator->getDescription() ?? ''),
                    (string) ($animator->getImage() ?? ''),
                    (string) ($animator->getCategory() ?? ''),
                    $animator->isActive() ? '1' : '0',
                    $animator->getCreatedAt() ? $animator->getCreatedAt()->format('Y-m-d H:i:s') : '',
                    $animator->getUpdatedAt() ? $animator->getUpdatedAt()->format('Y-m-d H:i:s') : '',
                ], ';');
            }
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * @return array{search: ?string, active: ?string, q: string}
     */
    private function resolveAnimatorAdminListFilters(Request $request): array
    {
        $q = trim((string) $request->query->get('q', ''));
        $search = $q === '' ? null : $q;
        $activeRaw = (string) $request->query->get('active', 'all');
        if (!\in_array($activeRaw, ['all', '1', '0'], true)) {
            $activeRaw = 'all';
        }
        $active = $activeRaw === 'all' ? null : $activeRaw;

        return ['search' => $search, 'active' => $active, 'q' => $q];
    }

    #[Route('/new', name: 'app_admin_animator_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $animator = new Animator();
        $form = $this->createForm(AnimatorType::class, $animator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animator->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($animator);
            $entityManager->flush();

            $this->addFlash('success', 'L\'animateur a été créé avec succès.');
            return $this->redirectToRoute('app_admin_animator_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/animator/new.html.twig', [
            'animator' => $animator,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_animator_show', methods: ['GET'])]
    public function show(Animator $animator): Response
    {
        return $this->render('admin/animator/show.html.twig', [
            'animator' => $animator,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_animator_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Animator $animator, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnimatorType::class, $animator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animator->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'L\'animateur a été modifié avec succès.');
            return $this->redirectToRoute('app_admin_animator_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/animator/edit.html.twig', [
            'animator' => $animator,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_animator_delete', methods: ['POST'])]
    public function delete(Request $request, Animator $animator, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$animator->getId(), $request->request->get('_token'))) {
            $entityManager->remove($animator);
            $entityManager->flush();
            $this->addFlash('success', 'L\'animateur a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_animator_index', [], Response::HTTP_SEE_OTHER);
    }
}
