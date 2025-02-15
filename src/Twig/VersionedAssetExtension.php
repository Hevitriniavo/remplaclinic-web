<?php

namespace App\Twig;

use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class VersionedAssetExtension extends AbstractExtension
{
    private Packages $assets;
    private string $version;

    public function __construct(Packages $assets, string $env, string $appVersion)
    {
        $this->assets = $assets;
        $this->version = $env === 'dev' ? time() : $appVersion;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('versioned_asset', [$this, 'addVersion']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('versioned_asset', [$this, 'addVersion']),
        ];
    }

    public function addVersion(string $path): string
    {
        $assetUrl = $this->assets->getUrl($path);

        $separator = strpos($assetUrl, '?') === false ? '?' : '&';
        return $assetUrl . $separator . 'v=' . $this->version;
    }
}
