<?php

namespace App\Tests\Integration\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Repository\RequestRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RequestRepositoryTest extends KernelTestCase
{
    private ?EntityManager $entityManager;
    
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testFindAllDataTables(): void
    {
        /**
         * @var RequestRepository
         */
        $repository = $this->entityManager->getRepository(Request::class);
        
        $params = DataTableParams::fromRequest([
            'order' => [[0, 'asc']], // by id
            'search' => ['value' => 'User 1'],
            'start' => '0',
            'length' => '10',
        ]);
        $result = $repository->findAllDataTables(RequestType::REPLACEMENT, $params);

        $this->assertInstanceOf(DataTableResponse::class, $result);
        $this->assertEquals(10, $result->recordsTotal);
        $this->assertEquals(10, $result->recordsFiltered);
        $this->assertCount(10, $result->data);

        $request = $result->data[0]['request'];

        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals(1, $request->getId());
        $this->assertEquals(10, $request->getPositionCount());
        $this->assertStringStartsWith('Demande de remplacement du ', $request->getTitle());
        $this->assertNotNull($request->getApplicant());
        $this->assertNotNull($request->getApplicant());
        $this->assertEquals(0, $result->data[0]['responseCount']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
