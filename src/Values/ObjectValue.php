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
        foreach($this->keys as $name => $values) {
            foreach($values as $value) {
                $value->value->setContext($context);
            }
        }

        return $this;
    }

    /**
     * Get value under given key with constraints evaluation.
     *
     * @param string $key
     * @param string|string[] $constraints
     * @return JsonValue
     * @throws TraversePathNotFoundException
     */
    public function traverse($key, $constraints)
    {
        // TODO: Implement traverse() method.
    }

    /**
     * Get JSON structure after evaluating constraints.
     *
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function get($constraints)
    {
        // TODO: Implement get() method.
    }

    /**
     * Get raw JSON structure (as is).
     *
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function getRaw($constraints)
    {
        // TODO: Implement getRaw() method.
    }

    /**
     * @param JsonValue $item
     * @return JsonValue New value from merging current and provided value.
     */
    public function merge(JsonValue $item)
    {
        // TODO: Implement merge() method.
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

        foreach($obj as $key => $value) {
            $extKey = self::parseKey($key, $nameSeparator, $constraintsSeparator);

            if(!isset($json->keys[$extKey->name])) {
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

        if(isset($parts[1])) {
            $result->constraints = explode($constraintsSeparator, $parts[1]);
        }

        return $result;
    }
}
