<?php

namespace App\Controller\Admin;

use App\Repository\EvidenceRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReferenceController extends AbstractController
{
    public function __construct(private EvidenceRepository $evidenceRepository) {}

    #[Route('/admin/reference', name: 'app_admin_reference')]
    public function index(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Référence',
        ];
        return $this->render('admin/reference.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/reference/new', name: 'app_admin_reference_new')]
    public function new(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            [
                'url' => $this->generateUrl('app_admin_reference'),
                'text' => 'Référence',
            ],
            'Nouveau temoignage',
        ];
        return $this->render('admin/reference/new.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/reference/detail/{id}', name: 'app_admin_reference_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $evidence = $this->evidenceRepository->find($id);
        if (empty($evidence)) {
            throw new EntityNotFoundException('No entity found for #' . $id);
        }

        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            [
                'url' => $this->generateUrl('app_admin_reference'),
                'text' => 'Référence',
            ],
            sprintf('Temoignage #%d: %s', $evidence->getId(), $evidence->getTitle()),
        ];
        return $this->render('admin/reference/show.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'reference' => $evidence,
        ]);
    }
}
