<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Registration;
use App\Form\RegistrationType;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActivityController extends AbstractController
{
    #[Route('/activities', name: 'app_activities')]
    public function index(ActivityRepository $activityRepository): Response
    {
        $activities = $activityRepository->findActive();

        return $this->render('activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    #[Route('/activity/{id}/register', name: 'app_activity_register', methods: ['GET', 'POST'])]
    public function register(
        Activity $activity,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $registration = new Registration();
        $registration->setActivity($activity);

        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($registration);
            $entityManager->flush();

            $this->addFlash('success', 'Votre inscription a été enregistrée avec succès ! Nous vous contacterons bientôt.');

            return $this->redirectToRoute('app_activities');
        }

        return $this->render('activity/register.html.twig', [
            'activity' => $activity,
            'form' => $form,
        ]);
    }
}

