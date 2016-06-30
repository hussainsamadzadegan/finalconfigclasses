<?php

namespace finalconfigclasses\bean\misc;

class IndexedPropertyChangeEvent extends PropertyChangeEvent
{
	private $index;

	/**
	 * Constructs a new <code>IndexedPropertyChangeEvent</code> object.
	 *
	 * @param source  The bean that fired the event.
	 * @param propertyName  The programmatic name of the property that
	 *             was changed.
	 * @param oldValue      The old value of the property.
	 * @param newValue      The new value of the property.
	 * @param index index of the property element that was changed.
	 */
	public function __construct($source, $propertyName,
			$oldValue, $newValue,
			$index)
	{
		parent::__construct($source, $propertyName, $oldValue, $newValue);
		$this->index = $index;
	}


	/**
	 * Gets the index of the property that was changed.
	 *
	 * @return The index specifying the property element that was
	 *         changed.
	 */
	public function getIndex()
	{
		return $this->index;
	}
}
