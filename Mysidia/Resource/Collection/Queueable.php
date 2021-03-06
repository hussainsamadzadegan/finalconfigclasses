<?php

namespace Mysidia\Resource\Collection;

use Mysidia\Resource\Native\Objective;

/**
 * The Queueable Interface, extending from the Collective interface.
 * It defines a standard interface for Queue type Collection objects.
 * @category  Resource
 * @package   Collection
 * @author    Ordland
 * @copyright Mysidia RPG, Inc
 * @link      http://www.mysidiarpg.com
 *
 */
interface Queueable extends Collective
{
    /**
     * The element method, retrieves but not remove the head of the queue.
     * This method throws an Exception if the Queue is empty.
     * @access public
     * @return Objective
     */
    public function element();

    /**
     * The erase method, removes but not retrieve the head of the queue.
     * @access public
     * @return Void
     */
    public function erase();

    /**
     * The offer method, inserts a specific Object to the end of the queue.
     *
     * @param Objective $object
     *
     * @access public
     * @return Boolean
     */
    public function offer(Objective $object);

    /**
     * The peek method, retrieves but not remove the head of queue.
     * This method returns NULL if Queue is empty.
     * @access public
     * @return Objective
     */
    public function peek();

    /**
     * The poll method, retrieves and removes the head of the queue at the same time.
     * @access public
     * @return Objective
     */
    public function poll();
}
