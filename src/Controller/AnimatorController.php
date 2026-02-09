<?php

namespace App\Controller;

use App\Repository\AnimatorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnimatorController extends AbstractController
{
    #[Route('/{_locale}/animators', name: 'app_animators', requirements: ['_locale' => 'en|fr|ar'])]
    public function index(AnimatorRepository $animatorRepository): Response
    {
        $animators = $animatorRepository->findBy(['isActive' => true], ['name' => 'ASC']);

        return $this->render('animator/index.html.twig', [
            'animators' => $animators,
        ]);
    }
}


