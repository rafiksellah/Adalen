<?php

namespace App\Controller;

use App\Form\ContactMessageType;
use App\Model\ContactMessage;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactController extends AbstractController
{
    private const FROM_EMAIL = 'contact@adalen-dz.com';

    private const NOTIFY_EMAIL = 'contact@adalen-dz.com';

    public function __construct(
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/{_locale}/contact', name: 'app_contact', requirements: ['_locale' => 'en|fr|ar'], defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $message = new ContactMessage();
        $form = $this->createForm(ContactMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->sendContactEmails($mailer, $message);
                $this->addFlash('success', $this->translator->trans('contact.messages.success'));
            } catch (\Throwable $e) {
                $this->logger->error('Contact form mail error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $this->addFlash('error', $this->translator->trans('contact.messages.error'));
            }

            return $this->redirectToRoute('app_contact', ['_locale' => $request->getLocale()]);
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }

    private function sendContactEmails(MailerInterface $mailer, ContactMessage $message): void
    {
        $locale = $this->translator->getLocale();

        $adminEmail = (new Email())
            ->from(self::FROM_EMAIL)
            ->replyTo($message->getEmail())
            ->to(self::NOTIFY_EMAIL)
            ->subject($this->translator->trans('contact.mail.admin_subject', [
                '%subject%' => $message->getSubject(),
            ]))
            ->html($this->renderView('emails/contact_notification.html.twig', [
                'contact' => $message,
                'locale' => $locale,
            ]));

        $mailer->send($adminEmail);

        $userEmail = (new Email())
            ->from(self::FROM_EMAIL)
            ->to($message->getEmail())
            ->replyTo(self::NOTIFY_EMAIL)
            ->subject($this->translator->trans('contact.mail.user_subject'))
            ->html($this->renderView('emails/contact_confirmation.html.twig', [
                'contact' => $message,
                'locale' => $locale,
            ]));

        $mailer->send($userEmail);
    }
}

