<?php
namespace App\Dto\DataTable;

class DataTableParams
{
    const DEFAULT_LIMIT = 10;

    public $value;
    public $draw;
    public $order_column;
    public $order_dir;
    public $offset;
    public $limit;

    public static function fromRequest($requestParams = []): self
    {
        $params = new DataTableParams();
        $params->value = isset($requestParams['search']['value']) ? $requestParams['search']['value'] : null;
        $params->draw = isset($requestParams['draw']) ? (int) $requestParams['draw'] : null;
        $params->order_column = isset($requestParams['order'][0]['column']) ? (int) $requestParams['order'][0]['column'] : null;
        $params->order_dir = isset($requestParams['order'][0]['dir']) ? $requestParams['order'][0]['dir'] : null;
        $params->offset = isset($requestParams['start']) ? (int) $requestParams['start'] : null;
        $params->limit = isset($requestParams['length']) ? (int) $requestParams['length'] : self::DEFAULT_LIMIT;

        return $params;
    }

    public function getOrderDir(): string
    {
        return empty($this->order_dir) ? 'asc' : $this->order_dir;
    }

    public function getOrderColumn(array $cols = [], ?string $defaultValue = null): string
    {
        return array_key_exists($this->order_column, $cols) ? $cols[$this->order_column] : $defaultValue;
    }
}