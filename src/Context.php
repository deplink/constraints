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
        if (!is_array($constraints)) {
            $constraints = func_get_args();
        }

        $this->groups[] = $constraints;
        return $this;
    }

    /**
     * Check whether $items pass the given $constraints.
     *
     * @param string|string[] $items
     * @param string|string[] $constraints
     * @return boolean
     */
    public function satisfy($items, $constraints)
    {
        $items = (array)$items;
        $constraints = (array)$constraints;

        foreach ($this->groups as $group) {
            $constraintsPerGroup = array_intersect($group, $constraints);
            $itemsPerGroup = array_intersect($group, $items);

            if (empty($itemsPerGroup) || empty($constraintsPerGroup)) {
                continue;
            }

            $pass = false;
            foreach($itemsPerGroup as $item) {
                if(in_array($item, $constraintsPerGroup)) {
                    $pass = true;
                    break;
                }
            }

            return $pass;
        }

        return true;
    }
}
