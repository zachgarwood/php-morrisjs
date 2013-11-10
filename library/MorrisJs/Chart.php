<?php
namespace MorrisJs;

use DataTable;

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
        DataTable\Data $data,
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
            $column = reset($this->data->getColumns());
            $json->data = array();
            foreach ($this->data->getRows() as $row) {
                $item = new \stdClass;
                $item->label = $column->getLabel();
                $cell = reset($row->getCells());
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

    protected function getId()
    {
        return isset($this->id) ?
            $this->id : $this->setId(uniqid());
    }

    public function setXAxis(DataTable\Column $xAxis)
    {
        return $this->xAxis = $xAxis;
    }

    protected function getXAxis()
    {
        return isset($this->xAxis) ?
            $this->xAxis : $this->setXAxis(reset($this->data->getColumns()));
    }
}

