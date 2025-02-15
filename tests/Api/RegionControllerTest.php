<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegionControllerTest extends WebTestCase
{
    public function testGetAll(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/regions');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('recordsTotal', $responseData);
        $this->assertArrayHasKey('recordsFiltered', $responseData);
        $this->assertArrayHasKey('data', $responseData);

        $this->assertEquals(10, $responseData['recordsTotal']);
        $this->assertEquals(10, $responseData['recordsFiltered']);
        $this->assertCount(10, $responseData['data']);
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/regions', [
            'name' => 'RegionControllerTest TestCreate',
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        $this->assertNotEmpty($responseData['id']);
        $this->assertEquals('RegionControllerTest TestCreate', $responseData['name']);

        $client->request('DELETE', '/api/regions/' . $responseData['id']);
        $this->assertResponseIsSuccessful();
    }

    public function testUpdate(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/regions/9', [
            'name' => 'RegionControllerTest TestCreate',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        $this->assertEquals(9, $responseData['id']);
        $this->assertEquals('RegionControllerTest TestCreate', $responseData['name']);
    }

    public function testGetDetail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/regions/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        $this->assertEquals(1, $responseData['id']);
        $this->assertEquals('Region 0', $responseData['name']);
    }
}
