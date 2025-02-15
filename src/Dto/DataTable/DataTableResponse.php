<?php
namespace App\Dto\DataTable;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Annotation\Groups;

class DataTableResponse
{
    #[Groups(['datatable'])]
    public $draw;

    #[Groups(['datatable'])]
    public $recordsTotal;

    #[Groups(['datatable'])]
    public $recordsFiltered;

    #[Groups(['datatable'])]
    public $data;

    public static function fromPaginator(Paginator $paginator, $draw): self
    {
        $result = new DataTableResponse;
        $result->draw = $draw;
        $result->data = iterator_to_array($paginator);
        $result->recordsFiltered = count($paginator);
        $result->recordsTotal = count($paginator);
        return $result;
    }
}