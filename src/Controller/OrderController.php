<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\OrderType;
use App\Repository\DishRepository;
use App\Repository\OrderRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

                return $this->redirectToRoute('app_order');
            }
        }

        return $this->render('order/create.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
        ]);
    }
}
