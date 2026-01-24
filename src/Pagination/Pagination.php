<?php
namespace App\Pagination;

use Exception;

class Pagination
{

    public int $limit;

    /**
     * @var int[]
     */
    public array $limitOptions = [];

    public int $offset;

    public int $page;

    public array $extraOptions = [];

    public function __construct(
        private readonly array $options
    )
    {
        $this->setLimitOptions($options);
        $this->setLimit($options);
        $this->setPageAndOffset($options);
    }

    public function getMaxPage(int $totalRecords): int
    {
        return (int) ceil($totalRecords / $this->limit);
    }

    public function toArray(): array
    {
        return [
            'limit_options' => $this->limitOptions,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'page' => $this->page,
            'extra_options' => $this->extraOptions,
        ];
    }

    public function withMaxPageArray(int $totalRecords, int $maxVisible = 5): array
    {
        $result = $this->toArray();
        return array_merge($result, [
            'max_page' => $this->getMaxPage($totalRecords),
            'pages' => $this->paginate($totalRecords, $maxVisible),
        ]);
    }

    public function paginate(int $totalItems, int $maxVisible = 5): array
    {
        $totalPages = $this->getMaxPage($totalItems);

        if ($totalPages <= 1) {
            return [1];
        }

        $pages = [];

        if ($totalPages <= $maxVisible) {
            return range(1, $totalPages);
        }

        $half = intdiv($maxVisible, 2);

        $start = max(1, $this->page - $half);
        $end   = min($totalPages, $this->page + $half);

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

    private function setLimitOptions(array $options)
    {
        $limitOptions = array_key_exists('limit_options', $options) ? $options['limit_options'] : [10, 20, 30, 50, 100];
        if (!is_array($limitOptions) || count($limitOptions) === 0) {
            throw new Exception('The option limit_options for pagination must be an array with one element at least.');
        }

        $this->limitOptions = $limitOptions;
    }

    private function setLimit(array $options)
    {
        $this->limit = array_key_exists('limit', $options) ? $options['limit'] : $this->limitOptions[0];
    }

    private function setPageAndOffset(array $options)
    {
        $pageAndOffset = $this->getPageAndOffset($options);

        $this->offset = $pageAndOffset['offset'];
        $this->page = $pageAndOffset['page'];
    }

    private function getPageAndOffset(array $options): array
    {
        $offset = isset($options['offset']) ? (int) $options['offset'] : null;
        $page   = isset($options['page'])   ? (int) $options['page']   : null;

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
                'page'   => intdiv($offset, $this->limit) + 1,
            ];
        }

        return [
            'offset' => ($page - 1) * $this->limit,
            'page'   => $page,
        ];
    }
}