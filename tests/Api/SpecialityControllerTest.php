<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SpecialityControllerTest extends WebTestCase
{
    public function testGetAll(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/specialities');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('recordsTotal', $responseData);
        $this->assertArrayHasKey('recordsFiltered', $responseData);
        $this->assertArrayHasKey('data', $responseData);

        $this->assertEquals(110, $responseData['recordsTotal']);
        $this->assertEquals(110, $responseData['recordsFiltered']);
        $this->assertCount(10, $responseData['data']);
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/specialities', [
            'name' => 'SpecialityControllerTest TestCreate',
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('specialityParent', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        $this->assertNotEmpty($responseData['id']);
        $this->assertEquals('SpecialityControllerTest TestCreate', $responseData['name']);
        $this->assertNull($responseData['specialityParent']);

        $client->request('DELETE', '/api/specialities/' . $responseData['id']);
        $this->assertResponseIsSuccessful();
    }

    public function testUpdate(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/specialities/99', [
            'name' => 'SpecialityControllerTest TestCreate',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('specialityParent', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        $this->assertEquals(99, $responseData['id']);
        $this->assertEquals('SpecialityControllerTest TestCreate', $responseData['name']);
        $this->assertNotNull($responseData['specialityParent']);
    }

    public function testGetDetail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/specialities/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('specialityParent', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        $this->assertEquals(1, $responseData['id']);
        $this->assertEquals('Speciality Parent 0', $responseData['name']);
        $this->assertNull($responseData['specialityParent']);
    }
}
