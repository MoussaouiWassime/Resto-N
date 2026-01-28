<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Restaurant;
use App\Entity\Statistic;
use App\Enum\ReservationStatus;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\RestaurantRepository;
use App\Repository\RestaurantTableRepository;
use App\Repository\RoleRepository;
use App\Repository\StatisticRepository;
use App\Security\Voter\RestaurantVoter;
use App\Service\StatisticService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(['user' => $this->getUser()], ['reservationDate' => 'DESC']),
        ]);
    }

    #[Route('/reservation/restaurant/{id}', name: 'app_reservation_restaurant')]
    #[IsGranted(RestaurantVoter::STAFF, subject: 'restaurant')]
    public function byRestaurant(Restaurant $restaurant, ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findBy(
            ['restaurant' => $restaurant],
            ['reservationDate' => 'ASC']
        );

        return $this->render('reservation/byRestaurant.html.twig', [
            'restaurant' => $restaurant,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/reservation/create/{id}', name: 'app_reservation_create')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        int $id,
        RestaurantRepository $restaurantRepository,
        RestaurantTableRepository $tableRepository,
        StatisticService $statisticService,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        $restaurant = $restaurantRepository->find($id);

        $reservation = new Reservation();
        $reservation->setRestaurant($restaurant);
        $reservation->setStatus(ReservationStatus::CONFIRMED);
        $reservation->setReservationDate(new \DateTime('+1 day 19:00'));
        $reservation->setUser($this->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->remove('status');
        $form->remove('restaurantTable');
        $form->handleRequest($request);

        $errorMessage = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $openingTime = $restaurant->getOpeningTime();
            $closingTime = $restaurant->getClosingTime();

            if ($openingTime && $closingTime) {
                $resaTime = clone $reservation->getReservationDate();
                $openCheck = clone $openingTime;
                $closeCheck = clone $closingTime;

                // Tout a la même date pour comparer heure
                $resaTime->setDate(2000, 1, 1);
                $openCheck->setDate(2000, 1, 1);
                $closeCheck->setDate(2000, 1, 1);

                if ($resaTime < $openCheck || $resaTime > $closeCheck) {
                    $errorMessage = 'Le restaurant est fermé à cette heure (Horaires : '.$openingTime->format('H:i').' - '.$closingTime->format('H:i').')';
                }
            }

            if (!$errorMessage) {
                $assignedTable = $tableRepository->findAvailableTable(
                    $restaurant,
                    $reservation->getReservationDate(),
                    $reservation->getNumberOfPeople()
                );

                if ($assignedTable) {
                    $reservation->setRestaurantTable($assignedTable);
                    $entityManager->persist($reservation);
                    $statisticService->updateStatistic($restaurant, Statistic::VISITS, $reservation->getReservationDate(), 1);

                    $entityManager->flush();
                    $this->addFlash('success', 'Votre réservation est confirmée !');

                    $email = (new TemplatedEmail())
                        ->from(new Address('resto.n@reston.com', "Resto'N"))
                        ->to($this->getUser()->getEmail())
                        ->subject('Confirmation de réservation - '.$restaurant->getName())
                        ->htmlTemplate('emails/reservation_confirmation.html.twig')
                        ->context([
                            'reservation' => $reservation,
                            'restaurant' => $restaurant,
                        ]);

                    $mailer->send($email);

                    return $this->redirectToRoute('app_reservation');
                } else {
                    $errorMessage = 'Aucune table disponible pour ce créneau.';
                    $this->addFlash('danger', $errorMessage);
                }
            } else {
                $this->addFlash('danger', $errorMessage);
            }
        }

        return $this->render('reservation/create.html.twig', [
            'form' => $form,
            'restaurant' => $restaurant,
        ], new Response(null, $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/reservation/update/{id}', name: 'app_reservation_update')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        RoleRepository $roleRepository,
        StatisticService $statisticService,
        Request $request,
        EntityManagerInterface $entityManager,
        ?Reservation $reservation): Response
    {
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable.');
        }

        $restaurant = $reservation->getRestaurant();
        $user = $this->getUser();

        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);
        if (null === $role) {
            if ($reservation->getUser() !== $user) {
                return $this->redirectToRoute('app_restaurant', [], 307);
            }
        }

        $oldDate = $this->getDate($reservation);

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newDate = $reservation->getReservationDate();

            if ($oldDate->format('Y-m-d') !== $newDate->format('Y-m-d')) {
                $statisticService->updateStatistic($restaurant, Statistic::VISITS, $oldDate, -1);
                $statisticService->updateStatistic($restaurant, Statistic::VISITS, $newDate, 1);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Réservation modifiée avec succès.');

            return $this->redirectToRoute('app_reservation');
        }

        return $this->render('reservation/update.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/reservation/delete/{id}', name: 'app_reservation_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        StatisticService $statisticService,
        Reservation $reservation,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        $restaurant = $reservation->getRestaurant();

        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Supprimer'])
            ->add('cancel', SubmitType::class, ['label' => 'Annuler'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $statisticService->updateStatistic($restaurant, Statistic::VISITS, $reservation->getReservationDate(), -1);
                $entityManager->remove($reservation);
                $entityManager->flush();

                $this->addFlash('success', "Réservation supprimée de l'historique avec succès.");
            }

            return $this->redirectToRoute('app_reservation');
        }

        return $this->render('reservation/delete.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    public function getStatisticByType(
        StatisticRepository $statisticRepository,
        ?Restaurant $restaurant,
        $type,
        ?\DateTime $date): ?Statistic
    {
        return $statisticRepository->findOneBy([
            'restaurant' => $restaurant,
            'statisticType' => $type,
            'date' => $date,
        ]);
    }

    public function getDate(Reservation $reservation): ?\DateTime
    {
        $date = clone $reservation->getReservationDate();
        $date->setTime(0, 0);

        return $date;
    }

    public function updateStatistic(StatisticRepository $statisticRepository, ?Restaurant $restaurant, ?\DateTime $date, EntityManagerInterface $entityManager): void
    {
        $statisticVisits = $this->getStatisticByType($statisticRepository, $restaurant, Statistic::VISITS, $date);
        if ($statisticVisits) {
            $statisticVisits->setValue($statisticVisits->getValue() - 1);
            if ($statisticVisits->getValue() <= 0) {
                $entityManager->remove($statisticVisits);
            }
        }
    }

    #[Route('/reservation/{id}/cancel', name: 'app_reservation_cancel')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function cancel(
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepository,
        StatisticService $statisticService,
        MailerInterface $mailer,
        Request $request,
    ): Response {
        $user = $this->getUser();
        $restaurant = $reservation->getRestaurant();

        $isManager = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);
        if ($reservation->getUser() !== $user && !$isManager) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'annuler cette réservation.");
        }

        if (ReservationStatus::CANCELED === $reservation->getStatus() || ReservationStatus::TAKEN === $reservation->getStatus()) {
            $this->addFlash('warning', 'Cette réservation ne peut plus être annulée.');
            if ($isManager) {
                return $this->redirectToRoute('app_reservation_restaurant', ['id' => $restaurant->getId()]);
            }

            return $this->redirectToRoute('app_reservation');
        }

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, [
                'label' => "Confirmer l'annulation",
                'attr' => ['class' => 'btn btn-danger'],
            ])
            ->add('return', SubmitType::class, [
                'label' => 'Ne pas annuler',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $reservation->setStatus(ReservationStatus::CANCELED);
                $reservation->setRestaurantTable(null);
                $statisticService->updateStatistic($restaurant, Statistic::VISITS, $reservation->getReservationDate(), -1);

                $entityManager->flush();

                $this->addFlash('success', 'La réservation a été annulée avec succès.');

                $email = (new TemplatedEmail())
                    ->from(new Address('resto.n@reston.com', "Resto'N"))
                    ->to($reservation->getUser()->getEmail())
                    ->subject('Annulation réservation - '.$restaurant->getName())
                    ->htmlTemplate('emails/reservation_cancel.html.twig')
                    ->context([
                        'reservation' => $reservation,
                        'restaurant' => $restaurant,
                    ]);

                $mailer->send($email);
            }

            if ($isManager) {
                return $this->redirectToRoute('app_reservation_restaurant', ['id' => $restaurant->getId()]);
            }

            return $this->redirectToRoute('app_reservation');
        }

        return $this->render('reservation/cancel.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }
}
