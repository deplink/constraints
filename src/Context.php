<?php

namespace Deplink\Constraints;

/**
 * Describes custm user execution context, like:
 * available operating system, CPU architectures etc.
 */
class Context
{
    /**
     * @var array Array of string arrays.
     */
    private $groups;

    /**
     * Inform context that some constraints belongs to the same group.
     *
     * @param string|string[] $constraints
     * @return $this
     */
    public function group($constraints)
    {
        if(!is_array($constraints)) {
            $constraints = func_get_args();
        }

        $this->groups[] = $constraints;
        return $this;
    }
}
