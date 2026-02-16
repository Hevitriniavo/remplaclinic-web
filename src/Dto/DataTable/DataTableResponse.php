<?php
namespace App\Dto\DataTable;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Annotation\Groups;

class DataTableResponse
{
    #[Groups(['datatable', 'request:datatable'])]
    public $draw;

    #[Groups(['datatable', 'request:datatable'])]
    public $recordsTotal;

    #[Groups(['datatable', 'request:datatable'])]
    public $recordsFiltered;

    #[Groups(['datatable', 'request:datatable'])]
    public $data;

    public static function fromPaginator(Paginator $paginator, $draw, $skipData = false): self
    {
        $result = new DataTableResponse;
        $result->draw = $draw;
        if (!$skipData) {
            $result->data = iterator_to_array($paginator);
        }
        $result->recordsFiltered = count($paginator);
        $result->recordsTotal = count($paginator);
        return $result;
    }

    public static function fromData(array $data = []): DataTableResponse
    {
        return self::make($data, count($data), 0);
    }

    public static function make(array $data = [], int $total = 0, int $draw = 0): DataTableResponse
    {
        $result = new DataTableResponse();
        $result->recordsFiltered = $total;
        $result->recordsTotal = $total;
        $result->draw = $draw;
        $result->data = $data;

        return $result;
    }
}