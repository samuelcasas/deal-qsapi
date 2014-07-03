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
[For Devs](https://github.com/dwsla/deal-qsapi/wiki/Dev-usage)

[For Clients](https://github.com/dwsla/deal-qsapi/wiki/Client-usage)
