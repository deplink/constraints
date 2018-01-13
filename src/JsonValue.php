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
     * Get value under given key with constraints evaluation.
     *
     * @param string $key
     * @param string|string[] $constraints
     * @return JsonValue
     * @throws TraversePathNotFoundException
     */
    public function traverse($key, $constraints);

    /**
     * Get JSON structure after evaluating constraints.
     *
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function get($constraints);

    /**
     * Get raw JSON structure (as is).
     *
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function getRaw($constraints);

    /**
     * @param mixed $obj
     * @param string $nameSeparator
     * @param string $constraintsSeparator
     * @return JsonValue
     * @throws IncorrectJsonValueException
     */
    public static function parse($obj, $nameSeparator = ':', $constraintsSeparator = ',');
}
