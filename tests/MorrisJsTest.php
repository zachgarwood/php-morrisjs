<?php
class MorrisJsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->fakeData = new DataTable\Data;
        $col1 = new DataTable\Column(DataTable\Column::TYPE_BOOLEAN, 'boolean');
        $cell1 = new DataTable\Cell($col1, true);
        $cell2 = new DataTable\Cell($col1, false);
        $cell3 = new DataTable\Cell($col1, true);
        $row1 = new DataTable\Row;
        $row1->setCell($cell1);
        $row2 = new DataTable\Row;
        $row2->setCell($cell2);
        $row3 = new DataTable\Row;
        $row3->setCell($cell3);
        $this->fakeData->addColumn($col1);
        $this->fakeData->insertRow($row1);
        $this->fakeData->insertRow($row2);
        $this->fakeData->insertRow($row3);
    }

    public function testExceptionOnInvalidChartType()
    {
        $this->setExpectedException('InvalidArgumentException');
        new MorrisJs\Chart(
            'INVALID CHART TYPE!',
            $this->fakeData
        );
    }

    public function testJsonIsValid()
    {
        $chart = new MorrisJs\Chart(MorrisJs\Chart::TYPE_LINE, $this->fakeData);
        json_decode($chart->convertDataToJson());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
    }

    public function testJsonIsProperlyFormatted()
    {
        $chart = new MorrisJs\Chart(MorrisJs\Chart::TYPE_LINE, $this->fakeData);
        $chart->setId('test');
        $this->assertJsonStringEqualsJsonFile(
            'tests/fixtures/data.json',
            $chart->convertDataToJson()
        );
    }

    public function testDonutChartHasLabelAndValueColumnInJson()
    {
        $chart =
            new MorrisJs\Chart(MorrisJs\Chart::TYPE_DONUT, $this->fakeData);
        $json = $chart->convertDataToJson();
        $this->assertRegExp('/"' . MorrisJs\Chart::COL_LABEL . '":/', $json);
        $this->assertRegExp('/"' . MorrisJs\Chart::COL_VALUE . '":/', $json);
    }
}

