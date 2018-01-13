<?php

namespace Deplink\Constraints\Values;

use Deplink\Constraints\Context;
use Deplink\Constraints\Exceptions\IncorrectJsonValueException;
use Deplink\Constraints\Exceptions\TraversePathNotFoundException;
use Deplink\Constraints\JsonValue;

class NullValue implements JsonValue
{
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
     * Get JSON structure after evaluating constraints.
     *
     * @param string|null $key Limit scope, use empty value to get whole structure.
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function get($key, $constraints)
    {
        if(!empty($key)) {
            throw new TraversePathNotFoundException("Null value doesn't support traversing.");
        }

        return null;
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
        if(!empty($key)) {
            throw new TraversePathNotFoundException("Null value doesn't support traversing.");
        }

        return null;
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
        if (!is_null($obj)) {
            throw new IncorrectJsonValueException("Provided JSON isn't a null value.", $obj);
        }

        return new NullValue();
    }
}
