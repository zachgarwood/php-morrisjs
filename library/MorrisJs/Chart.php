<?php
namespace MorrisJs;

use DataTable\Cell;
use DataTable\Column;
use DataTable\Row;
use DataTable\Table;

class Chart
{
    const
        TYPE_AREA   = 'Area',
        TYPE_BAR    = 'Bar',
        TYPE_DONUT  = 'Donut',
        TYPE_LINE   = 'Line',

        COL_LABEL = 'label',
        COL_VALUE = 'value',

        ID_PREFIX = 'chart-';

    protected
        $data,
        $id,
        $options,
        $type,
        $xAxis;

    private static
        $types = [
            self::TYPE_AREA,
            self::TYPE_BAR,
            self::TYPE_DONUT,
            self::TYPE_LINE,
        ];

    public function __construct(
        $type,
        Table $data,
        array $options = array()
    ) {
        if (!in_array($type, self::$types)) {
            throw new \InvalidArgumentException(
                "'$type' is not a valid chart type!"
            );
        }
        $this->type = $type;
        $this->data = $data;
        $this->options = $options;
    }

    public function getData()
    {
        return $this->data;
    }

    public function convertDataToJson()
    {
        $json = new \stdClass;
        $json->element = $this->getId();
        if ($this->type == self::TYPE_DONUT) {
            $columns = $this->data->getColumns();
            $column = reset($columns);
            $json->data = array();
            foreach ($this->data->getRows() as $row) {
                $item = new \stdClass;
                $item->label = $column->getLabel();
                $cells = $row->getCells();
                $cell = reset($cells);
                $item->value = $cell->value;
                $json->data []= $item;
            }
        } else {
            $json->xkey = $this->getXAxis()->getId();
            foreach ($this->data->getColumns() as $column) {
                if ($this->getXAxis() !== $column) {
                    $json->ykeys []= $column->getId();
                    $json->labels []= $column->getLabel();
                }
            }
            $json->data = $this->createDataJsonProperty();
        }

        return json_encode($json);
    }

    private function createDataJsonProperty()
    {
        foreach ($this->data->getRows() as $row) {
            $item = new \stdClass;
            foreach ($row->getCells() as $cell) {
                $item->{$cell->getColumn()->getId()} = $cell->value;
            }
            $data []= $item;
        }

        return $data;
    }

    public function setId($id)
    {
        return $this->id = self::ID_PREFIX . $id;
    }

    public function getId()
    {
        return isset($this->id) ?
            $this->id : $this->setId(uniqid());
    }

    public function setXAxis(Column $xAxis)
    {
        return $this->xAxis = $xAxis;
    }

    protected function getXAxis()
    {
        $columns = $this->data->getColumns();

        return isset($this->xAxis) ?
            $this->xAxis : $this->setXAxis(reset($columns));
    }
}

