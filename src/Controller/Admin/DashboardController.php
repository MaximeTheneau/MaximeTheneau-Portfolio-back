<?php

namespace App\Controller\Admin;

use App\Entity\Posts;
use App\Entity\Category;
use App\Entity\Subcategory;
use App\Entity\ParagraphPosts;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        // Redirection vers la liste des posts
        return $this->redirect($adminUrlGenerator->setController(PostsCrudController::class)->generateUrl());

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Portfolio - Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Posts', 'fa fa-newspaper', Posts::class);
        yield MenuItem::linkToCrud('Paragraphes', 'fa fa-paragraph', ParagraphPosts::class);
        yield MenuItem::linkToCrud('Categories', 'fa fa-folder', Category::class);
        yield MenuItem::linkToCrud('Subcategories', 'fa fa-folder-open', Subcategory::class);
    }
}
