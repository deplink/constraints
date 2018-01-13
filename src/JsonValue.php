<?php

namespace Deplink\Constraints;

use Deplink\Constraints\Exceptions\IncorrectJsonValueException;
use Deplink\Constraints\Exceptions\TraversePathNotFoundException;

/**
 * Represents JSON value in one of the types:
 * array, object, number, string, boolean, null
 */
interface JsonValue
{
    /**
     * @param Context $context
     * @return $this
     */
    public function setContext(Context $context);

    /**
     * Get JSON structure after evaluating constraints.
     *
     * @param string|null $key Limit scope, use empty value to get whole structure.
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function get($key, $constraints);

    /**
     * Get raw JSON structure (as is).
     *
     * @param string|null $key Limit scope, use empty value to get whole structure.
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function getRaw($key, $constraints);

    /**
     * @param mixed $obj
     * @param string $nameSeparator
     * @param string $constraintsSeparator
     * @return JsonValue
     * @throws IncorrectJsonValueException
     */
    public static function parse($obj, $nameSeparator = ':', $constraintsSeparator = ',');
}
