<?php

declare( strict_types = 1 );

namespace Wikibase\JsonDumpReader\Tests;

use DataValues\BooleanValue;
use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use DataValues\NumberValue;
use DataValues\QuantityValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use DataValues\UnknownValue;
use Deserializers\Deserializer;
use Deserializers\DispatchableDeserializer;
use Wikibase\DataModel\DeserializerFactory;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityIdValue;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TestFactory {

	public static function newInstance(): self {
		return new self();
	}

	public function newEntityDeserializer(): DispatchableDeserializer {
		$factory = new DeserializerFactory(
			$this->newDataValueDeserializer(),
			new BasicEntityIdParser()
		);

		return $factory->newEntityDeserializer();
	}

	private function newDataValueDeserializer(): Deserializer {
		$dataValueClasses = [
			'boolean' => BooleanValue::class,
			'number' => NumberValue::class,
			'string' => StringValue::class,
			'unknown' => UnknownValue::class,
			'globecoordinate' => GlobeCoordinateValue::class,
			'monolingualtext' => MonolingualTextValue::class,
			'multilingualtext' => MultilingualTextValue::class,
			'quantity' => QuantityValue::class,
			'time' => TimeValue::class,
			'wikibase-entityid' => EntityIdValue::class,
		];

		return new DataValueDeserializer( $dataValueClasses );
	}

}