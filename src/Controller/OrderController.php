<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Restaurant;
use App\Entity\Statistic;
use App\Enum\OrderStatus;
use App\Form\OrderType;
use App\Repository\DishRepository;
use App\Repository\OrderRepository;
use App\Repository\RestaurantRepository;
use App\Repository\RoleRepository;
use App\Repository\StatisticRepository;
use App\Security\Voter\RestaurantVoter;
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
    #[IsGranted(RestaurantVoter::STAFF, subject: 'restaurant')]
    public function byRestaurant(
        Restaurant $restaurant,
        OrderRepository $orderRepository,
    ): Response {
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
        $order->setStatus(OrderStatus::PENDING);

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
                    $statisticVisites = $this->getStatisticByType($statisticRepository, $restaurant, Statistic::VISITS, $date);
                    if (!$statisticVisites) {
                        $statisticVisites = $this->createStatisticByType($restaurant, Statistic::VISITS, $date, 1);
                        $entityManager->persist($statisticVisites);
                    } else {
                        $statisticVisites->setValue($statisticVisites->getValue() + 1);
                    }
                }

                $statisticCA = $this->getStatisticByType($statisticRepository, $restaurant, Statistic::INCOME, $date);
                if (!$statisticCA) {
                    $statisticCA = $this->createStatisticByType($restaurant, Statistic::INCOME, $date, $price);
                    $entityManager->persist($statisticCA);
                } else {
                    $statisticCA->setValue($statisticCA->getValue() + $price);
                }

                $statisticCommandes = $this->getStatisticByType($statisticRepository, $restaurant, Statistic::ORDERS, $date);
                if (!$statisticCommandes) {
                    $statisticCommandes = $this->createStatisticByType($restaurant, Statistic::ORDERS, $date, 1);
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
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        ?Order $order,
        StatisticRepository $statisticRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $restaurant = $order->getRestaurant();

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

                $this->updateStatistic($order, $statisticRepository, $restaurant, $entityManager, $price);

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

    public function getStatisticByType(
        StatisticRepository $statisticRepository,
        $restaurant,
        $type,
        ?\DateTime $date): ?Statistic
    {
        return $statisticRepository->findOneBy([
            'restaurant' => $restaurant,
            'statisticType' => $type,
            'date' => $date,
        ]);
    }

    public function createStatisticByType(Restaurant $restaurant, $type, ?\DateTime $date, int $value): Statistic
    {
        return (new Statistic())
            ->setRestaurant($restaurant)
            ->setStatisticType($type)
            ->setDate($date)
            ->setValue($value);
    }

    #[Route('/order/{id}/cancel', name: 'app_order_cancel')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function cancel(
        Order $order,
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepository,
        StatisticRepository $statisticRepository,
        MailerInterface $mailer,
        Request $request,
    ): Response {
        $user = $this->getUser();
        $restaurant = $order->getRestaurant();

        $role = $roleRepository->findOneBy([
            'user' => $user,
            'restaurant' => $restaurant,
        ]);

        $price = 0;
        foreach ($order->getOrderItems() as $item) {
            $price += $item->getDish()->getPrice() * $item->getQuantity();
        }

        if (null === $role) {
            throw $this->createAccessDeniedException('Seul le personnel du restaurant peut annuler une commande.');
        }

        if ('A' === $order->getStatus()) {
            $this->addFlash('warning', 'Cette commande est déjà annulée.');

            return $this->redirectToRoute('app_order_restaurant', ['id' => $restaurant->getId()]);
        }

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, [
                'label' => "Confirmer l'annulation",
                'attr' => ['class' => 'btn btn-danger'],
            ])
            ->add('return', SubmitType::class, [
                'label' => 'Retour',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $order->setStatus(OrderStatus::CANCELED);

                $email = (new TemplatedEmail())
                    ->from(new Address('resto.n@reston.com', "Resto'N"))
                    ->to($order->getUser()->getEmail())
                    ->subject('Annulation de votre commande - '.$restaurant->getName())
                    ->htmlTemplate('emails/order_cancel.html.twig')
                    ->context([
                        'order' => $order,
                        'restaurant' => $restaurant,
                    ]);

                $mailer->send($email);

                $this->addFlash('success', 'La commande a été annulée avec succès.');

                $this->updateStatistic($order, $statisticRepository, $restaurant, $entityManager, $price);

                $entityManager->flush();
            }

            return $this->redirectToRoute('app_order_restaurant', ['id' => $restaurant->getId()]);
        }

        return $this->render('order/cancel.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    public function updateStatistic(Order $order, StatisticRepository $statisticRepository, ?Restaurant $restaurant, EntityManagerInterface $entityManager, $price): void
    {
        $date = clone $order->getOrderDate();
        $date->setTime(0, 0);

        if ('L' !== $order->getOrderType()) {
            $statisticVisits = $this->getStatisticByType($statisticRepository, $restaurant, Statistic::VISITS, $date);
            if ($statisticVisits) {
                $statisticVisits->setValue($statisticVisits->getValue() - 1);
                if ($statisticVisits->getValue() <= 0) {
                    $entityManager->remove($statisticVisits);
                }
            }
        }

        $statisticCommandes = $this->getStatisticByType($statisticRepository, $restaurant, Statistic::ORDERS, $date);
        if ($statisticCommandes) {
            $statisticCommandes->setValue($statisticCommandes->getValue() - 1);
            if ($statisticCommandes->getValue() <= 0) {
                $entityManager->remove($statisticCommandes);
            }
        }

        $statisticIncome = $this->getStatisticByType($statisticRepository, $restaurant, Statistic::INCOME, $date);
        if ($statisticIncome) {
            $statisticIncome->setValue($statisticIncome->getValue() - $price);
            if ($statisticIncome->getValue() <= 0) {
                $entityManager->remove($statisticIncome);
            }
        }
    }
}
