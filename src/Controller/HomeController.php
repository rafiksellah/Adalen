<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/{_locale}', name: 'app_home', requirements: ['_locale' => 'en|fr|ar'], defaults: ['_locale' => 'en'])]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/{_locale}/la-petite-coop', name: 'app_coop', requirements: ['_locale' => 'en|fr|ar'], defaults: ['_locale' => 'en'])]
    public function coop(): Response
    {
        return $this->render('coop/index.html.twig');
    }

    #[Route('/', name: 'redirect_to_locale')]
    public function redirectToLocale(Request $request): RedirectResponse
    {
        $locale = $request->getPreferredLanguage(['en', 'fr', 'ar']) ?? 'en';
        return $this->redirectToRoute('app_home', ['_locale' => $locale]);
    }
}


