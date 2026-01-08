<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Role;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RestaurantController extends AbstractController
{
    #[Route('/restaurant', name: 'app_restaurant')]
    public function index(RestaurantRepository $restaurant, #[MapQueryParameter] string $search = ''): Response
    {
        $restaurants = $restaurant->search($search);

        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurants,
            'search' => $search,
        ]);
    }

    #[Route('/restaurant/{id}', name: 'app_restaurant_show', requirements: ['id' => '\d+'])]
    public function show(#[MapEntity(expr: 'repository.findWithId(id)')] Restaurant $restaurant): Response
    {
        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/restaurant/create/', name: 'app_restaurant_create')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        $restaurant = new Restaurant();

        $user = $this->getUser();

        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($restaurant);

            $newRole = new Role();
            $newRole->setRole('P')
                ->setRestaurant($restaurant)
                ->setUser($user);
            $entityManager->persist($newRole);

            $entityManager->flush();

            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        } else {
            return $this->render('restaurant/create.html.twig', [
                'form' => $form,
            ]);
        }
    }

    #[Route('/restaurant/delete/{id}', name: 'app_restaurant_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(
        int $id,
        RestaurantRepository $restaurantRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        $user = $this->getUser();
        $restaurant = $restaurantRepository->findOneBy(['id' => $id]);
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (null === $role) {
            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        }

        $form = $this->createFormBuilder($restaurant)
            ->add('delete', SubmitType::class)
            ->add('cancel', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $entityManager->remove($restaurant);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_restaurant', [], 307);
        } else {
            return $this->render('restaurant/delete.html.twig', [
                'restaurant' => $restaurant,
                'form' => $form,
            ]);
        }


    }
}
