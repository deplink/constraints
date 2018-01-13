<?php

namespace Deplink\Constraints;

use Deplink\Constraints\Exceptions\IncorrectJsonValueException;
use Deplink\Constraints\Exceptions\TraversePathNotFoundException;

/**
 * JSON Document which root node should be either array or object
 * (implementation extends JSON format and allows also plain values).
 */
class Json
{
    /**
     * Split key string into key (name) and list of constraints.
     *
     * @var string
     */
    public $nameSeparator = ':';

    /**
     * Split constraints into array.
     *
     * @var string
     */
    public $constraintsSeparator = ',';

    /**
     * @var JsonValue
     */
    private $root;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param array|object|string $json
     * @param string $nameSeparator
     * @param string $constraintsSeparator
     * @throws IncorrectJsonValueException
     */
    public function __construct($json, $nameSeparator = ':', $constraintsSeparator = ',')
    {
        $this->nameSeparator = $nameSeparator;
        $this->constraintsSeparator = $constraintsSeparator;

        $this->root = (new Factory())->parseJson($json, $nameSeparator, $constraintsSeparator);
        $this->setContext(new Context());
    }

    /**
     * @return string
     */
    public function getNameSeparator()
    {
        return $this->nameSeparator;
    }

    /**
     * @return string
     */
    public function getConstraintsSeparator()
    {
        return $this->constraintsSeparator;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param Context $context
     * @return Json
     */
    public function setContext($context)
    {
        $this->context = $context;
        $this->root->setContext($context);

        return $this;
    }

    /**
     * Get JSON structure for given key after evaluating constraints.
     *
     * @param string|null $key Limit scope, use empty value to get whole structure.
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function get($key, $constraints)
    {
        if (empty($key)) {
            return $this->root->get($constraints);
        }

        return $this->root
            ->traverse($key, $constraints)
            ->get($constraints);
    }

    /**
     * Get raw JSON structure (as is) for given key.
     *
     * @param string|null $key Limit scope, use empty value to get whole structure.
     * @param string|string[] $constraints
     * @return mixed
     * @throws TraversePathNotFoundException
     */
    public function getRaw($key, $constraints)
    {
        if (empty($key)) {
            return $this->root->getRaw($constraints);
        }

        return $this->root
            ->traverse($key, $constraints)
            ->getRaw($constraints);
    }
}
