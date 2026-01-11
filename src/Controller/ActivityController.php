<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActivityController extends AbstractController
{
    #[Route('/activities', name: 'app_activities')]
    public function index(): Response
    {
        return $this->render('activity/index.html.twig');
    }
}


