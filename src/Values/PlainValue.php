<?php

namespace Deplink\Constraints\Values;

use Deplink\Constraints\Context;
use Deplink\Constraints\Exceptions\IncorrectJsonValueException;
use Deplink\Constraints\Exceptions\TraversePathNotFoundException;
use Deplink\Constraints\JsonValue;

class PlainValue implements JsonValue
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param Context $context
     * @return $this
     */
    public function setContext(Context $context)
    {
        // Do nothing, we don't need context.

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
        if(!empty($key)) {
            throw new TraversePathNotFoundException("Plain value doesn't support traversing.");
        }

        return $this;
    }

    /**
     * Get JSON structure after evaluating constraints.
     *
     * @param string|string[] $constraints
     * @return mixed
     */
    public function get($constraints)
    {
        return $this->value;
    }

    /**
     * Get raw JSON structure (as is).
     *
     * @param string|string[] $constraints
     * @return mixed
     */
    public function getRaw($constraints)
    {
        return $this->value;
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
        if(!is_numeric($obj) && !is_string($obj) && !is_bool($obj)) {
            throw new IncorrectJsonValueException("Provided JSON isn't a plain value.", $obj);
        }

        $json = new PlainValue();
        $json->value = $obj;

        return $json;
    }
}
