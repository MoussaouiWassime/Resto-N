<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReviewController extends AbstractController
{
    #[Route('/{id}/update', name: 'app_review_update', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        Review $review,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Sécurité : On vérifie que l'utilisateur est bien l'auteur de l'avis
        if ($review->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet avis.');
        }

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setCreatedAt(new \DateTimeImmutable()); // Optionnel : mettre à jour la date
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a bien été modifié.');

            return $this->redirectToRoute('app_restaurant_show', [
                'id' => $review->getRestaurant()->getId()
            ]);
        }

        return $this->render('review/update.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_review_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(
        Review $review,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Sécurité : On vérifie que l'utilisateur est bien l'auteur de l'avis
        if ($review->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cet avis.');
        }

        $restaurantId = $review->getRestaurant()->getId();

        // Création du formulaire de suppression comme dans ReservationController
        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Supprimer mon avis', 'attr' => ['class' => 'btn btn-danger']])
            ->add('cancel', SubmitType::class, ['label' => 'Annuler', 'attr' => ['class' => 'btn btn-secondary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $entityManager->remove($review);
                $entityManager->flush();
            }

            // Dans tous les cas (suppression ou annulation), on retourne au restaurant
            return $this->redirectToRoute('app_restaurant_show', ['id' => $restaurantId]);
        }

        return $this->render('review/delete.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }
}
