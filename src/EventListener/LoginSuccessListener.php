<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessListener implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $request = $event->getRequest();
        
        // Vérifier si l'utilisateur a le rôle ADMIN
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Rediriger vers le dashboard admin
            $response = new RedirectResponse($this->urlGenerator->generate('app_admin_dashboard'));
            $event->setResponse($response);
        } else {
            // Rediriger vers la page d'accueil pour les autres utilisateurs
            $locale = $request->getLocale() ?: 'fr';
            $response = new RedirectResponse($this->urlGenerator->generate('app_home', ['_locale' => $locale]));
            $event->setResponse($response);
        }
    }
}
