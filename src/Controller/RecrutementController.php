<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Form\JobApplicationType;
use App\Repository\JobApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecrutementController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/{_locale}/recrutement', name: 'app_recrutement', requirements: ['_locale' => 'en|fr|ar'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        SluggerInterface $slugger
    ): Response {
        $jobApplication = new JobApplication();
        $form = $this->createForm(JobApplicationType::class, $jobApplication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Gérer l'upload des fichiers
                $cvFile = $form->get('cvFilename')->getData();
                $motivationFile = $form->get('motivationFilename')->getData();

                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $cvDir = $uploadsDir . '/cv';
                $motivationDir = $uploadsDir . '/motivation';
                
                if (!is_dir($cvDir)) {
                    mkdir($cvDir, 0755, true);
                }
                if (!is_dir($motivationDir)) {
                    mkdir($motivationDir, 0755, true);
                }

                if ($cvFile) {
                    $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $cvFile->guessExtension();

                    try {
                        $cvFile->move($cvDir, $newFilename);
                        $jobApplication->setCvFilename('uploads/cv/' . $newFilename);
                    } catch (FileException $e) {
                        $this->logger->error('Erreur lors de l\'upload du CV: ' . $e->getMessage());
                        throw new \Exception($this->translator->trans('job_application.messages.cv_upload_error'));
                    }
                }

                if ($motivationFile) {
                    $originalFilename = pathinfo($motivationFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $motivationFile->guessExtension();

                    try {
                        $motivationFile->move($motivationDir, $newFilename);
                        $jobApplication->setMotivationFilename('uploads/motivation/' . $newFilename);
                    } catch (FileException $e) {
                        $this->logger->error('Erreur lors de l\'upload de la lettre de motivation: ' . $e->getMessage());
                        throw new \Exception($this->translator->trans('job_application.messages.motivation_file_upload_error'));
                    }
                }

                // Sauvegarder l'application
                $em->persist($jobApplication);
                $em->flush();

                // Envoyer un email de confirmation
                $this->sendConfirmationEmail($mailer, $jobApplication);

                // Réponse pour AJAX
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => $this->translator->trans('job_application.messages.success')
                    ]);
                }

                $this->addFlash('success', $this->translator->trans('job_application.messages.success'));
                return $this->redirectToRoute('app_recrutement', ['_locale' => $request->getLocale()]);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de l\'envoi de la candidature: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => $this->translator->trans('job_application.messages.error_details', ['details' => $e->getMessage()])
                    ], 500);
                }

                $this->addFlash('error', $this->translator->trans('job_application.messages.error'));
            }
        }

        // Retourner les erreurs pour AJAX (quand le formulaire n'est pas valide)
        if ($request->isXmlHttpRequest() && $form->isSubmitted() && !$form->isValid()) {
            $errors = [];
            $fieldErrors = [];

            // Récupérer toutes les erreurs
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            // Récupérer les erreurs par champ
            foreach ($form->all() as $fieldName => $field) {
                if (!$field->isValid()) {
                    $fieldErrors[$fieldName] = [];
                    foreach ($field->getErrors() as $error) {
                        $fieldErrors[$fieldName][] = $error->getMessage();
                    }
                }
            }

            // Gestion spéciale pour les collections (langues)
            if (isset($form['langues']) && !$form['langues']->isValid()) {
                foreach ($form['langues']->all() as $index => $langueForm) {
                    if (!$langueForm->isValid()) {
                        foreach ($langueForm->all() as $langueField => $langueFieldForm) {
                            if (!$langueFieldForm->isValid()) {
                                $fieldName = "langues_{$index}_{$langueField}";
                                $fieldErrors[$fieldName] = [];
                                foreach ($langueFieldForm->getErrors() as $error) {
                                    $fieldErrors[$fieldName][] = $error->getMessage();
                                }
                            }
                        }
                    }
                }
            }

            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('job_application.messages.form_errors'),
                'errors' => $errors,
                'fieldErrors' => $fieldErrors
            ], 400);
        }

        $jobs = [
            ['name' => 'job_application.jobs.positions.coordinateur_activites', 'icon' => 'fas fa-users-cog'],
            ['name' => 'job_application.jobs.positions.veterinaire', 'icon' => 'fas fa-paw'],
            ['name' => 'job_application.jobs.positions.agronome', 'icon' => 'fas fa-seedling'],
            ['name' => 'job_application.jobs.positions.ecologiste', 'icon' => 'fas fa-leaf'],
            ['name' => 'job_application.jobs.positions.environnementaliste', 'icon' => 'fas fa-recycle'],
            ['name' => 'job_application.jobs.positions.paysagiste', 'icon' => 'fas fa-tree'],
            ['name' => 'job_application.jobs.positions.musicien', 'icon' => 'fas fa-music'],
            ['name' => 'job_application.jobs.positions.chanteur', 'icon' => 'fas fa-microphone'],
            ['name' => 'job_application.jobs.positions.artiste_beaux_arts', 'icon' => 'fas fa-palette'],
            ['name' => 'job_application.jobs.positions.potier', 'icon' => 'fas fa-vase'],
            ['name' => 'job_application.jobs.positions.enseignant_anglais', 'icon' => 'fas fa-book-open'],
            ['name' => 'job_application.jobs.positions.enseignant_francais', 'icon' => 'fas fa-book'],
            ['name' => 'job_application.jobs.positions.enseignant_arabe', 'icon' => 'fas fa-book-reader'],
            ['name' => 'job_application.jobs.positions.animateur_tamazight', 'icon' => 'fas fa-language'],
            ['name' => 'job_application.jobs.positions.archeologue', 'icon' => 'fas fa-monument'],
            ['name' => 'job_application.jobs.positions.anthropologue', 'icon' => 'fas fa-user-friends'],
            ['name' => 'job_application.jobs.positions.geologue', 'icon' => 'fas fa-mountain'],
            ['name' => 'job_application.jobs.positions.botaniste', 'icon' => 'fas fa-seedling'],
            ['name' => 'job_application.jobs.positions.biologiste', 'icon' => 'fas fa-dna'],
        ];

        return $this->render('recrutement/index.html.twig', [
            'form' => $form->createView(),
            'recruitment' => [
                'jobs' => $jobs
            ]
        ]);
    }

    private function sendConfirmationEmail(MailerInterface $mailer, JobApplication $jobApplication): void
    {
        try {
            // Email au candidat
            $candidateEmail = (new Email())
                ->from('contact@adalen-dz.com')
                ->to($jobApplication->getEmail())
                ->subject($this->translator->trans('job_application.email_templates.candidate.subject'))
                ->html($this->renderView('emails/job_application_confirmation.html.twig', [
                    'application' => $jobApplication,
                ]));

            $mailer->send($candidateEmail);

            // Email à l'équipe RH
            $hrEmail = (new Email())
                ->from('contact@adalen-dz.com')
                ->to('contact@adalen-dz.com')
                ->subject($this->translator->trans('job_application.email_templates.hr.subject', ['position' => $jobApplication->getPosteSouhaite()]))
                ->html($this->renderView('emails/job_application_notification.html.twig', [
                    'application' => $jobApplication,
                ]));

            $mailer->send($hrEmail);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        }
    }
}
