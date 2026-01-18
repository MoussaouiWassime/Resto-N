<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Restaurant;
use App\Entity\Statistic;
use App\Form\OrderType;
use App\Repository\DishRepository;
use App\Repository\OrderRepository;
use App\Repository\RestaurantRepository;
use App\Repository\RoleRepository;
use App\Repository\StatisticRepository;
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

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findBy(['user' => $this->getUser()], ['orderDate' => 'DESC']),
        ]);
    }

    #[Route('/order/restaurant/{id}', name: 'app_order_restaurant')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function byRestaurant(
        Restaurant $restaurant,
        RoleRepository $roleRepository,
        OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (null === $role) {
            return $this->redirectToRoute('app_restaurant', [], 307);
        }

        $orders = $orderRepository->findBy(
            ['restaurant' => $restaurant],
            ['orderDate' => 'ASC']
        );

        return $this->render('order/byRestaurant.html.twig', [
            'restaurant' => $restaurant,
            'orders' => $orders,
        ]);
    }

    #[Route('/order/create/{id}', name: 'app_order_create')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        int $id,
        RestaurantRepository $restaurantRepository,
        DishRepository $dishRepository,
        StatisticRepository $statisticRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        $restaurant = $restaurantRepository->find($id);

        $order = new Order();
        $order->setRestaurant($restaurant);
        $order->setUser($this->getUser());
        $order->setOrderDate(new \DateTime());
        $order->setStatus('E');

        $dishes = $dishRepository->findBy(['restaurant' => $restaurant]);
        foreach ($dishes as $dish) {
            $item = new OrderItem();
            $item->setDish($dish);
            $item->setQuantity(0);
            $order->addOrderItem($item);
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        $price = 0;

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($order->getOrderItems() as $item) {
                if ($item->getQuantity() <= 0) {
                    $order->removeOrderItem($item);
                } else {
                    $price += $item->getDish()->getPrice() * $item->getQuantity();
                }
            }

            if ($order->getOrderItems()->count() > 0) {
                $entityManager->persist($order);

                $date = clone $order->getOrderDate();
                $date->setTime(0, 0);

                if ('L' !== $order->getOrderType()) {
                    $statisticVisites = $statisticRepository->findOneBy([
                        'restaurant' => $restaurant,
                        'statisticType' => 'NB_VISITES',
                        'date' => $date,
                    ]);
                    if (!$statisticVisites) {
                        $statisticVisites = (new Statistic())
                            ->setRestaurant($restaurant)
                            ->setStatisticType('NB_VISITES')
                            ->setDate($date)
                            ->setValue(1);
                        $entityManager->persist($statisticVisites);
                    } else {
                        $statisticVisites->setValue($statisticVisites->getValue() + 1);
                    }
                }

                $statisticCA = $statisticRepository->findOneBy([
                    'restaurant' => $restaurant,
                    'statisticType' => 'CA_JOURNALIER',
                    'date' => $date,
                ]);
                if (!$statisticCA) {
                    $statisticCA = (new Statistic())
                        ->setRestaurant($restaurant)
                        ->setStatisticType('CA_JOURNALIER')
                        ->setDate($date)
                        ->setValue($price);
                    $entityManager->persist($statisticCA);
                } else {
                    $statisticCA->setValue($statisticCA->getValue() + $price);
                }

                $statisticCommandes = $statisticRepository->findOneBy([
                    'restaurant' => $restaurant,
                    'statisticType' => 'NB_COMMANDES',
                    'date' => $date,
                ]);
                if (!$statisticCommandes) {
                    $statisticCommandes = (new Statistic())
                        ->setRestaurant($restaurant)
                        ->setStatisticType('NB_COMMANDES')
                        ->setDate($date)
                        ->setValue(1);
                    $entityManager->persist($statisticCommandes);
                } else {
                    $statisticCommandes->setValue($statisticCommandes->getValue() + 1);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Votre commande a été passé avec succès !');

                $email = (new TemplatedEmail())
                    ->from(new Address('resto.n@reston.com', "Resto'N"))
                    ->to($this->getUser()->getEmail())
                    ->subject('Confirmation de votre commande')
                    ->htmlTemplate('emails/order_confirmation.html.twig')
                    ->context([
                        'order' => $order,
                        'user' => $this->getUser(),
                    ]);

                $mailer->send($email);

                return $this->redirectToRoute('app_order', [], 307);
            } else {
                $this->addFlash('danger', 'Votre commande est vide.');
            }
        }

        return $this->render('order/create.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
        ]);
    }

    #[Route('/order/{id}/delete', name: 'app_order_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(
        ?Order $order,
        RoleRepository $roleRepository,
        StatisticRepository $statisticRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $user = $this->getUser();
        $restaurant = $order->getRestaurant();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (null === $role) {
            return $this->redirectToRoute('app_restaurant', [], 307);
        }

        $form = $this->createFormBuilder($restaurant)
            ->add('delete', SubmitType::class)
            ->add('cancel', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $price = 0;
                foreach ($order->getOrderItems() as $item) {
                    $price += $item->getDish()->getPrice() * $item->getQuantity();
                }

                $entityManager->remove($order);

                $date = clone $order->getOrderDate();
                $date->setTime(0, 0);

                if ('L' !== $order->getOrderType()) {
                    $statisticVisits = $statisticRepository->findOneBy([
                        'restaurant' => $restaurant,
                        'statisticType' => 'NB_VISITES',
                        'date' => $date,
                    ]);
                    if ($statisticVisits) {
                        $statisticVisits->setValue($statisticVisits->getValue() - 1);
                        if ($statisticVisits->getValue() <= 0) {
                            $entityManager->remove($statisticVisits);
                        }
                    }
                }

                $statisticCommandes = $statisticRepository->findOneBy([
                    'restaurant' => $restaurant,
                    'statisticType' => 'NB_COMMANDES',
                    'date' => $date,
                ]);
                if ($statisticCommandes) {
                    $statisticCommandes->setValue($statisticCommandes->getValue() - 1);
                    if ($statisticCommandes->getValue() <= 0) {
                        $entityManager->remove($statisticCommandes);
                    }
                }

                $statisticIncome = $statisticRepository->findOneBy([
                    'restaurant' => $restaurant,
                    'statisticType' => 'CA_JOURNALIER',
                    'date' => $date,
                ]);
                if ($statisticIncome) {
                    $statisticIncome->setValue($statisticIncome->getValue() - $price);
                    if ($statisticIncome->getValue() <= 0) {
                        $entityManager->remove($statisticIncome);
                    }
                }

                $entityManager->flush();

                $this->addFlash('success', 'La commande a été supprimé de votre historique.');
            }

            return $this->redirectToRoute('app_order', [], 307);
        } else {
            return $this->render('order/delete.html.twig', [
                'order' => $order,
                'form' => $form,
            ]);
        }
    }
}
