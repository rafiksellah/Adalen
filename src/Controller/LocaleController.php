<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    #[Route('/change-locale/{locale}', name: 'app_change_locale', requirements: ['locale' => 'en|fr|ar'])]
    public function changeLocale(string $locale, Request $request): RedirectResponse
    {
        // Store the locale in the session
        $request->getSession()->set('_locale', $locale);

        // Redirect to the referrer or home page
        $referer = $request->headers->get('referer');
        if ($referer) {
            // Extract the current route and replace the locale
            $path = parse_url($referer, PHP_URL_PATH);
            $pathParts = array_filter(explode('/', trim($path, '/')));
            $pathParts = array_values($pathParts);
            
            // If first part is a locale, replace it
            if (!empty($pathParts) && in_array($pathParts[0], ['en', 'fr', 'ar'])) {
                $pathParts[0] = $locale;
                $newPath = '/' . implode('/', $pathParts);
                if ($request->getQueryString()) {
                    $newPath .= '?' . $request->getQueryString();
                }
                return $this->redirect($newPath);
            }
        }

        // Default redirect to home with new locale
        return $this->redirectToRoute('app_home', ['_locale' => $locale]);
    }
}

