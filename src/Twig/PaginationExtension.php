<?php

namespace App\Twig;

use App\Pagination\Pagination;
use App\Pagination\PaginationView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PaginationExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('pagination_view_limit_options', [$this, 'renderLimitOptions'], ['is_safe' => ['html']]),
            new TwigFunction('pagination_view_page_links', [$this, 'renderPageLinks'], ['is_safe' => ['html']]),
            new TwigFunction('pagination_view', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    public function renderLimitOptions(int $totalRecords, array $options = []): string
    {
        $paginationView = new PaginationView(new Pagination($options), $this->getUrl($options));

        return $paginationView->renderLimitOptions($totalRecords);
    }

    public function renderPageLinks(int $totalRecords, array $options = []): string
    {
        $paginationView = new PaginationView(new Pagination($options), $this->getUrl($options));

        return $paginationView->renderPageLinks($totalRecords);
    }

    public function render(int $totalRecords, array $options = []): string
    {
        $paginationView = new PaginationView(new Pagination($options), $this->getUrl($options));

        return $paginationView->render($totalRecords);
    }

    private function getUrl(array $options): string
    {
        return array_key_exists('_url', $options) ? $options['_url'] : '';
    }
}
