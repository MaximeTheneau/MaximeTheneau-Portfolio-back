<?php

namespace App\Controller\Admin;

use App\Entity\Posts;
use App\Entity\Category;
use App\Entity\Subcategory;
use App\Entity\Subtopic;
use App\Entity\Skill;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Comments;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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

        return $this->redirect($adminUrlGenerator->setController(PostsCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Portfolio - Administration')
            ->setLocales(['fr']);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addJsFile('build/js/trix-upload.js');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::subMenu('Contenu', 'fa fa-newspaper')->setSubItems([
            MenuItem::linkToCrud('Posts', 'fa fa-newspaper', Posts::class),
            MenuItem::linkToCrud('Commentaires', 'fa fa-comments', Comments::class),
        ]);

        yield MenuItem::subMenu('Taxonomie', 'fa fa-tags')->setSubItems([
            MenuItem::linkToCrud('Categories', 'fa fa-folder', Category::class),
            MenuItem::linkToCrud('Subcategories', 'fa fa-folder-open', Subcategory::class),
            MenuItem::linkToCrud('Subtopics', 'fa fa-tag', Subtopic::class),
        ]);

        yield MenuItem::linkToCrud('Skills', 'fa fa-cogs', Skill::class);
        yield MenuItem::linkToCrud('Products', 'fa fa-shopping-cart', Product::class);
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
    }
}
