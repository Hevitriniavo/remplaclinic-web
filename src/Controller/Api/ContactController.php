<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\ContactDto;
use App\Dto\IdListDto;
use App\Exceptions\ApiException;
use App\Repository\ContactRepository;
use App\Security\SecurityUser;
use App\Service\Contact\ContactService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly ContactService $contactService,
    ) {}
    
    #[Route('/api/contacts', name: 'api_contacts_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->contactRepository->findAllDataTables($params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/contacts', name: 'api_contacts_new', methods: ['POST'])]
    public function create(
        Request $request,
        FlashBagAwareSessionInterface $flashBag,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ContactDto $contactDto,
    ): Response {
        // step 1: get user if connected
        $user = null;
        if ($this->isGranted('ROLE_USER')) {
            /**
             * @var SecurityUser
             */
            $connectedToken = $this->getUser();
            $user = $connectedToken->getUser();
        }

        // step 2: get remote address
        if (empty($contactDto->remote_addr)) {
            // @TODO: get remote addr from request
        }

        // step 4: create and send email
        $created = $this->contactService->submitContact($contactDto, $user);
        if (is_null($created)) {
            throw ApiException::make("Impossible d'envoyer votre message. Veuillez contacter par telephone l'administateur pour l'assistance.", 'CONTACT_CREATION_FAILED', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // step 5: confirm
        $flashBag->getFlashBag()->set('contact_id', $created->getId());
        $flashBag->getFlashBag()->set('contact_type', $created->getContactType());

        return $this->json([
            '_redirect' => $this->generateUrl('app_contacts_confirmation')
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/contacts/{id}', name: 'api_contacts_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $contact = $this->contactRepository->find($id);

        return $this->json(
            $contact,
            is_null($contact) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => ['datatable', 'full']]
        );
    }

    #[Route('/api/contacts/{id}', name: 'api_contacts_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id): Response
    {
        $deleted = $this->contactService->deleteContact($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/api/contacts/delete-multiple', name: 'api_contacts_delete_multiple', methods: ['DELETE'])]
    public function removeMultiple(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] IdListDto $idList
    ): Response
    {

        $deleted = $this->contactService->deleteMultipleContact($idList->ids);

        return $this->json(
            '',
            !empty($deleted) ? Response::HTTP_OK : Response::HTTP_CONFLICT
        );
    }
}
