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
    public function show(
        RoleRepository $roleRepository,
        #[MapEntity(expr: 'repository.findWithId(id)')] Restaurant $restaurant): Response
    {
        $role = $roleRepository->findOneBy(['restaurant' => $restaurant, 'user' => $this->getUser()]);

        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
            'role' => $role,
        ]);
    }

    #[Route('/restaurant/{id}/manage', name: 'app_restaurant_manage', requirements: ['id' => '\d+'])]
    public function manage(
        RoleRepository $roleRepository,
        #[MapEntity(expr: 'repository.findWithId(id)')] Restaurant $restaurant): Response
    {
        $role = $roleRepository->findOneBy(['restaurant' => $restaurant, 'user' => $this->getUser()]);

        return $this->render('restaurant/manage.html.twig', [
            'restaurant' => $restaurant,
            'role' => $role,
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

    #[Route('/restaurant/{id}/delete', name: 'app_restaurant_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(
        ?Restaurant $restaurant,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (null === $role || 'P' != $role->getRole()) {
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
                return $this->redirectToRoute('app_restaurant', [], 307);
            } elseif ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('app_restaurant_manage', [
                    'id' => $restaurant->getId(),
                    'restaurant' => $restaurant,
                    'role' => $role], 307);
            }


        } else {
            return $this->render('restaurant/delete.html.twig', [
                'restaurant' => $restaurant,
                'form' => $form,
            ]);
        }
    }

    #[Route('/restaurant/{id}/update/', name: 'app_restaurant_update')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        ?Restaurant $restaurant,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (null === $role || 'P' != $role->getRole()) {
            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        }

        $form = $this->createForm(RestaurantType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        } else {
            return $this->render('restaurant/update.html.twig', [
                'restaurant' => $restaurant,
                'form' => $form,
            ]);
        }
    }
}
