<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Animator;
use App\Repository\ActivityRepository;
use App\Repository\AnimatorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ActivityRepository $activityRepository, AnimatorRepository $animatorRepository): Response
    {
        $activities = $activityRepository->findActive();
        $animators = $animatorRepository->findActive();

        return $this->render('home/index.html.twig', [
            'activities' => $activities,
            'animators' => $animators,
        ]);
    }
}


