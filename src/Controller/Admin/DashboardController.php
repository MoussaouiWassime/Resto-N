<?php

namespace App\Controller\Admin;

use App\Entity\Dish;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\Reservation;
use App\Entity\Restaurant;
use App\Entity\RestaurantCategory;
use App\Entity\Review;
use App\Entity\Role;
use App\Entity\Statistic;
use App\Entity\Stock;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Sae3 01');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de Bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Restaurants', 'fas fa-list', Restaurant::class);
        yield MenuItem::linkToCrud('Catégories de Restaurant', 'fas fa-list', RestaurantCategory::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-list', User::class);
        yield MenuItem::linkToCrud('Rôles', 'fas fa-list', Role::class);
        yield MenuItem::linkToCrud('Avis', 'fas fa-list', Review::class);
        yield MenuItem::linkToCrud('Plats', 'fas fa-list', Dish::class);
        yield MenuItem::linkToCrud('Produits', 'fas fa-list', Product::class);
        yield MenuItem::linkToCrud('Catégories du Produit', 'fas fa-list', ProductCategory::class);
        yield MenuItem::linkToCrud('Stocks', 'fas fa-list', Stock::class);
        yield MenuItem::linkToCrud('Commandes', 'fas fa-list', Order::class);
        yield MenuItem::linkToCrud('Liste des Plats de la Commande', 'fas fa-list', OrderItem::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-list', Reservation::class);
        yield MenuItem::linkToCrud('Statistiques', 'fas fa-list', Statistic::class);
    }
}
