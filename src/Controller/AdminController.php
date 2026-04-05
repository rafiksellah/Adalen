<?php

namespace App\Controller;

use App\Entity\ContactRequest;
use App\Entity\JobApplication;
use App\Repository\ContactRequestRepository;
use App\Repository\JobApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin_dashboard')]
    public function dashboard(JobApplicationRepository $jobApplicationRepo): Response
    {
        $totalCandidates = $jobApplicationRepo->count([]);
        $recentCandidates = $jobApplicationRepo->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalCandidates' => $totalCandidates,
            'recentCandidates' => $recentCandidates,
        ]);
    }

    #[Route('/admin/candidatures', name: 'app_admin_candidatures')]
    public function candidatures(JobApplicationRepository $jobApplicationRepo, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $candidatures = $jobApplicationRepo->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $total = count($jobApplicationRepo->findAll());
        $totalPages = ceil($total / $limit);

        return $this->render('admin/candidatures.html.twig', [
            'candidatures' => $candidatures,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    #[Route('/admin/candidatures/{id}', name: 'app_admin_candidature_show')]
    public function showCandidature(JobApplication $candidature): Response
    {
        return $this->render('admin/candidature_show.html.twig', [
            'candidature' => $candidature,
        ]);
    }

    #[Route('/admin/contacts', name: 'app_admin_contacts')]
    public function contacts(ContactRequestRepository $contactRequestRepo): Response
    {
        $contacts = $contactRequestRepo->findAllRecentFirst();

        return $this->render('admin/contacts.html.twig', [
            'contacts' => $contacts,
            'total' => \count($contacts),
        ]);
    }

    #[Route('/admin/contacts/{id}', name: 'app_admin_contact_show', requirements: ['id' => '\d+'])]
    public function showContact(ContactRequest $contact): Response
    {
        return $this->render('admin/contact_show.html.twig', [
            'contact' => $contact,
        ]);
    }
}
