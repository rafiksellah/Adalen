<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnimatorController extends AbstractController
{
    #[Route('/animators', name: 'app_animators')]
    public function index(): Response
    {
        return $this->render('animator/index.html.twig');
    }
}


