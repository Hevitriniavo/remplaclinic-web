<?php

namespace App\Twig;

use Exception;
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
        $limitOptions = $this->getLimitOptions($options);
        $limit = $this->getLimit($options);
        $offset = $this->getOffset($options);

        $optionHtml = [];
        foreach ($limitOptions as $limitOption) {
            $selected = $limitOption === $limit ? ' selected' : '';
            $optionHtml[] = sprintf('<option value="%d"%s>%d</option>', $limitOption, $selected, $limitOption);
        }

        $pageSelectionView = sprintf(
            'Afficher <select name="limit" class="outline-none border-[1px] border-solid border-[#eaeaea] h-50 w-100 p-2 rounded-md bg-transparent">%s</select> sur <span>%d</span> lignes (de <span>%d</span> à <span>%d</span> lignes)',
            implode('', $optionHtml),
            $totalRecords,
            $offset + 1,
            min($offset + $limit, $totalRecords)
        );

        return $pageSelectionView;
    }

    public function renderPageLinks(int $totalRecords, array $options = []): string
    {
        $limit = $this->getLimit($options);
        $page = $this->getPage($options);
        $maxPage = $this->getMaxPage($totalRecords, $limit);
        $url = $this->getUrl($options);
        
        $pages = $this->paginate($page, $totalRecords, $limit, 5);

        $pageLinksHtml = [];

        // previous page link
        $pageLinksHtml[] = $this->getPageLink($page - 1, $url, false, $page <= 1, '&lt;');
        
        // first page
        foreach ($pages as $p) {
            if ($p === '...') {
                $pageLinksHtml[] = $this->getPageLink(0, $url, false, false, '...');
            } else {
                $pageLinksHtml[] = $this->getPageLink($p, $url, $p === $page, false);
            }
        }

        // next page
        $pageLinksHtml[] = $this->getPageLink($page + 1, $url, false, $page >= $maxPage, '&gt;');

        return implode('', $pageLinksHtml);
    }

    public function render(int $totalRecords, array $options = []): string
    {
        $limitOptionsView = $this->renderLimitOptions($totalRecords, $options);
        $pageLinksView = $this->renderPageLinks($totalRecords, $options);

        return sprintf(
            '<div class="flex justify-between items-center gap-2 mt-2"><div>%s</div><div>%s</div></div>',
            $limitOptionsView,
            $pageLinksView
        );
    }

    private function getLimitOptions(array $options): array
    {
        $limitOptions = array_key_exists('limit_options', $options) ? $options['limit_options'] : [10, 20, 30, 50, 100];
        if (!is_array($limitOptions) || count($limitOptions) === 0) {
            throw new Exception('The option limit_options for pagination must be an array with one element at least.');
        }

        return $limitOptions;
    }

    private function getLimit(array $options): int
    {
        $limitOptions = $this->getLimitOptions($options);
        return array_key_exists('limit', $options) ? $options['limit'] : $limitOptions[0];
    }

    private function getOffset(array $options): int
    {
        return $this->getPageAndOffset($options)['offset'];
    }

    private function getPage(array $options): int
    {
        return $this->getPageAndOffset($options)['page'];
    }

    private function getMaxPage(int $totalRecords, int $limit): int
    {
        return (int) ceil($totalRecords / $limit);
    }

    private function getUrl(array $options): string
    {
        return array_key_exists('_url', $options) ? $options['_url'] : '';
    }

    private function getPageAndOffset(array $options): array
    {
        $offset = isset($options['offset']) ? (int) $options['offset'] : null;
        $page   = isset($options['page'])   ? (int) $options['page']   : null;

        $limit = $this->getLimit($options);

        if ($offset === null && $page === null) {
            return [
                'offset' => 0,
                'page'   => 1,
            ];
        }

        if ($offset !== null && $page !== null) {
            return [
                'offset' => $offset,
                'page'   => $page,
            ];
        }

        if ($offset !== null) {
            return [
                'offset' => $offset,
                'page'   => intdiv($offset, $limit) + 1,
            ];
        }

        return [
            'offset' => ($page - 1) * $limit,
            'page'   => $page,
        ];
    }

    private function getPageLink(int $page, string $url, bool $active = false, bool $disabled = false, string $pageText = ''): string
    {
        $baseUrl = '';
        if (stripos('?', $url) !== false) {
            $baseUrl = $url . '?';
        } else {
            $baseUrl = $url . '&';
        }

        if (empty($pageText)) {
            $pageText = $page;
        }

        $activeClass = $active ? 'border-[#86d0f0] bg-[#ebf6fc] text-[#2d8eb8]' : 'border-[#eaeaea]';
        $startTag = $disabled ? 'span' : sprintf('a href="%spage=%s"', $baseUrl, $page);
        $endTag = $disabled ? 'span' : 'a';

        return sprintf(
            '<%s class="p-2 border-[1px] border-solid %s">%s</%s>',
            $startTag,
            $activeClass,
            $pageText,
            $endTag
        );
    }

    private function paginate(int $currentPage, int $totalItems, int $perPage, int $maxVisible = 5): array
    {
        $totalPages = $this->getMaxPage($totalItems, $perPage);

        if ($totalPages <= 1) {
            return [1];
        }

        $pages = [];

        if ($totalPages <= $maxVisible) {
            return range(1, $totalPages);
        }

        $half = intdiv($maxVisible, 2);

        $start = max(1, $currentPage - $half);
        $end   = min($totalPages, $currentPage + $half);

        if ($start === 1) {
            $end = $maxVisible;
        }

        if ($end === $totalPages) {
            $start = $totalPages - $maxVisible + 1;
        }

        if ($start > 1) {
            $pages[] = 1;
            if ($start > 2) {
                $pages[] = '...';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $pages[] = '...';
            }
            $pages[] = $totalPages;
        }

        return $pages;
    }
}
