# JsonDumpReader

[![Build Status](https://secure.travis-ci.org/JeroenDeDauw/JsonDumpReader.png?branch=master)](http://travis-ci.org/JeroenDeDauw/JsonDumpReader)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/JeroenDeDauw/JsonDumpReader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/JeroenDeDauw/JsonDumpReader/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/JeroenDeDauw/JsonDumpReader/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/JeroenDeDauw/JsonDumpReader/?branch=master)
[![Dependency Status](https://www.versioneye.com/php/jeroen:json-dump-reader/dev-master/badge.svg)](https://www.versioneye.com/php/jeroen:json-dump-reader/dev-master)

[![Download count](https://poser.pugx.org/jeroen/json-dump-reader/d/total.png)](https://packagist.org/packages/jeroen/json-dump-reader)
[![Latest Stable Version](https://poser.pugx.org/jeroen/json-dump-reader/version.png)](https://packagist.org/packages/jeroen/json-dump-reader)

**JsonDumpReader** provides ways to read from and iterate through the [Wikibase](http://wikiba.se/)
entities in a Wikibase Repository JSON dump.

Works with PHP 5.4+, PHP7 and HHVM.

## Installation

To add this package as a local, per-project dependency to your project, simply add a
dependency on `jeroen/json-dump-reader` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
JsonDumpReader 1.x:

```js
{
    "require": {
        "jeroen/json-dump-reader": "~1.0"
    }
}
```

## Release notes

### Version 1.0.0 (2015-09-29)

* Added `DumpLineReader`, which is used by `JsonDumpIterator` and implemented by `JsonDumpReader`
* Added `Bz2DumpReader`, also implementing `DumpLineReader`
* Added `JsonDumpIterator::onError`
* Added ci command that runs PHPUnit, PHPCS, PHPMD and covers tags validation

### Version 0.2.0 (2015-09-29)

* Installation with Wikibase DataModel Serialization 2.x is now supported
* Installation restrictions of Wikibase DataModel version have been dropped

### Version 0.1.0 (2014-10-22)

Initial release with

* `JsonDumpReader` to read entity JSON from the dump
* `JsonDumpIterator` to iterate through the dump as if it was a collection of `EntityDocument`
