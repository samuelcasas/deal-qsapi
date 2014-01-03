<?php

namespace Deal\Tests\Modules\Qsapi\Parser;

use Deal\Modules\Qsapi\Engine;
use Deal\Modules\Qsapi\Exporter;
use Deal\Modules\Qsapi\Parser;

/**
 * Tests of the Parser
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEngine()
    {
        $engine = $this->getMock('Deal\Modules\Qsapi\Engine\EngineInterface');
        $exporter = $this->getMock('Deal\Modules\Qsapi\Exporter\ExporterInterface');
        $parser = new Parser\Parser($engine, $exporter);
        $this->assertSame($engine, $parser->getEngine());
    }
    
    public function testGetExporter()
    {
        $engine = $this->getMock('Deal\Modules\Qsapi\Engine\EngineInterface');        
        $exporter = $this->getMock('Deal\Modules\Qsapi\Exporter\ExporterInterface');
        $parser = new Parser\Parser($engine, $exporter);
        $this->assertSame($exporter, $parser->getExporter());
    }
    
    public function testParseWithCleanData()
    {
        $expectedValue = array(
            'k1' => 'v1',
        );
        $engine = $this->getMock('Deal\Modules\Qsapi\Engine\EngineInterface', array('parse'));
        $engine->expects($this->once())
                ->method('parse')
                ->with('k1=v1')
                ->will($this->returnValue($expectedValue));        
        
        $exporter = $this->getMock('Deal\Modules\Qsapi\Exporter\ExporterInterface', array('export'));
        $exporter->expects($this->once())
                ->method('export')
                ->with($expectedValue)
                ->will($this->returnValue($expectedValue));

        $parser = new Parser\Parser($engine, $exporter);
        $this->assertEquals($expectedValue, $parser->parse('k1=v1'));
    }    

    
    /**
     * @expectedException Deal\Modules\Qsapi\Parser\ParserException
     */
    public function testParseThrowsParserExceptionWhenEngineThrowsEngineException()
    {
        $expectedValue = array(
            'k1' => 'v1',
        );
        $engine = $this->getMock('Deal\Modules\Qsapi\Engine\EngineInterface', array('parse'));
        $engine->expects($this->once())
            ->method('parse')
            ->with('k1=v1')
            ->will($this->throwException(new Engine\EngineException()));
                
        $exporter = $this->getMock('Deal\Modules\Qsapi\Exporter\ExporterInterface');

        $parser = new Parser\Parser($engine, $exporter);
        $result = $parser->parse('k1=v1');
    }
    
    /**
     * @expectedException Deal\Modules\Qsapi\Parser\ParserException
     */
    public function testParseThrowsParserExceptionWhenExporterThrowsExporterException()
    {
        $expectedValue = array(
            'k1' => 'v1',
        );
        $engine = $this->getMock('Deal\Modules\Qsapi\Engine\EngineInterface', array('parse'));
        $engine->expects($this->once())
                ->method('parse')
                ->with('k1=v1')
                ->will($this->returnValue($expectedValue));
                
        $exporter = $this->getMock('Deal\Modules\Qsapi\Exporter\ExporterInterface', array('export'));
        $exporter->expects($this->once())
            ->method('export')
            ->with($expectedValue)
            ->will($this->throwException(new Exporter\ExporterException()));

        $parser = new Parser\Parser($engine, $exporter);
        $result = $parser->parse('k1=v1');
    }    
}
