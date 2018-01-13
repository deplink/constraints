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
    private $values;

    /**
     * @param Context $context
     * @return $this
     */
    public function setContext(Context $context)
    {
        foreach($this->values as $value) {
            $value->setContext($context);
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
        $parts = explode('.', $key, 2);
        $index = $parts[0];

        if(!is_int($index)) {
            throw new TraversePathNotFoundException("Traversing the array value must be done using the numeric keys.");
        }

        if($index < 0 || $index >= count($this->values)) {
            throw new TraversePathNotFoundException("Cannot traverse the array value '$key', index out of range.");
        }

        if(isset($parts[1])) {
            return $this->values[$index]->traverse($parts[1], $constraints);
        }

        return $this->values[$index];
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
        $results = [];
        foreach($this->values as $value) {
            $results[] = $value->get($constraints);
        }

        return $results;
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
        $results = [];
        foreach($this->values as $value) {
            $results[] = $value->getRaw($constraints);
        }

        return $results;
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
        $json->values = [];

        $factory = new Factory();
        foreach($obj as $item) {
            $json->values[] = $factory->parseJson($item, $nameSeparator, $constraintsSeparator);
        }

        return $json;
    }
}
