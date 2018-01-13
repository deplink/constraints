<?php

namespace Deplink\Constraints;

use Deplink\Constraints\Exceptions\IncorrectJsonValueException;
use Deplink\Constraints\Values\ArrayValue;
use Deplink\Constraints\Values\NullValue;
use Deplink\Constraints\Values\ObjectValue;
use Deplink\Constraints\Values\PlainValue;

class Factory
{
    /**
     * @var string[]
     */
    const VALUES_NS = [
        NullValue::class,
        PlainValue::class,
        ArrayValue::class,
        ObjectValue::class,
    ];

    /**
     * @param mixed $json
     * @param string $nameSeparator
     * @param string $constraintsSeparator
     * @return JsonValue
     * @throws IncorrectJsonValueException
     */
    public function parseJson($json, $nameSeparator = ':', $constraintsSeparator = ',')
    {
        foreach(self::VALUES_NS as $valueNs)  {
            try {
                /** @var JsonValue $value */
                $value = new $valueNs();

                return $value::parse($json, $nameSeparator, $constraintsSeparator);
            } catch(\Exception $e) {
                // OK, try parse to other value...
            }
        }

        throw new IncorrectJsonValueException("JSON has invalid structure.", $json);
    }
}
