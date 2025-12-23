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
}