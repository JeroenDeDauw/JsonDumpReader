<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Wikibase\JsonDumpReader\JsonDumpFactory;

$factory = new JsonDumpFactory();

$printError = function( string $errorMessage ) {
	echo "\n$errorMessage\n";
	exit;
};

$iterator = $factory->newEntityDumpIterator(
	$factory->newBz2DumpReader( $argv[1] ),
	\Wikibase\JsonDumpReader\Tests\TestFactory::newInstance()->newEntityDeserializer(),
	$printError
);

foreach ( $iterator as $i => $entity ) {
	echo $entity->getId() . "\n";
}

//$iterator = $factory->newStringDumpIterator(
//	$factory->newBz2DumpReader( $argv[1] ),
//	$printError
//);
//
//foreach ( new \LimitIterator( $iterator, 0, 1010 ) as $i => $line ) {
//	echo $i . ': ' . substr( $line, 0, 150 ) . "\n";
//}