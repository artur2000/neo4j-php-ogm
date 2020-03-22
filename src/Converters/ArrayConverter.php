<?php

/*
 * This file is part of the GraphAware Neo4j PHP OGM package.
 *
 * (c) GraphAware Ltd <info@graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\OGM\Converters;

use GraphAware\Neo4j\OGM\Exception\ConverterException;

class ArrayConverter extends Converter
{
    const DEFAULT_FORMAT = 'json';

    public function getName()
    {
        return 'array';
    }

    public function toDatabaseValue($value, array $options)
    {
        if (null === $value) {
            return $value;
        }

        if (is_array($value) || $value instanceof \Doctrine\Common\Collections\Collection) {

            $format = isset($options['format']) ? $options['format'] : self::DEFAULT_FORMAT;

            $items = [];

            foreach ($value as $value) {
                if (is_object($value)) {
                    if ($value instanceof \JsonSerializable) {
                        $items[] = json_decode(json_encode($value), true);
                    } else if (method_exists($value, 'getId')) {
                        $items[] = $value->getId();
                    } else {
                        throw new \Exception(sprintf('Method getId not available in %s', get_class($value)));
                    }
                } else {
                    $items[] = $value;
                }
            }

            if (self::DEFAULT_FORMAT === $format) {
                return json_encode($items);
            }

            try {
                return json_encode($items);
            } catch (\Exception $e) {
                throw new ConverterException(sprintf('Error while converting array: %s', $e->getMessage()));
            }

        }

        throw new ConverterException(sprintf('Unable to convert value in converter "%s"', $this->getName()));
    }

    public function toPHPValue(array $values, array $options)
    {
        if (!isset($values[$this->propertyName]) || null === $values[$this->propertyName]) {
            return null;
        }

        $format = isset($options['format']) ? $options['format'] : self::DEFAULT_FORMAT;
        $v = $values[$this->propertyName];

        if (self::DEFAULT_FORMAT === $format) {
            $arrayData = json_decode($v, true);
        } else {
            $arrayData = json_decode($v, true);
        }

        if ($options['class']) {
            $className = $options['class'];
            if (class_exists($options['class'])) {
                $empty = new $className();
                if (method_exists($empty, 'fromArray')) {
                    $objectArray = [];
                    foreach ($arrayData as $rowData) {
                        $objectArray[] = $empty::fromArray($rowData);
                    }
                    return $objectArray;
                } else {
                    return $arrayData;
                }
            } else {
                return $arrayData;
                //throw new ConverterException(sprintf('Class does not exists: %s', $options['class']));
            }
        }

    }

}
