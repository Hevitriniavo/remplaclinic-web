<?php
namespace App\Tests\Unit\Twig;

use App\Twig\VersionedAssetExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;

class VersionedAssetExtensionTest extends TestCase
{
    /**
     * @var object
     */
    private Packages $assetMock;

    protected function setUp(): void
    {
        $this->assetMock = $this->createMock(Packages::class);
    }

    public function testVersionedAssetInProduction()
    {
        $this->assetMock
            ->method('getUrl')
            ->willReturn('https://cdn.example.com/build/style.css');

        $extension = new VersionedAssetExtension($this->assetMock, 'prod', '1.2.3');

        $result = $extension->addVersion('build/style.css');

        $this->assertEquals('https://cdn.example.com/build/style.css?v=1.2.3', $result);
    }

    public function testVersionedAssetInDevelopment()
    {
        $this->assetMock
            ->method('getUrl')
            ->willReturn('/build/style.css');

        $extension = new VersionedAssetExtension($this->assetMock, 'dev', '1.2.3');

        $result = $extension->addVersion('build/style.css');

        preg_match('/\?v=(\d+)/', $result, $matches);
        
        $this->assertNotEmpty($matches, 'Version parameter is missing');
        $this->assertGreaterThan(time() - 5, (int) $matches[1], 'Timestamp is outdated');
    }

    public function testVersionedAssetWithExistingQueryParams()
    {
        $this->assetMock
            ->method('getUrl')
            ->willReturn('/build/style.css?theme=dark');

        $extension = new VersionedAssetExtension($this->assetMock, 'prod', '1.2.3');

        $result = $extension->addVersion('build/style.css');

        $this->assertEquals('/build/style.css?theme=dark&v=1.2.3', $result);
    }
}