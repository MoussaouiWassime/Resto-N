<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Entity\Restaurant;
use App\Form\DishType;
use App\Repository\DishRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DishController extends AbstractController
{
    #[Route('/dish', name: 'app_dish')]
    public function index(DishRepository $dishes, #[MapQueryParameter] string $search = ''): Response
    {
        return $this->render('dish/index.html.twig', [
            'dishes' => $dishes->search($search),
            'search' => $search,
        ]);
    }

    #[Route('/dish/{id}', requirements: ['id' => '\d+'])]
    public function show(?Dish $dish): Response
    {
        if (null == $dish) {
            throw $this->createNotFoundException('Plat introuvable.');
        }

        return $this->render('dish/show.html.twig', [
            'dish' => $dish,
        ]);
    }

    #[Route('/restaurant/{id}/dish/create', name: 'app_dish_create', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        ?Restaurant $restaurant,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        if (null == $restaurant) {
            throw $this->createNotFoundException('Vous ne pouvez pas créer un plat sans restaurant');
        }

        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);
        if (null === $role || 'P' != $role->getRole()) {
            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        }

        $dish = new Dish();
        $dish->setRestaurant($restaurant);

        $form = $this->createForm(DishType::class, $dish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();
            if ($photo) {
                $newFileName = md5(uniqid(null, true)).'.'.$photo->guessExtension();
                $photo->move(
                    $this->getParameter('kernel.project_dir').'/public/images/dishes',
                    $newFileName,
                );
                $dish->setPhoto($newFileName);
            }

            $entityManager->persist($dish);
            $entityManager->flush();

            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        }

        return $this->render('dish/create.html.twig', [
            'form' => $form,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/dish/{id}/update/', name: 'app_dish_update', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        Dish $dish,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        $restaurant = $dish->getRestaurant();

        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);
        if (null === $role || 'P' != $role->getRole()) {
            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        }

        $form = $this->createForm(DishType::class, $dish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();
            if ($photo) {
                if ($dish->getPhoto()) {
                    $oldPhotoPath = $this->getParameter('kernel.project_dir').'/public/images/dishes/'.$dish->getPhoto();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
                $newFileName = md5(uniqid(null, true)).'.'.$photo->guessExtension();
                $photo->move(
                    $this->getParameter('kernel.project_dir').'/public/images/dishes',
                    $newFileName,
                );
                $dish->setPhoto($newFileName);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        }

        return $this->render('dish/update.html.twig', [
            'form' => $form,
            'restaurant' => $restaurant,
            'dish' => $dish,
        ]);
    }

    #[Route('/dish/{id}/delete', name: 'app_dish_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(
        Dish $dish,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        $restaurant = $dish->getRestaurant();

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

        $form = $this->createFormBuilder($dish)
            ->add('delete', SubmitType::class)
            ->add('cancel', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                if ($dish->getPhoto()) {
                    $oldPhotoPath = $this->getParameter('kernel.project_dir').'/public/images/dishes/'.$dish->getPhoto();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
                $entityManager->remove($dish);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $restaurant->getId(),
            ], 307);
        } else {
            return $this->render('dish/delete.html.twig', [
                'restaurant' => $restaurant,
                'form' => $form,
                'dish' => $dish,
            ]);
        }
    }
}
