<?php

namespace App\Controller\Admin;

use App\Entity\Animator;
use App\Form\AnimatorType;
use App\Repository\AnimatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/animator')]
class AnimatorCrudController extends AbstractController
{
    #[Route('/', name: 'app_admin_animator_index', methods: ['GET'])]
    public function index(AnimatorRepository $animatorRepository): Response
    {
        return $this->render('admin/animator/index.html.twig', [
            'animators' => $animatorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_animator_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $animator = new Animator();
        $form = $this->createForm(AnimatorType::class, $animator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animator->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($animator);
            $entityManager->flush();

            $this->addFlash('success', 'L\'animateur a été créé avec succès.');
            return $this->redirectToRoute('app_admin_animator_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/animator/new.html.twig', [
            'animator' => $animator,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_animator_show', methods: ['GET'])]
    public function show(Animator $animator): Response
    {
        return $this->render('admin/animator/show.html.twig', [
            'animator' => $animator,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_animator_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Animator $animator, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnimatorType::class, $animator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animator->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'L\'animateur a été modifié avec succès.');
            return $this->redirectToRoute('app_admin_animator_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/animator/edit.html.twig', [
            'animator' => $animator,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_animator_delete', methods: ['POST'])]
    public function delete(Request $request, Animator $animator, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$animator->getId(), $request->request->get('_token'))) {
            $entityManager->remove($animator);
            $entityManager->flush();
            $this->addFlash('success', 'L\'animateur a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_animator_index', [], Response::HTTP_SEE_OTHER);
    }
}
