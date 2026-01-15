<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class InvitationController extends AbstractController
{
    #[Route('/restaurant/{id}/invite', name: 'app_invitation_send', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function send(
        Restaurant $restaurant,
        Request $request,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        $user = $this->getUser();
        $ownerRole = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (!$ownerRole || 'P' !== $ownerRole->getRole()) {
            $this->addFlash('danger', 'Action non autorisée.');

            return $this->redirectToRoute('app_restaurant_show', ['id' => $restaurant->getId()]);
        }

        $emailTarget = $request->request->get('server_email');

        $serverUser = $userRepository->findOneBy(['email' => $emailTarget]);
        if ($serverUser == $user) {
            $this->addFlash('warning', "Vous ne pouvez pas vous ajouter vous même !");
        } elseif ($serverUser) {
            $existingRole = $roleRepository->findOneBy(['user' => $serverUser, 'restaurant' => $restaurant]);

            if ($existingRole) {
                $this->addFlash('warning', "Cet utilisateur est déjà dans l'équipe ou invité.");
            } else {
                $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $token = substr(str_shuffle($chars), 0, 30);

                $newRole = new Role();
                $newRole->setUser($serverUser);
                $newRole->setRestaurant($restaurant);
                $newRole->setRole('P');
                $newRole->setInvitationToken($token);

                $entityManager->persist($newRole);
                $entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from(new Address('ton.email@gmail.com', "Resto'N"))
                    ->to($serverUser->getEmail())
                    ->subject("Rejoignez l'équipe de ".$restaurant->getName())
                    ->htmlTemplate('emails/invitation.html.twig')
                    ->context([
                        'restaurant' => $restaurant,
                        'token' => $token,
                        'manager' => $user,
                    ]);

                $mailer->send($email);
                $this->addFlash('success', 'Invitation envoyée au membre existant !');
            }
        }
        else {
            $email = (new TemplatedEmail())
                ->from(new Address('resto.n@reston.com', "Resto'N"))
                ->to($emailTarget)
                ->subject('Invitation à rejoindre '.$restaurant->getName())
                ->htmlTemplate('emails/invitation_register.html.twig')
                ->context([
                    'restaurant' => $restaurant,
                    'manager' => $user,
                ]);

            $mailer->send($email);
            $this->addFlash('info', "Ce compte n'existe pas encore. Un mail d'invitation à s'inscrire a été envoyé.");
        }

        return $this->redirectToRoute('app_restaurant_manage', ['id' => $restaurant->getId()]);
    }

    #[Route('/invitation/accept/{token}', name: 'app_invitation_accept')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function accept(
        string $token,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $role = $roleRepository->findOneBy(['invitationToken' => $token]);

        if (!$role) {
            $this->addFlash('danger', 'Lien invalide ou expiré.');

            return $this->redirectToRoute('app_home');
        }

        if ($role->getUser() !== $this->getUser()) {
            $this->addFlash('danger', 'Cette invitation ne vous est pas destinée.');

            return $this->redirectToRoute('app_home');
        }

        $role->setRole('S');
        $role->setInvitationToken(null);

        $entityManager->flush();

        $this->addFlash('success', "Vous avez rejoint l'équipe !");

        return $this->redirectToRoute('app_restaurant_show', ['id' => $role->getRestaurant()->getId()]);
    }
}
