<?php

require_once('library/MorrisJs/Chart.php');

class MorrisJsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $data = new DataTable\Data;
        $col1 = new DataTable\Column(DataTable\Column::TYPE_BOOLEAN, 'boolean');
        $col2 = new DataTable\Column(DataTable\Column::TYPE_STRING, 'string');
        $cell1 = new DataTable\Cell($col1, true);
        $cell2 = new DataTable\Cell($col2, 'string');
        $row1 = new DataTable\Row;
        $row1->setCell($cell1)->setCell($cell2);
        $data->addColumn($col1)->addColumn($col2);
        $data->insertRow($row1);
        $this->chart = new MorrisJs\Chart(MorrisJs\Chart::TYPE_LINE, $data);
        $this->chart->setId('test');
    }

    public function testExceptionOnInvalidChartType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->chart = new MorrisJs\Chart(
            'INVALID CHART TYPE!',
            new DataTable\Data
        );
    }

    public function testJsonIsValid()
    {
        json_decode($this->chart->convertDataToJson());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
    }

    public function testJsonIsProperlyFormatted()
    {
        $this->assertJsonStringEqualsJsonFile(
            'tests/fixtures/data.json',
            $this->chart->convertDataToJson()
        );
    }

    public function testDonutChartHasLabelAndValueColumnInJson()
    {
        $data = new DataTable\Data;
        $col1 = new DataTable\Column(DataTable\Column::TYPE_BOOLEAN, 'boolean');
        $cell1 = new DataTable\Cell($col1, true);
        $cell2 = new DataTable\Cell($col1, 'string');
        $cell3 = new DataTable\Cell($col1, 'string');
        $row1 = new DataTable\Row;
        $row1->setCell($cell1);
        $row2 = new DataTable\Row;
        $row2->setCell($cell2);
        $row3 = new DataTable\Row;
        $row3->setCell($cell3);
        $data->addColumn($col1);
        $data->insertRow($row1);
        $data->insertRow($row2);
        $data->insertRow($row3);
        $this->chart = new MorrisJs\Chart(MorrisJs\Chart::TYPE_DONUT, $data);
        $json = $this->chart->convertDataToJson();
        $this->assertRegExp('/"' . MorrisJs\Chart::COL_LABEL . '":/', $json);
        $this->assertRegExp('/"' . MorrisJs\Chart::COL_VALUE . '":/', $json);
    }
}

