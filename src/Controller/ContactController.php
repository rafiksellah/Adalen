<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactType;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $contactMessage = new ContactMessage();
        $form = $this->createForm(ContactType::class, $contactMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Protection anti-spam : vérifier le honeypot
            if (!empty($contactMessage->getHoneypot())) {
                $this->addFlash('error', 'Erreur lors de l\'envoi du message.');
                return $this->redirectToRoute('app_contact');
            }

            $entityManager->persist($contactMessage);
            $entityManager->flush();

            // Envoyer un email
            try {
                $email = (new Email())
                    ->from($this->getParameter('app.contact_email_from') ?? 'noreply@adalen.com')
                    ->to($this->getParameter('app.contact_email_to') ?? 'montessoriadalen@gmail.com')
                    ->subject('Nouveau message de contact - ' . ($contactMessage->getSubject() ?? 'Sans sujet'))
                    ->html($this->renderView('emails/contact.html.twig', [
                        'contact' => $contactMessage,
                    ]));

                $mailer->send($email);
            } catch (\Exception $e) {
                // Log l'erreur mais continue
            }

            $this->addFlash('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}


