<?php
namespace MorrisJs;

use DataTable\Cell;
use DataTable\Column;
use DataTable\Row;
use DataTable\Table;

class ChartTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $col1 = new Column(Column::TYPE_BOOLEAN, 'col1');
        $col2 = new Column(Column::TYPE_DATE, 'col2');
        $col3 = new Column(Column::TYPE_DATETIME, 'col3');
        $col4 = new Column(Column::TYPE_NUMBER, 'col4');
        $col5 = new Column(Column::TYPE_STRING, 'col5');

        $cell1 = new Cell($col1, true);
        $cell2 = new Cell($col2, new \DateTime('1983-03-24'));
        $cell3 = new Cell($col3, new \DateTime('1983-03-24'));
        $cell4 = new Cell($col4, 1);
        $cell5 = new Cell($col5, 'a');
        $cell6 = new Cell($col1, false);
        $cell7 = new Cell($col2, new \DateTime('2000-01-01'));
        $cell8 = new Cell($col3, new \DateTime('2000-01-01'));
        $cell9 = new Cell($col4, 2);
        $cell0 = new Cell($col5, 'b');

        $row1 = new Row;
        $row1->setCell($cell1);
        $row1->setCell($cell2);
        $row1->setCell($cell3);
        $row1->setCell($cell4);
        $row1->setCell($cell5);
        $row2 = new Row;
        $row2->setCell($cell6);
        $row2->setCell($cell7);
        $row2->setCell($cell8);
        $row2->setCell($cell9);
        $row2->setCell($cell0);

        $this->fakeTable = new Table;
        $this->fakeTable->addColumn($col1);
        $this->fakeTable->addColumn($col2);
        $this->fakeTable->addColumn($col3);
        $this->fakeTable->addColumn($col4);
        $this->fakeTable->addColumn($col5);
        $this->fakeTable->insertRow($row1);
        $this->fakeTable->insertRow($row2);
    }

    public function testGetData()
    {
        $chart = new Chart(Chart::TYPE_LINE, $this->fakeTable);
        $this->assertSame($this->fakeTable, $chart->getData());
    }

    public function testSetOptions()
    {
        $chart = new Chart(Chart::TYPE_LINE, $this->fakeTable);
        $this->assertInternalType('array', $chart->setOptions(array()));
        $this->assertContains('value1', $chart->setOptions(['option' => 'value1']));
        $this->assertNotContains('value1', $chart->setOptions(['option' => 'value2']));
    }

    public function testExceptionOnInvalidChartType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $chart = new Chart('INVALID CHART TYPE!', $this->fakeTable);
    }

    public function testJsonIsValid()
    {
        $chart = new Chart(Chart::TYPE_LINE, $this->fakeTable);
        $chart->setId('test');
        $chart->setOptions(['option1' => 'value', 'option2' => 'function() {return;}']);
        // A new line is appended to the output of convertDataToJavascript() to match the new line character at the
        // end of the fixture file.
        $this->assertStringEqualsFile('./tests/fixtures/data.js', $chart->convertDataToJavascript() . "\n");
    }

    public function testDonutChartHasLabelAndValueColumnInJson()
    {
        $chart = new Chart(Chart::TYPE_DONUT, $this->fakeTable);
        $json = $chart->convertDataToJavascript();
        $this->assertRegExp('/"' . Chart::COL_LABEL . '":/', $json);
        $this->assertRegExp('/"' . Chart::COL_VALUE . '":/', $json);
    }
}

