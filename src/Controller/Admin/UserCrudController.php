<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserCrudController extends AbstractController
{
    public function __construct(private UserRepository $userRepository) {}

    #[Route('/admin/replacements', name: 'app_admin_replacement')]
    public function replacement(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs('Remplaçant');
        return $this->render('admin/user/replacement.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/doctors', name: 'app_admin_doctor')]
    public function doctor(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs('Médecin');
        return $this->render('admin/user/doctor.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/clinics', name: 'app_admin_clinic')]
    public function clinic(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs('Médecin');
        return $this->render('admin/user/clinic.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/directors', name: 'app_admin_director')]
    public function director(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs('Directeur');
        return $this->render('admin/user/director.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/replacement/new', name: 'app_admin_replacement_new')]
    public function new(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs('Remplaçant');
        $breadcrumbs[1] = [
            'url' => $this->generateUrl('app_admin_replacement'),
            'text' => 'Remplaçant',
        ];
        $breadcrumbs[] = 'Nouveau remplaçant';
        return $this->render('admin/user/replacement/new.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/replacement/detail/{id}', name: 'app_admin_replacement_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (empty($user)) {
            throw new EntityNotFoundException('No entity found for #' . $id);
        }

        $breadcrumbs = $this->getBreadcrumbs('Remplaçant');
        $breadcrumbs[1] = [
            'url' => $this->generateUrl('app_admin_replacement'),
            'text' => 'Remplaçant',
        ];
        $breadcrumbs[] = sprintf('User #%d: %s', $user->getId(), $user->getName());
        return $this->render('admin/user/replacement/show.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'user' => $user,
        ]);
    }

    private function getBreadcrumbs(string $title)
    {
        return  [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            $title,
        ];
    }
}
