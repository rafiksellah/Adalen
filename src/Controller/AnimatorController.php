<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnimatorController extends AbstractController
{
    #[Route('/{_locale}/animators', name: 'app_animators', requirements: ['_locale' => 'en|fr|ar'], defaults: ['_locale' => 'en'])]
    public function index(): Response
    {
        return $this->render('animator/index.html.twig');
    }
}


