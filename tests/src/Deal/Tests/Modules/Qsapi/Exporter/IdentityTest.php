<?php

namespace Deal\Tests\Modules\Qsapi\Exporter;

use Deal\Modules\Qsapi\Exporter;

/**
 * Tests of the Identity exporter
 *
 * @author David Weinraub <david.weinraub@dws.la.>
 */
class IdentityTest extends \PHPUnit_Framework_TestCase 
{

    /**
     * @var Exporter\Identity
     */
    protected $exporter;
    
    public function setUp()
    {
        $this->exporter = new Exporter\Identity();
    }
    
    public function testExport()
    {
        $this->assertEquals(array('xxx'), $this->exporter->export(array('xxx')));
    }

}
