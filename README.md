# JsonDumpReader

[![Build Status](https://secure.travis-ci.org/JeroenDeDauw/JsonDumpReader.png?branch=master)](http://travis-ci.org/JeroenDeDauw/JsonDumpReader)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/JeroenDeDauw/JsonDumpReader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/JeroenDeDauw/JsonDumpReader/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/JeroenDeDauw/JsonDumpReader/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/JeroenDeDauw/JsonDumpReader/?branch=master)
[![Dependency Status](https://www.versioneye.com/php/jeroen:json-dump-reader/dev-master/badge.svg)](https://www.versioneye.com/php/jeroen:json-dump-reader/dev-master)

[![Download count](https://poser.pugx.org/jeroen/json-dump-reader/d/total.png)](https://packagist.org/packages/jeroen/json-dump-reader)
[![Latest Stable Version](https://poser.pugx.org/jeroen/json-dump-reader/version.png)](https://packagist.org/packages/jeroen/json-dump-reader)

**JsonDumpReader** provides ways to read from and iterate through the [Wikibase](http://wikiba.se/)
entities in a Wikibase Repository JSON dump. You can find more information on the format on the
[Wikidata dump download page](https://www.wikidata.org/wiki/Wikidata:Database_download).

Works with PHP 5.5+, including PHP7

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

## Usage

All services are constructed via the `JsonDumpFactory` class:

```php
use Wikibase\JsonDumpReader\JsonDumpFactory;
$factory = new JsonDumpFactory();
```

There are two types of services provided by this library: those implementing `DumpReader` and those
implementing `Iterator`. The former allow you to ask for the next line of the dump. They are the most
low level, with the different implementations supporting different dump file formats (such as `.json`
and `.json.bz2`). The iterators all depend on a `DumpReader`, and allow you to easily iterate over
all entities in the dump. They differ in how much additional processing they do, from nothing (returning
the JSON stings) to fully deserializing the entities into `EntityDocument` objects.

### Reading some lines from a dump

```php
$dumpReader = $factory->newExtractedDumpReader( '/tmp/wd-dump.json' );
echo 'First line: ' . $dumpReader->nextJsonLine();
echo 'Second line: ' . $dumpReader->nextJsonLine();
```

```php
$dumpReader = $factory->newGzDumpReader( '/tmp/wd-dump.json.gz' );
echo 'First line: ' . $dumpReader->nextJsonLine();
echo 'Second line: ' . $dumpReader->nextJsonLine();
```

```php
$dumpReader = $factory->newBz2DumpReader( '/tmp/wd-dump.json.bz2' );
echo 'First line: ' . $dumpReader->nextJsonLine();
echo 'Second line: ' . $dumpReader->nextJsonLine();
```

### Resume reading from a previous position

```php
$dumpReader = $factory->newGzDumpReader( '/tmp/wd-dump.json.gz' );
echo 'First line: ' . $dumpReader->nextJsonLine();
echo 'Second line: ' . $dumpReader->nextJsonLine();

$newReader = $factory->newGzDumpReader( '/tmp/wd-dump.json.gz' );
$newReader->seekToPosition( $dumpReader->getPosition() );

echo 'Third line: ' . $newReader->nextJsonLine();
```

### Iterating though the JSON

```php
$dumpReader = $factory->newGzDumpReader( '/tmp/wd-dump.json.gz' );
$dumpIterator = $factory->newStringDumpIterator( $dumpReader );

foreach ( $dumpIterator as $jsonLine ) {
	echo 'You can haz JSON: ' . $jsonLine;
}
```

### Creating an EntityDocument iterator

```php
$dumpReader = $factory->newBz2DumpReader( '/tmp/wd-dump.json.bz2' );
$dumpIterator = $factory->newEntityDumpIterator( $dumpReader );

foreach ( $dumpIterator as $entityDocument ) {
	echo 'At entity ' . $entityDocument->getId()->getSerialization();
}
```

The iterator approach taken by this library is lazy and can easily be combined with iterator tools
provided by PHP, such as `LimitIterator` and `CallbackFilterIterator`.

## Running the tests

For tests only

    composer test

For style checks only

	composer cs

For a full CI run

	composer ci

## Release notes

### Version 1.3.0 (2015-11-23)

* `JsonDumpFactory::newGzDumpReader` now takes an optional `$initialPosition` argument

### Version 1.2.0 (2015-11-23)

* Added `SeekableDumpReader` interface
	* `JsonDumpFactory::newGzDumpReader` now returns a `SeekableDumpReader`
	* `JsonDumpFactory::newExtractedDumpReader` now returns a `SeekableDumpReader`
* `ExtractedDumpReader` is now package private (no breaking changes to it will be made before 2.0)

### Version 1.1.0 (2015-11-12)

* Added `JsonDumpFactory::newGzDumpReader` for gzip dump support

### Version 1.0.1 (2015-11-10)

* Fixed of-by-one error in resumption of `ExtractedDumpReader` via `getPosition`

### Version 1.0.0 (2015-11-08)

* Added `JsonDumpFactory`
	* Added `JsonDumpFactory::newBz2DumpReader`
	* Added `JsonDumpFactory::newExtractedDumpReader`
	* Added `JsonDumpFactory::newStringDumpIterator`
	* Added `JsonDumpFactory::newObjectDumpIterator`
	* Added `JsonDumpFactory::newEntityDumpIterator`
* Removed `JsonDumpReader` (now `JsonDumpFactory::newExtractedDumpReader`)
* Removed `JsonDumpIterator` (now `JsonDumpFactory::newEntityDumpIterator`)
* Added ci command that runs PHPUnit, PHPCS, PHPMD and covers tags validation

### Version 0.2.0 (2015-09-29)

* Installation with Wikibase DataModel Serialization 2.x is now supported
* Installation restrictions of Wikibase DataModel version have been dropped

### Version 0.1.0 (2014-10-22)

Initial release with

* `JsonDumpReader` to read entity JSON from the dump
* `JsonDumpIterator` to iterate through the dump as if it was a collection of `EntityDocument`
