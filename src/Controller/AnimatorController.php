<?php

namespace App\Controller;

use App\Repository\AnimatorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnimatorController extends AbstractController
{
    #[Route('/animators', name: 'app_animators')]
    public function index(AnimatorRepository $animatorRepository): Response
    {
        $animators = $animatorRepository->findActive();
        
        // Grouper par catégorie
        $animatorsByCategory = [];
        foreach ($animators as $animator) {
            $category = $animator->getCategory() ?? 'Autres';
            if (!isset($animatorsByCategory[$category])) {
                $animatorsByCategory[$category] = [];
            }
            $animatorsByCategory[$category][] = $animator;
        }

        return $this->render('animator/index.html.twig', [
            'animatorsByCategory' => $animatorsByCategory,
        ]);
    }
}


