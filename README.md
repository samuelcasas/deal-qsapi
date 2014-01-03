Deal-QSAPI
==========

Deal-QSAPI is a PHP-based query-string parser package for API's that allows a client to specify:

* filter conditions
* field-limiting
* pagination
* ordering

It is extensible using different engines and exporters.

Requirements
------------

* PHP 5.3

Installation
------------

Using Composer, add the following to your project's `composer.json`:

```
{
    "repositories" : [
        {
            "type" : "vcs",
            "url" : "https://github.com/dwsla/deal-qsapi.git"
        }
    ],

    "require" : {
        "deal-qsapi" : "dev-master"
    }
}
```

Usage
-----

```
use Deal\Modules\Qsapi\Parser\ParserFactory;
use Deal\Modules\Qsapi\Parser\ParserException;

try {
    $result = ParserFactory::create()->parse($_SERVER['QUERY_STRING']);
} catch (ParserException $e) {
    // probably return a 400 Bad Request to the client
}

```

The `$result` variable is an array containing keys and embedded structures under the following keys:

* filter
* fields
* exclude
* order
* page
* limit

Deal-QSAPI operates internally using Engines and Exporters. Engines parse the query 
string into a fixed intermediate format. Exporters can be used to massage that structure
into a form that is most useful for your project's model layer.

Current engines:

* Fixed: a trivially stupid and worthless engine that always returns the same result. Serves as an example for how to create an engine.
* PhpParseStr: an engine outputs exactly what PHP would provide in the $_GET array
* NestedFilter: an engine that really does something, largely characterized by handling syntax of the form `?filter[myfield]=myvalue`

Current exporters:

* Identity - trivial, just dumps the result of the initial parse result.
* Monga - outputs results that can be passed directly into the Monga layer (not yet implemented)

More documentation required. 