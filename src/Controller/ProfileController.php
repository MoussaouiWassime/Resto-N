<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile_show')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(RoleRepository $roleRepository): Response
    {
        $user = $this->getUser();
        $roles = $roleRepository->findBy(['user' => $user]);

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'roles' => $roles,
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
            $this->addFlash('success', 'Vos informations ont été mises à jour.');

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
        RoleRepository $roleRepository,
        MailerInterface $mailer,
    ): Response {
        $user = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Confirmer la suppression'])
            ->add('cancel', SubmitType::class, ['label' => 'Annuler'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                foreach ($user->getReservations() as $reservation) {
                    $reservation->setUser(null);
                }

                foreach ($user->getOrders() as $order) {
                    $order->setUser(null);
                }

                $roles = $roleRepository->findBy(['user' => $user]);
                foreach ($roles as $role) {
                    $entityManager->remove($role);
                }

                $tokenStorage->setToken(null);
                $session->invalidate();

                $entityManager->remove($user);
                $entityManager->flush();
                $this->addFlash('info', 'Votre compte a été définitivement supprimé.');

                $email = (new TemplatedEmail())
                    ->from(new Address('resto.n@reston.com', "Resto'N"))
                    ->to($this->getUser()->getEmail())
                    ->subject("Suppression de votre compte Resto'N")
                    ->htmlTemplate('emails/account_deleted.html.twig');

                $mailer->send($email);

                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('app_profile_show');
        }

        return $this->render('profile/delete.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
