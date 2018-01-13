<?php

namespace Deplink\Constraints\Values;

use Deplink\Constraints\Context;
use Deplink\Constraints\Exceptions\IncorrectJsonValueException;
use Deplink\Constraints\Exceptions\TraversePathNotFoundException;
use Deplink\Constraints\Factory;
use Deplink\Constraints\JsonValue;

class ArrayValue implements JsonValue
{
    /**
     * @var JsonValue[]
     */
    private $items;

    /**
     * @param Context $context
     * @return $this
     */
    public function setContext(Context $context)
    {
        foreach($this->items as $item) {
            $item->setContext($context);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string|string[] $constraints
     * @param callable $callback
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    private function access($key, $constraints, callable $callback)
    {
        if(empty($key)) {
            $results = [];
            foreach($this->items as $item) {
                $results[] = $callback($item, null, $constraints);
            }

            return $results;
        }

        $parts = explode('.', $key, 2);
        $index = $parts[0];

        if(!is_int($index)) {
            throw new TraversePathNotFoundException("Traversing the array value must be done using the numeric keys.");
        }

        if($index < 0 || $index >= count($this->items)) {
            throw new TraversePathNotFoundException("Cannot traverse the array value '$key', index out of range.");
        }

        if(isset($parts[1])) {
            return $callback($this->items[$index], $parts[1], $constraints);
        }

        return $callback($this->items[$index], null, $constraints);
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
        return $this->access($key, $constraints, function(JsonValue $item, $subKey, $constraints) {
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
        return $this->access($key, $constraints, function(JsonValue $item, $subKey, $constraints) {
            return $item->getRaw($subKey, $constraints);
        });
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
        if(!is_array($obj) || ObjectValue::isJsonObject($obj)) {
            throw new IncorrectJsonValueException("", $obj);
        }

        $json = new ArrayValue();
        $json->items = [];

        $factory = new Factory();
        foreach($obj as $item) {
            $json->items[] = $factory->parseJson($item, $nameSeparator, $constraintsSeparator);
        }

        return $json;
    }
}
