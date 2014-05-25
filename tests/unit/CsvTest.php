<?php

/**
 * Testing the CSV related functionality.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Advancedform
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Advancedform_XH
 */

require_once './vendor/autoload.php';
require_once './advfrm.php';

function stsl($string)
{
    return $string;
}

function e($et, $ft, $fn)
{

}

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class CsvTest extends PHPUnit_Framework_TestCase
{
    private $_rootFolder;

    private $_csvFile;

    private $_dataFolderMock;

    private $_dbMock;

    private $_eMock;

    public function setUp()
    {
        global $pth, $plugin_tx;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $this->_rootFolder = vfsStream::url('test/');
        $this->_csvFile = $this->_rootFolder . 'test.csv';
        $plugin_tx = array(
            'advancedform' => array(
                'error_form_missing' => 'form %s missing'
            )
        );
        $this->_setUpMocks();
    }

    private function _setUpMocks()
    {
        $this->_dataFolderMock = new PHPUnit_Extensions_MockFunction(
            'Advancedform_dataFolder', $this
        );
        $this->_dataFolderMock->expects($this->any())
            ->will($this->returnValue($this->_rootFolder));
        $this->_dbMock = new PHPUnit_Extensions_MockFunction(
            'Advancedform_db', $this
        );
        $this->_dbMock->expects($this->any())->will($this->returnValue(
            array(
                'test' => array(
                    'fields' => array(
                        array('field' => 'foo', 'type' => 'text'),
                        array('field' => 'bar', 'type' => 'text')
                    )
                )
            )
        ));
        $this->_eMock = new PHPUnit_Extensions_MockFunction('e', $this);
    }

    public function tearDown()
    {
        $this->_dataFolderMock->restore();
        $this->_dbMock->restore();
        $this->_eMock->restore();
    }

    /**
     * @dataProvider dataForEscape
     */
    public function testEscape($field, $expected)
    {
        $this->assertEquals($expected, Advancedform_escapeCsvField($field));
    }

    public function dataForEscape()
    {
        return array(
            array('foo', 'foo'),
            array('foo"bar', '"foo""bar"'),
            array("foo\nbar", "\"foo\nbar\"")
        );
    }

    /**
     * @dataProvider dataForRead
     */
    public function testRead($separator, $contents)
    {
        global $plugin_cf;

        $plugin_cf['advancedform']['csv_separator'] = $separator;
        file_put_contents($this->_csvFile, $contents);
        $expected = array(
            array('foo' => 'foo value', 'bar' => 'bar"value'),
            array('foo' => 'foo value2', 'bar' => 'bar"value2')

        );
        $this->assertEquals($expected, Advancedform_readCsv('test'));
    }

    public function dataForRead()
    {
        return array(
            array('', "foo value\tbar\"value\nfoo value2\tbar\"value2\n"),
            array(
                ';',
                "foo value;\"bar\"\"value\"\nfoo value2;\"bar\"\"value2\"\n"
            )
        );
    }

    public function testReadNonExistingForm()
    {
        global $e;

        $matcher = array(
            'tag' => 'li',
            'content' => 'form foo missing'
        );
        $this->assertFalse(Advancedform_readCsv('foo'));
        $this->assertTag($matcher, $e);
    }

    /**
     * @dataProvider dataForCantRead
     */
    public function testCantRead($separator)
    {
        global $plugin_cf;

        $plugin_cf['advancedform']['csv_separator'] = $separator;
        $errorReporting = error_reporting(0);
        $this->_eMock->expects($this->once())->with(
            'cntopen', 'file', $this->_csvFile
        );
        Advancedform_readCsv('test');
        error_reporting($errorReporting);
    }

    public function dataForCantRead()
    {
        return array(
            array(''),
            array(';')
        );
    }

    /**
     * @dataProvider dataForAppend
     */
    public function testAppend($separator, $expected)
    {
        global $plugin_cf;

        $plugin_cf['advancedform']['csv_separator'] = $separator;
        $_POST = array(
            'advfrm-foo' => 'foo value',
            'advfrm-bar' => 'bar"value'
        );
        Advancedform_appendCsv('test');
        $this->assertStringEqualsFile($this->_csvFile, $expected);
    }

    public function dataForAppend()
    {
        return array(
            array(';', "foo value;\"bar\"\"value\"\n"),
            array('', "foo value\tbar\"value\n")
        );
    }

    public function testAppendFails()
    {
        global $pluginc_cf;

        $plugin_cf['advancedform']['csv_separator'] = '';
        $_POST = array(
            'advfrm-foo' => 'foo value',
            'advfrm-bar' => 'bar"value'
        );
        chmod($this->_rootFolder, 0);
        $this->_eMock->expects($this->once())->with(
            'cntwriteto', 'file', $this->_csvFile
        );
        $errorReporting = error_reporting(0);
        Advancedform_appendCsv('test');
        error_reporting($errorReporting);
    }
}

?>
