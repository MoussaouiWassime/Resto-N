<?php

namespace App\Controller;

use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile_show')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profile/update', name: 'app_profile_update')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_show');
        }

        return $this->render('profile/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
    ): Response {
        $user = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Confirmer la suppression'])
            ->add('cancel', SubmitType::class, ['label' => 'Annuler'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $tokenStorage->setToken(null);
                $session->invalidate();

                $entityManager->remove($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('app_profile_show');
        }

        return $this->render('profile/delete.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
