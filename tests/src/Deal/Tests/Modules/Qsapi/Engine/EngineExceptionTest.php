<?php

namespace Deal\Tests\Modules\Qsapi\Exporter;

use Deal\Modules\Qsapi\Engine;

/**
 * Tests of the ExporterException
 */
class EngineExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ExporterException
     */
    protected $exception;
    
    /**
     * Setup
     */
    public function setUp()
    {
        $this->exception = new Engine\EngineException();
    }
    
    public function testExceptionExists()
    {
        $this->assertInstanceOf('Deal\Modules\Qsapi\Engine\EngineException', $this->exception);
    }
}
