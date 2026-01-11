<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActivityController extends AbstractController
{
    #[Route('/{_locale}/activities', name: 'app_activities', requirements: ['_locale' => 'en|fr|ar'], defaults: ['_locale' => 'en'])]
    public function index(): Response
    {
        return $this->render('activity/index.html.twig');
    }
}


