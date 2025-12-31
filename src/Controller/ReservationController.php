<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\RestaurantRepository;
use App\Repository\RestaurantTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReservationController extends AbstractController
{
    #[Route('/reservation/create/{id}', name: 'app_reservation_create')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        int $id,
        RestaurantRepository $restaurantRepository,
        RestaurantTableRepository $tableRepository,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $restaurant = $restaurantRepository->find($id);

        $reservation = new Reservation();
        $reservation->setRestaurant($restaurant);
        $reservation->setStatus('C');
        $reservation->setReservationDate(new \DateTime('+1 day 19:00'));
        $reservation->setUser($this->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        $errorMessage = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $openingTime = $restaurant->getOpeningTime();
            $closingTime = $restaurant->getClosingTime();

            if ($openingTime && $closingTime) {
                $resaTime = (clone $reservation)->getReservationDate();
                $openCheck = (clone $openingTime);
                $closeCheck = (clone $closingTime);

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
                    $entityManager->flush();

                    return $this->redirectToRoute('app_home');
                } else {
                    $errorMessage = 'Aucune table disponible pour ce créneau.';
                }
            }
        }

        return $this->render('reservation/create.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant,
            'error' => $errorMessage,
        ]);
    }
}
