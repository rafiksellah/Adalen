<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/{_locale}/contact', name: 'app_contact', requirements: ['_locale' => 'en|fr|ar'], defaults: ['_locale' => 'en'])]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }
}


