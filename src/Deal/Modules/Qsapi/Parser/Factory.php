<?php

namespace Deal\Modules\Qsapi\Parser;

use \ReflectionClass;

/**
 * An implementation of a Parser factory
 *
 * @author David Weinraub <david.weinraub@dws.la.>
 */
class Factory implements FactoryInterface
{

    /**
     * Defaults for engine and exporter
     * ß
     * @var array
     */
    protected static $defaultClasses = array(
        'engine'    => 'Deal\Modules\Qsapi\Engine\PhpStrParse',
        'exporter'  => 'Deal\Modules\Qsapi\Exporter\Identity',
    );
    
    /**
     * @param array $options options to create the parser instance
     *      Typically, this will be an array of engine and exporter options.
     * 
     *      ```
     *      $options = array(
     *          'engine' => array(
     *              // engine options here
     *          ),
     * 
     *          'exporter' => array(
     *              // exporter options here
     *          ),
     *      );
     *      ```
     * 
     *      Standard engine options include:
     * 
     *      'instance' : an instance implementing EngineInterface
     *      'class'    : class name for the engine instance
     *      'constructorParams' : an array of params to pass to the constructor
     * 
     *      Standard exporter options include:
     * 
     *      'instance' : an instance implementing ExporterInterface
     *      'class'    : class name for the exporter instance
     *      'constructorParams' : an array of params to pass to the constructor
     * 
     * @return ParserInterface
     *    
     */
    public static function create(array $options = array())
    {
        $engine = static::buildEngine($options);
        $exporter = static::buildExporter($options);
        $parser = new Parser($engine, $exporter);
        return $parser;
    }
    
    /**
     * Build the engine instance from params
     * 
     * @param array $options
     * @return Engine\EngineInterface
     * @throws \RuntimeException
     */
    protected static function buildEngine(array $options)
    {
        return static::buildGeneric('Engine', 'engine', $options);
    }

    /**
     * Build the exporter instance from params
     * 
     * @param array $options
     * @return Exporter\ExporterInterface
     * @throws \RuntimeException
     */
    protected static function buildExporter(array $options)
    {
        return static::buildGeneric('Exporter', 'exporter', $options);
    }
    
    /**
     * A generic builder for the two common types: 'engine' and 'exporter'
     * 
     * @param string $type 'engine' or 'exporter'. Used in exception message
     * @param string $key key in the $option arrsy under which to find the options for this type
     * @param array $options
     * @return mixed and instance of the given type
     * @throws \RuntimeException
     */
    protected static function buildGeneric($type, $key, $options)    
    {
        if (!array_key_exists($key, $options)) {
            $class = static::$defaultClasses[$key];
            $instance = new $class();
            return $instance;
        }
        
        // Drill down using our key
        $options = $options[$key];
        
        // Instance provided?
        if (isset($options['instance'])) {
            return $options['instance'];
        }
        
        // Class provided?
        if (array_key_exists('class', $options)) {
            $reflection = new ReflectionClass($options['class']);
            
            // constructor params
            $params = array_key_exists('constructorParams', $options)
                ? $options['constructorParams']
                : array();
            $instance = $reflection->newInstanceArgs($params);
            return $instance;        
        }
        
        // Otherwise, throw an exception
        throw new \RuntimeException(ucfirst($type) . ' params provided, but no instance or classname specified.');
    }    
}
