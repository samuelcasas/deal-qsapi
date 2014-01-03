<?php

namespace Deal\Modules\Qsapi\Parser;

use Deal\Modules\Qsapi\Engine;
use Deal\Modules\Qsapi\Exporter;

/**
 * An implementation of a QSAPI parser that focuses on producing
 * the fields necessary for Mongo:
 *
 *      * filters: array (actually, Mongo refers to 'criteria')
 *      * fields: array|null (actually, Mongo refers to 'projection')
 *      * limit: int|null
 *      * skip: int|null
 *      * order: array|null
 *
 */
class Parser implements ParserInterface
{   
    
    /**
     * @var Engine\EngineInterface
     */
    protected $engine;
    
    /**
     * @var Exporter\ExporterInterface
     */
    protected $exporter;

    /**
     * Constructor
     * 
     * @param \Deal\Modules\Qsapi\Engine\EngineInterface $engine
     * @param \Deal\Modules\Qsapi\Exporter\ExporterInterface $exporter
     */
    public function __construct(Engine\EngineInterface $engine, Exporter\ExporterInterface $exporter)
    {
        $this->setEngine($engine);
        $this->setExporter($exporter);
    }
    /**
     * Parse a given query string.
     * 
     * @param string $qs
     * @return array
     * @throws ParserException
     */
    public function parse($qs = '')
    {
        
        try {
            
            $arr = $this->engine->parse($qs);
            $result = $this->exporter->export($arr);            
            return $result;
            
        } catch (Engine\EngineException $e) {

            $msg = sprintf('Parse engine error: %s', $e->getMessage());
            throw new ParserException($msg);
            
        } catch (Exporter\ExporterException $e) {
            
            $msg = sprintf('Parser export error: %s', $e->getMessage());
            throw new ParserException($msg);
        }
    }
    
    /**
     * Set the engine
     * 
     * @param \Deal\Modules\Qsapi\Engine\EngineInterface $engine
     * @return \Deal\Modules\Qsapi\Parser\Parser
     */
    public function setEngine(Engine\EngineInterface $engine)
    {
        $this->engine = $engine;
        return $this;
    }
    
    /**
     * Get the engine
     * 
     * @return Engine\EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Set the exporter
     *  
     * @param \Deal\Modules\Qsapi\Exporter\ExporterInterface $exporter
     * @return \Deal\Modules\Qsapi\Parser\Parser
     */
    public function setExporter(Exporter\ExporterInterface $exporter)
    {
        $this->exporter = $exporter;
        return $this;
    }
    /**
     * Get the exporter
     * 
     * @return Exporter\ExporterInterfaceß 
     */
    public function getExporter()
    {
        return $this->exporter;
    }
}
