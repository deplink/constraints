<?php

namespace Deplink\Constraints\Values;

use Deplink\Constraints\Context;
use Deplink\Constraints\Exceptions\IncorrectJsonValueException;
use Deplink\Constraints\Exceptions\TraversePathNotFoundException;
use Deplink\Constraints\Factory;
use Deplink\Constraints\JsonValue;

class ObjectValue implements JsonValue
{
    /**
     * [
     *     "<key>" => [
     *         ["value" => <value>, "constraints" => [<constraints>]],
     *         ...
     *     ],
     * ]
     *
     * @var array
     */
    private $keys;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     * @return $this
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
        foreach ($this->keys as $name => $values) {
            foreach ($values as $value) {
                $value->value->setContext($context);
            }
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string|string[] $constraints
     * @param callable $callback
     * @param bool $isNested
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    private function access($key, $constraints, callable $callback, $isNested = true)
    {
        if (empty($key)) {
            $results = [];
            foreach (array_keys($this->keys) as $keyName) {
                if (!isset($results[$keyName])) {
                    $results[$keyName] = null;
                }

                $results[$keyName] = self::merge(
                    $results[$keyName],
                    $this->accessKey($keyName, null, $constraints, $callback)
                );
            }

            return $results;
        }

        $parts = explode('.', $key, 2);
        $keyName = $parts[0];
        $subKey = isset($parts[1]) ? $parts[1] : null;

        if (!isset($this->keys[$keyName])) {
            $availableKeysStr = implode(', ', array_keys($this->keys));
            throw new TraversePathNotFoundException("Key not found in JSON object, given '$keyName' key which not exists in available set: $availableKeysStr");
        }

        return $this->accessKey($keyName, $subKey, $constraints, $callback);
    }

    /**
     * @param string $keyName
     * @param string $subKey
     * @param string|string[] $constraints
     * @param callable $callback
     * @return mixed
     */
    private function accessKey($keyName, $subKey, $constraints, callable $callback)
    {
        $results = null;
        foreach ($this->keys[$keyName] as $item) {
            if ($this->context->satisfy($constraints, $item->constraints)) {
                $obj = $callback($item->value, $subKey, $constraints);
                $results = self::merge($results, $obj);
            }
        }

        return $results;
    }

    /**
     * Get JSON structure after evaluating constraints.
     *
     * @param string|null $key Limit scope, use empty value to get whole structure.
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function get($key, $constraints)
    {
        return $this->access($key, $constraints, function (JsonValue $item, $subKey, $constraints) {
            return $item->get($subKey, $constraints);
        });
    }

    /**
     * Get raw JSON structure (as is).
     *
     * @param string|null $key Limit scope, use empty value to get whole structure.
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function getRaw($key, $constraints)
    {
        return $this->access($key, $constraints, function (JsonValue $item, $subKey, $constraints) {
            return $item->getRaw($subKey, $constraints);
        });
    }

    /**
     * @param mixed $itemA
     * @param mixed $itemB
     * @return mixed
     */
    public static function merge($itemA, $itemB)
    {
        if (empty($itemA)) {
            return $itemB;
        }

        if (empty($itemB)) {
            return $itemA;
        }

        if (self::isJsonObject($itemA) || self::isJsonObject($itemB)) {
            return self::mergeObjects($itemA, $itemB);
        }

        if (is_array($itemA) && is_array($itemB)) {
            return array_merge($itemA, $itemB);
        }

        if (is_array($itemA)) {
            return array_merge($itemA, [$itemB]);
        }

        if (is_array($itemB)) {
            return array_merge([$itemA], $itemB);
        }

        return [$itemA, $itemB];
    }

    /**
     * @param array $itemA
     * @param array $itemB
     * @return array
     */
    private static function mergeObjects($itemA, $itemB)
    {
        $result = $itemA;
        foreach ($itemB as $key => $value) {
            if(!isset($result[$key])) {
                $result[$key] = null;
            }

            $result[$key] = self::merge(
                $result[$key],
                $value
            );
        }

        return $result;
    }

    /**
     * @param mixed $obj
     * @param string $nameSeparator
     * @param string $constraintsSeparator
     * @return JsonValue
     * @throws IncorrectJsonValueException
     */
    public static function parse($obj, $nameSeparator = ':', $constraintsSeparator = ',')
    {
        if (is_object($obj)) {
            $obj = (array)$obj;
        }

        if (!self::isJsonObject($obj)) {
            throw new IncorrectJsonValueException("Cannot convert value to JSON object.", $obj);
        }

        $factory = new Factory();
        $json = new ObjectValue();

        foreach ($obj as $key => $value) {
            $extKey = self::parseKey($key, $nameSeparator, $constraintsSeparator);

            if (!isset($json->keys[$extKey->name])) {
                $json->keys[$extKey->name] = [];
            }

            $json->keys[$extKey->name][] = (object)[
                "value" => $factory->parseJson($value, $nameSeparator, $constraintsSeparator),
                "constraints" => $extKey->constraints,
            ];
        }

        return $json;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isJsonObject($value)
    {
        return is_array($value) && array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * @param string $key
     * @param string $nameSeparator
     * @param string $constraintsSeparator
     * @return object Contains keys: "name", "constraints".
     */
    public static function parseKey($key, $nameSeparator = ':', $constraintsSeparator = ',')
    {
        $parts = explode($nameSeparator, $key, 2);

        $result = (object)[
            'name' => $parts[0],
            'constraints' => [],
        ];

        if (isset($parts[1])) {
            $result->constraints = explode($constraintsSeparator, $parts[1]);
        }

        return $result;
    }
}
