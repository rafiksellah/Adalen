<?php

namespace App\Controller;

use App\Repository\ActualityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/{_locale}', name: 'app_home', requirements: ['_locale' => 'en|fr|ar'])]
    public function index(Request $request, ActualityRepository $actualityRepository): Response
    {
        // Récupérer la dernière actualité publiée (peu importe la langue, elle sera traduite)
        $latestActuality = $actualityRepository->findOneBy(
            ['isPublished' => true],
            ['createdAt' => 'DESC']
        );

        return $this->render('home/index.html.twig', [
            'latestActuality' => $latestActuality,
        ]);
    }

    #[Route('/{_locale}/la-petite-coop', name: 'app_coop', requirements: ['_locale' => 'en|fr|ar'])]
    public function coop(): Response
    {
        return $this->render('coop/index.html.twig');
    }

    #[Route('/', name: 'redirect_to_locale')]
    public function redirectToLocale(Request $request): RedirectResponse
    {
        return $this->redirect('/en');
    }
}


