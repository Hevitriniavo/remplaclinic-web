<?php

namespace App\Tests\Integration\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Dto\SpecialityDto;
use App\Entity\Speciality;
use App\Repository\SpecialityRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SpecialityRepositoryTest extends KernelTestCase
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
         * @var SpecialityRepository
         */
        $repository = $this->entityManager->getRepository(Speciality::class);
        
        $params = DataTableParams::fromRequest([
            'order' => [[0, 'asc']], // by id
            'search' => ['value' => ''],
            'start' => '0',
            'length' => '10',
        ]);
        $result = $repository->findAllDataTables($params);

        $this->assertInstanceOf(DataTableResponse::class, $result);
        $this->assertEquals(110, $result->recordsTotal);
        $this->assertEquals(110, $result->recordsFiltered);
        $this->assertCount(10, $result->data);

        $this->assertInstanceOf(Speciality::class, $result->data[0]);
        $this->assertEquals(1, $result->data[0]->getId());
        $this->assertEquals('Speciality Parent 0', $result->data[0]->getName());
        $this->assertNull($result->data[0]->getSpecialityParent());
    }

    public function testSave(): void
    {
        /**
         * @var SpecialityRepository
         */
        $repository = $this->entityManager->getRepository(Speciality::class);
        
        $specialityDto = new SpecialityDto('SpecialityRepositoryTest Name', NULL);
        $result = $repository->save($specialityDto);

        $this->assertInstanceOf(Speciality::class, $result);
        $this->assertEquals('SpecialityRepositoryTest Name', $result->getName());
        $this->assertNotNull($result->getId());
        $this->assertNull($result->getSpecialityParent());

        $this->entityManager->remove($result);
        $this->entityManager->flush();
    }

    public function testSave_WithParent(): void
    {
        /**
         * @var SpecialityRepository
         */
        $repository = $this->entityManager->getRepository(Speciality::class);
        
        $specialityDto = new SpecialityDto('SpecialityRepositoryTest Name', 1);
        $result = $repository->save($specialityDto);

        $this->assertInstanceOf(Speciality::class, $result);
        $this->assertEquals('SpecialityRepositoryTest Name', $result->getName());
        $this->assertNotNull($result->getId());

        $parent = $result->getSpecialityParent();
        $this->assertNotNull($parent);
        $this->assertEquals(1, $parent->getId());
        $this->assertEquals('Speciality Parent 0', $parent->getName());

        $this->entityManager->remove($result);
        $this->entityManager->flush();
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
