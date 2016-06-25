<?php

namespace finalconfigclasses\util;

use Mysidia\Resource\Collection\Set;
use Mysidia\Resource\Collection\CollectionIterator;
use Mysidia\Resource\Exception\NosuchElementException;
use Mysidia\Resource\Exception\UnsupportedOperationException;
use Mysidia\Resource\Native\Objective;

class Collections {
	public $EMPTY_SET;
	
	/**
	 * @var Singleton The reference to *Singleton* instance of this class
	 */
	private static $instance;
	
	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton The *Singleton* instance.
	 */
	public static function getInstance()
	{
		if (null === static::$instance) {
			static::$instance = new static();
		}
	
		return static::$instance;
	}
	
	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct()
	{
		$this->EMPTY_SET = new EmptySet();
	}
	
	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone()
	{
	}
	
	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup()
	{
	}
	
}

class EmptySet extends Set {
	public function iterator() {
		return new EmptyIterator();
	}
	
	public function size() {return 0;}
	
	public function contains(Objective $obj) {return false;}
	
	public function __toString() {
		return "[]";
	}
}

class EmptyIterator extends CollectionIterator {
	/**
	 * The current method, returns the current entry in the iterator.
	 * @access public
	 * @return Entry
	 */
	public function current()
	{
		throw new NosuchElementException();
	}
	
	/**
	 * The next method, returns the next object in the iteration.
	 * @access public
	 * @return Objective
	 */
	public function next() {
		throw new NosuchElementException();
	}
	
	/**
	 * The hasNext method, checks if the iterator has next entry.
	 * This is a final method, and thus can not be overriden by child class.
	 * @access public
	 * @return Entry
	 * @final
	 */
	final public function hasNext()
	{
		return false;
	}
	
	/**
	 * The nextEntry method, returns the next entry in iteration.
	 * This is a final method, and thus can not be overriden by child class.
	 * @access public
	 * @return Entry
	 * @final
	 */
	final public function nextEntry()
	{
		throw new NosuchElementException();
	}
	
	/**
	 * The remove method, removes from the underlying value associated with the current key in iteration.
	 * @access public
	 * @return Void
	 */
	public function remove()
	{
		throw new UnsupportedOperationException();
	}
	
}