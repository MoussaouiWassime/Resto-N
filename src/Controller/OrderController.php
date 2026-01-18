<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Restaurant;
use App\Form\OrderType;
use App\Repository\DishRepository;
use App\Repository\OrderRepository;
use App\Repository\RestaurantRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        Request $request,
        EntityManagerInterface $entityManager,
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

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($order->getOrderItems() as $item) {
                if ($item->getQuantity() <= 0) {
                    $order->removeOrderItem($item);
                }
            }

            if ($order->getOrderItems()->count() > 0) {
                $entityManager->persist($order);
                $entityManager->flush();

                $this->addFlash('success', 'Votre commande a été passé avec succès !');

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
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        if (!$order) {
            throw $this->createNotFoundException('Restaurant introuvable.');
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
                $entityManager->remove($order);
                $entityManager->flush();
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
