Deal-QSAPI
==========

test

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
* count_distinct

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


Engines
-------

**NestedFilter**

* Filter, single field, single value

    ```
    result = $engine->parse('filter[myfield]=myvalue')
    ```

    produces under $result['filter']

    ```
    array(
        'myfield' => array(
            '$and' => array(
                array(
                    'value' => 'myvalue',
                    'operator' => 'eq',
                ),
            ),
        ),
    );
    ```

* Filter, single field, multiple value, AND

    ```
    $engine->parse('filter[myfield]=myvalue1,myvalue2')
    ```

    produces under `$result['filter']`

    ```
    array(
        'myfield' => array(
            '$and' => array(
                array(
                    'value' => 'myvalue1',
                    'operator' => 'eq',
                ),
                array(
                    'value' => 'myvalue2',
                    'operator' => 'eq',
                ),
            ),
        ),
    );
    ```

* Single field, multiple value, OR

    ```
    $result = $engine->parse('filter[myfield]=myvalue1|myvalue2')
    ```

    produces under `$result['filter']`

    ```
    array(
        'myfield' => array(
            '$or' => array(
                array(
                    'value' => 'myvalue1',
                    'operator' => 'eq',
                ),
                array(
                    'value' => 'myvalue2',
                    'operator' => 'eq',
                ),
            ),
        ),
    );
    ```

* Multiple field, single value

    ```
    $result = $engine->parse('filter[myfield1]=myvalue1&filter[myfield2]=myvalue2')
    ```

    produces under `$result['filter']`

    ```
    array(
        'myfield1' => array(
            '$and' => array(
                array(
                    'value' => 'myvalue1',
                    'operator' => 'eq',
                ),
            ),
        ),
        'myfield2' => array(
            '$and' => array(
                array(
                    'value' => 'myvalue2',
                    'operator' => 'eq',
                ),
            ),
        ),
    );
    ```

* Single field, single value, non-equality operator

    ```
    $result = $engine->parse('filter[myfield][gte]=myvalue')
    ```

    produces under `$result['filter']`

    ```
    array(
        'myfield' => array(
            '$and' => array(
                array(
                    'value' => 'myvalue',
                    'operator' => 'gte',
                ),
            ),
        ),
    );
    ```

* Order, single field

    ```
    $result = $engine->parse('order=myfield:asc')
    ```

    produces under `$result['order']`

    ```
    array(
        'myfield' => 'asc',
    );
    ```

* Order, multiple field

    ```
    $result = $engine->parse('order=myfield1:asc&myfield2:desc')
    ```

    produces under `$result['order']`

    ```
    array(
        'myfield1' => 'asc',
        'myfield2' => 'desc',
    );
    ```

* Page, integer value

    ```
    $result = $engine->parse('page=2')
    ```

    produces `$result['page'] == 2`

* Page, empty

    ```
    $result = $engine->parse()
    ```

    produces `$result['page'] == null`

* Limit, integer value

    ```
    $result = $engine->parse('limit=10')
    ```

    produces `$result['limit'] == 10`

* Limit, integer value

    ```
    $result = $engine->parse('')
    ```

    produces under `$result['limit'] == null`

* Fields, comma-separated values

    ```
    $result = $engine->parse('fields=myfield1,myfield2')
    ```

    produces under `$result['fields']`

    ```
    array(
        'myfield1',
        'myfield2',
    ),
    ```

* Fields, empty

    ```
    $result = $engine->parse()
    ```

    produces `$result['fields'] == null`

* Exclude, comma-separated values

    ```
    $result = $engine->parse('exclude=myfield1,myfield2')
    ```

    produces under `$result['exclude']`

    ```
    array(
        'myfield1',
        'myfield2',
    ),
    ```

* Exclude, empty

    ```
    $result = $engine->parse()
    ```

    produces `$result['exclude'] == null`

* Count Distinct

    ```
    $result = $engine->parse('count_distinct=myfield')
    ```

    produces `$result['count_distinct`] == 'myfield'

