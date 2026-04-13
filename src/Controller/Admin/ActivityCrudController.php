<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/activity')]
class ActivityCrudController extends AbstractController
{
    #[Route('/', name: 'app_admin_activity_index', methods: ['GET'])]
    public function index(ActivityRepository $activityRepository, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        ['search' => $search, 'active' => $active, 'q' => $q] = $this->resolveActivityAdminListFilters($request);

        $total = $activityRepository->countForAdminList($search, $active);
        $totalPages = max(1, (int) ceil($total / $limit));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $activities = $activityRepository->findPageForAdminList($offset, $limit, $search, $active);

        return $this->render('admin/activity/index.html.twig', [
            'activities' => $activities,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'searchQuery' => $q,
            'activeFilter' => $active === null ? 'all' : $active,
        ]);
    }

    #[Route('/export', name: 'app_admin_activity_export', methods: ['GET'])]
    public function export(ActivityRepository $activityRepository, Request $request): Response
    {
        ['search' => $search, 'active' => $active] = $this->resolveActivityAdminListFilters($request);
        $rows = $activityRepository->findAllForAdminExport($search, $active);
        $actSlug = \in_array($active, ['1', '0'], true) ? '_actif' . $active : '';
        $filename = 'activites' . $actSlug . '_' . (new \DateTimeImmutable())->format('Y-m-d_His') . '.csv';

        $response = new StreamedResponse(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                'name',
                'description',
                'icon',
                'age_range',
                'price',
                'number_of_classes',
                'duration',
                'image',
                'is_active',
                'created_at',
                'updated_at',
            ], ';');

            foreach ($rows as $activity) {
                fputcsv($out, [
                    (string) ($activity->getId() ?? ''),
                    (string) ($activity->getName() ?? ''),
                    (string) ($activity->getDescription() ?? ''),
                    (string) ($activity->getIcon() ?? ''),
                    (string) ($activity->getAgeRange() ?? ''),
                    (string) ($activity->getPrice() ?? ''),
                    $activity->getNumberOfClasses() !== null ? (string) $activity->getNumberOfClasses() : '',
                    (string) ($activity->getDuration() ?? ''),
                    (string) ($activity->getImage() ?? ''),
                    $activity->isActive() ? '1' : '0',
                    $activity->getCreatedAt() ? $activity->getCreatedAt()->format('Y-m-d H:i:s') : '',
                    $activity->getUpdatedAt() ? $activity->getUpdatedAt()->format('Y-m-d H:i:s') : '',
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
    private function resolveActivityAdminListFilters(Request $request): array
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

    #[Route('/new', name: 'app_admin_activity_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($activity);
            $entityManager->flush();

            $this->addFlash('success', 'L\'activité a été créée avec succès.');
            return $this->redirectToRoute('app_admin_activity_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/activity/new.html.twig', [
            'activity' => $activity,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_activity_show', methods: ['GET'])]
    public function show(Activity $activity): Response
    {
        return $this->render('admin/activity/show.html.twig', [
            'activity' => $activity,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_activity_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activity $activity, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'L\'activité a été modifiée avec succès.');
            return $this->redirectToRoute('app_admin_activity_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_activity_delete', methods: ['POST'])]
    public function delete(Request $request, Activity $activity, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$activity->getId(), $request->request->get('_token'))) {
            $entityManager->remove($activity);
            $entityManager->flush();
            $this->addFlash('success', 'L\'activité a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_admin_activity_index', [], Response::HTTP_SEE_OTHER);
    }
}
