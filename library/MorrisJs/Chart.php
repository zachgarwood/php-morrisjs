<?php
/**
 * This file is part of the MorrisJs package.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @author Zach Garwood <zachgarwood@gmail.com>
 * @copyright Copyright (c) 2013 Zach Garwood
 * @license MIT
 */

namespace MorrisJs;

use DataTable\Cell;
use DataTable\Column;
use DataTable\Row;
use DataTable\Table;
use Zend\Json\Expr as JavascriptExpr;
use Zend\Json\Json as Javascript;

/**
 * Chart container
 */
class Chart
{
    /**
     * @api
     * @since 1.0.0
     */
    const COL_LABEL = 'label';

    /**
     * @api
     * @since 1.0.0
     */
    const COL_VALUE = 'value';

    /**
     * @api
     * @since 1.0.0
     */
    const ID_PREFIX = 'morris-';

    /**
     * @api
     * @since 1.0.0
     */
    const RESOURCE_JQUERY = '//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js';

    /**
     * @since 1.0.0
     */
    const RESOURCE_MORRIS_SCRIPT = '//cdn.oesmith.co.uk/morris-0.4.3.min.js';

    /**
     * @api
     * @since 1.0.0
     */
    const RESOURCE_MORRIS_STYLESHEET = '//cdn.oesmith.co.uk/morris-0.4.3.min.css';

    /**
     * @api
     * @since 1.0.0
     */
    const RESOURCE_RAPHAEL = '//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js';

    /**
     * @api
     * @since 1.0.0
     */
    const TYPE_AREA = 'Area';

    /**
     * @api
     * @since 1.0.0
     */
    const TYPE_BAR = 'Bar';

    /**
     * @api
     * @since 1.0.0
     */
    const TYPE_DONUT = 'Donut';

    /**
     * @api
     * @since 1.0.0
     */
    const TYPE_LINE = 'Line';

    /**
     * @api
     * @since 1.0.0
     *
     * @var string[] An array of stylesheet resource uris
     */
    public static $stylesheets = [
        self::RESOURCE_MORRIS_STYLESHEET,
    ];

    /**
     * @api
     * @since 1.0.0
     *
     * @var string[] An array of script resource uris
     */
    public static $scripts = [
        self::RESOURCE_JQUERY,
        self::RESOURCE_MORRIS_SCRIPT,
        self::RESOURCE_RAPHAEL,
    ];

    /**
     * @var DataTable\Table
     */
    protected $data;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string[] An array of additional options
     */
    protected $options;

    /**
     * @var string One of the TYPE_* constants defined in this class
     */
    protected $type;

    /**
     * @var DataType\Column If not manually set, the first column will be used as the X axis
     */
    protected $xAxis;

    /**
     * @var string[] An arry of the TYPE_* constants defined in this class
     */
    private static $types = [
        self::TYPE_AREA,
        self::TYPE_BAR,
        self::TYPE_DONUT,
        self::TYPE_LINE,
    ];

    /**
     * @api
     * @since 1.0.0
     *
     * @param string $type
     * @param DataTable\Table $data
     * @param string[] $options An array of additional options
     *
     * @throws InvalidArgumentException if $type is not one of the constant defined in this class
     */
    public function __construct($type, Table $data, array $options = array())
    {
        if (!in_array($type, self::$types)) {
            throw new \InvalidArgumentException("'$type' is not a valid chart type!");
        }
        $this->type = $type;
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * @api
     * @since 1.0.0
     *
     * @return DataTable\Table
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @deprecated Use Chart::convertDataToJavascript() instead
     * @api
     * @since 1.0.0
     *
     * @return string A Javascript-encoded string representation of the chart
     */
    public function convertDataToJson()
    {
        return $this->convertDataToJavascript();
    }

    /**
     * @api
     * @since 1.0.1
     *
     * @return string A Javascript-encoded string representation of the chart
     */
    public function convertDataToJavascript()
    {
        $javascript = new \stdClass;
        $javascript->element = $this->getId();
        if ($this->type == self::TYPE_DONUT) {
            $columns = $this->data->getColumns();
            $column = reset($columns);
            $javascript->data = array();
            foreach ($this->data->getRows() as $row) {
                $item = new \stdClass;
                $item->label = $column->getLabel();
                $cells = $row->getCells();
                $cell = reset($cells);
                $item->value = $cell->value;
                $javascript->data []= $item;
            }
        } else {
            $javascript->xkey = $this->getXAxis()->getId();
            foreach ($this->data->getColumns() as $column) {
                if ($this->getXAxis() !== $column) {
                    $javascript->ykeys []= $column->getId();
                    $javascript->labels []= $column->getLabel();
                }
            }
            $javascript->data = $this->createDataProperty();
        }
        foreach ($this->options as $option => $value) {
            if (stripos($value, 'function(') === 0) {
                $javascript->$option = new JavascriptExpr($value);
            } else {
                $javascript->$option = $value;
            }
        }

        return Javascript::encode($javascript, true, ['enableJsonExprFinder' => true]);
    }

    /**
     * @api
     * @since 1.0.0
     *
     * @param string $id
     * @return string A prefixed ID
     */
    public function setId($id)
    {
        return $this->id = self::ID_PREFIX . $id;
    }

    /**
     * @api
     * @since 1.0.0
     *
     * @return string
     */
    public function getId()
    {
        return isset($this->id) ? $this->id : $this->setId(uniqid());
    }

    /**
     * Adds a set of options to those already set
     *
     * @api
     * @since 1.0.0
     *
     * @param string[] $options An array of additional options
     * @return string[] An array of all set options
     */
    public function setOptions(array $options)
    {
        return $this->options = array_merge($this->options, $options);
    }

    /**
     * Specifies the column to be used as the X axis
     *
     * @api
     * @since 1.0.0
     *
     * @param DataTable\Column
     * @return DataTable\Column
     */
    public function setXAxis(Column $xAxis)
    {
        return $this->xAxis = $xAxis;
    }

    /**
     * Returns the column used as the X axis
     *
     * If no column has been specified, the first column is returned.
     *
     * @api
     * @since 1.0.0
     *
     * @return DataTable\Column
     */
    protected function getXAxis()
    {
        $columns = $this->data->getColumns();

        return isset($this->xAxis) ? $this->xAxis : $this->setXAxis(reset($columns));
    }

    /**
     * Returns an array to be used as the `data` property of the output Javascript object
     *
     * @return mixed[]
     */
    private function createDataProperty()
    {
        $data = array();
        foreach ($this->data->getRows() as $row) {
            $item = new \stdClass;
            foreach ($row->getCells() as $cell) {
                if ($cell->getColumn()->getType() == Column::TYPE_DATETIME) {
                    $item->{$cell->getColumn()->getId()} = $cell->value->format('Y-m-d h:i:s.u');
                } elseif ($cell->getColumn()->getType() == Column::TYPE_DATE) {
                    $item->{$cell->getColumn()->getId()} = $cell->value->format('Y-m-d');
                } else {
                    $item->{$cell->getColumn()->getId()} = $cell->value;
                }
            }
            $data []= $item;
        }

        return $data;
    }
}
