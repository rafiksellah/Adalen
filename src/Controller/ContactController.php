<?php

namespace App\Controller;

use App\Entity\ContactRequest;
use App\Form\ContactMessageType;
use App\Model\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        $message = new ContactMessage();
        $form = $this->createForm(ContactMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stored = (new ContactRequest())
                ->setNom((string) $message->getName())
                ->setEmail((string) $message->getEmail())
                ->setSujet((string) $message->getSubject())
                ->setMessage((string) $message->getMessage())
                ->setLocale($request->getLocale());

            try {
                $em->persist($stored);
                $em->flush();
            } catch (\Throwable $e) {
                $this->logger->error('Contact form persist error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $this->addFlash('error', $this->translator->trans('contact.messages.save_error'));

                return $this->redirectToRoute('app_contact', ['_locale' => $request->getLocale()]);
            }

            $mailOk = $this->trySendContactEmails($mailer, $message);
            if (!$mailOk) {
                $this->addFlash('warning', $this->translator->trans('contact.messages.mail_warning'));
            }
            $this->addFlash('success', $this->translator->trans('contact.messages.success'));

            return $this->redirectToRoute('app_contact', ['_locale' => $request->getLocale()]);
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Envoie d’abord l’e-mail visiteur avec Bcc vers la boîte site (contourne souvent le filtrage From=To),
     * puis la notification détaillée à l’équipe.
     *
     * @return bool true si tous les envois ont réussi
     */
    private function trySendContactEmails(MailerInterface $mailer, ContactMessage $message): bool
    {
        $locale = $this->translator->getLocale();
        $ok = true;

        $userEmail = (new Email())
            ->from(self::FROM_EMAIL)
            ->to((string) $message->getEmail())
            ->replyTo(self::NOTIFY_EMAIL)
            ->subject($this->translator->trans('contact.mail.user_subject'))
            ->html($this->renderView('emails/contact_confirmation.html.twig', [
                'contact' => $message,
                'locale' => $locale,
            ]));

        $visitor = strtolower((string) $message->getEmail());
        $notify = strtolower(self::NOTIFY_EMAIL);
        if ($visitor !== $notify) {
            $userEmail->addBcc(self::NOTIFY_EMAIL);
        }

        try {
            $mailer->send($userEmail);
        } catch (\Throwable $e) {
            $ok = false;
            $this->logger->error('Contact user mail error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        $adminEmail = (new Email())
            ->from(self::FROM_EMAIL)
            ->replyTo((string) $message->getEmail())
            ->to(self::NOTIFY_EMAIL)
            ->subject($this->translator->trans('contact.mail.admin_subject', [
                '%subject%' => (string) $message->getSubject(),
            ]))
            ->html($this->renderView('emails/contact_notification.html.twig', [
                'contact' => $message,
                'locale' => $locale,
            ]));

        try {
            $mailer->send($adminEmail);
        } catch (\Throwable $e) {
            $ok = false;
            $this->logger->error('Contact admin mail error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return $ok;
    }
}

