<?php

namespace Deal\Tests\Modules\Qsapi\Engine;

use Deal\Modules\Qsapi\Exporter;

/**
 * Tests of the ExporterException
 */
class ExporterExceptionTest extends \PHPUnit_Framework_TestCase
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
        $this->exception = new Exporter\ExporterException();
    }
    
    public function testExceptionExists()
    {
        $this->assertInstanceOf('Deal\Modules\Qsapi\Exporter\ExporterException', $this->exception);
    }
}
