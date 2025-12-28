<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\Mailbox\AdminEmailDto;
use App\Entity\AdminEmail;
use App\Repository\AdminEmailRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class AdminEmailController extends AbstractController
{
    public function __construct(
        private AdminEmailRepository $adminEmailRepository,
    ) {}
    
    #[Route('/api/admin-emails', name: 'api_admin_email_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->adminEmailRepository->findAllDataTables($params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/admin-emails', name: 'api_admin_email_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] AdminEmailDto $adminEmailDto
    ): Response {
        $adminEmail = new AdminEmail();

        $this->updateAdminEmail($adminEmail, $adminEmailDto);

        return $this->json($this->adminEmailRepository->save($adminEmail), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
    }

    #[Route('/api/admin-emails/{id}', name: 'api_admin_email_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $adminEmail = $this->adminEmailRepository->find($id);

        return $this->json(
            $adminEmail,
            is_null($adminEmail) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/admin-emails/{id}', name: 'api_admin_email_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] AdminEmailDto $adminEmailDto
    ): Response {
        $adminEmail = $this->adminEmailRepository->find($id);

        if (is_null($adminEmail)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $this->updateAdminEmail($adminEmail, $adminEmailDto);

        $this->adminEmailRepository->update($adminEmail);

        return $this->json(
            $adminEmail,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/admin-emails/{id}', name: 'api_admin_email_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id): Response
    {
        $deleted = $this->adminEmailRepository->remove($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    private function updateAdminEmail(AdminEmail $adminEmail, AdminEmailDto $adminEmailDto)
    {
        $adminEmail->setName($adminEmailDto->name);
        $adminEmail->setEmail($adminEmailDto->email);
        $adminEmail->setEvents($adminEmailDto->events);
        $adminEmail->setStatus($adminEmailDto->status);
    }
}
