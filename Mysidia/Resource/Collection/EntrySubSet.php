<?php

namespace Mysidia\Resource\Collection;

use Mysidia\Resource\Native\Objective;

/**
 * The abstract EntrySubSet Class, extending from the abstract MapSet Class.
 * It defines a standard subset to hold entries in a SubMap, must be extended by child classes.
 * @category  Resource
 * @package   Collection
 * @author    Ordland
 * @copyright Mysidia RPG, Inc
 * @link      http://www.mysidiarpg.com
 * @abstract
 *
 */
abstract class EntrySubSet extends MapSet
{
    /**
     * The size property, defines the current size of the Set.
     * @access protected
     * @var Int
     */
    protected $size = -1;

    /**
     * Constructor of EntrySubSet Class, it simply calls parent constructor.
     *
     * @param SubMap $map
     *
     * @access public
     * @return Void
     */
    public function __construct(SubMap $map)
    {
        parent::__construct($map);
    }

    /**
     * The contains method, checks if a given entry is already on the EntrySubSet.
     *
     * @param Objective $object
     *
     * @access public
     * @return Boolean
     */
    public function contains(Objective $object)
    {
        if (!($object instanceof MapEntry)) {
            return false;
        }
        $entry = $object;
        $key = $entry->getKey();
        if (!$this->map->inRange($key)) {
            return false;
        }
        $map = $this->map->getMap();
        $mapEntry = $map->getEntry($key);

        return ($mapEntry != null and $map->valueEquals($mapEntry->getValue(), $entry->getValue()));
    }

    /**
     * The isEmpty method, checks if the EntrySubSet is empty or not.
     * @access public
     * @return Boolean
     */
    public function isEmpty()
    {
        $lowest = $this->map->absLowest();

        return ($lowest == null or $this->map->tooHigh($lowest->getKey()));
    }

    /**
     * The remove method, removes the mapping specified by the given Entry.
     *
     * @param Objective $object
     *
     * @access public
     * @return Boolean
     */
    public function remove(Objective $object)
    {
        if (!($object instanceof MapEntry)) {
            return false;
        }
        $entry = $object;
        $key = $entry->getKey();
        if (!$this->map->inRange($key)) {
            return false;
        }
        $map = $this->map->getMap();
        $mapEntry = $map->getEntry($key);
        if ($mapEntry != null and $map->valueEquals($mapEntry->getValue(), $entry->getValue())) {
            $map->deleteEntry($mapEntry);

            return true;
        }

        return false;
    }

    /**
     * The size method, returns the current size of the EntrySubSet.
     * @access public
     * @return Int
     */
    public function size()
    {
        $map = $this->map->getMap();
        if ($this->map->fromStart() and $this->map->toEnd()) {
            return $map->size();
        }
        if ($this->size == -1) {
            $this->size = 0;
            $iterator = $this->iterator();
            while ($iterator->hasNext()) {
                $this->size++;
                $iterator->next();
            }
        }

        return $this->size;
    }
}
