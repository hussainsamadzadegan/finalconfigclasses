<?php

namespace finalconfigclasses\bean\misc;

/**
 * A class which broadcast changes to interested listeners.
 */
final class PropertyChangeSupport extends \Stackable
{

	/**
	 * The source component for lifecycle events that we will fire.
	 */
	private $source = null;


	/**
	 * The set of registered LifecycleListeners for event notifications.
	 */
	private $listeners = array();

	//private readonly Object listenersLock = new Object(); // Lock object for changes to listeners

	/**
	 * Construct a new LifecycleSupport object associated with the specified
	 * Lifecycle component.
	 *
	 * @param lifecycle The Lifecycle component that will be the source
	 *  of events that we fire
	 */
	public function __construct($source) {
		if ($source == null) {
			throw new \InvalidArgumentException('null source');
		}
		$this->source = $source;
	}

	// --------------------------------------------------------- Public Methods


	/**
	 * Add a lifecycle event listener to this component.
	 *
	 * @param listener The listener to add
	 */
	public function addPropertyChangeListener(PropertyChangeListener $listener)
	{
		try {
			$this->lock();
			$results = array_fill(0, count($this->listeners) + 1, NULL);
			//new PropertyChangeListener[listeners.Length + 1];
			for ($i = 0; i < count($this->listeners); $i++)
				$results[i] = $this->listeners[i];
			$results[count($this->listeners)] = $listener;
			$this->listeners = $results;
		} finally {
			$this->unlock();
		}

	}

	/**
	 * Get the lifecycle listeners associated with this lifecycle. If this
	 * Lifecycle has no listeners registered, a zero-length array is returned.
	 */
	public function getPropertyChangeListeners()
	{
		return $this->listeners;
	}

	/**
	 * Remove a lifecycle event listener from this component.
	 *
	 * @param listener The listener to remove
	 */
	public function removePropertyChangeListener(PropertyChangeListener $listener)
	{

		try {
			$this->lock();
			$n = -1;
			for ($i = 0; $i < count($this->listeners); $i++) {
				if ($this->listeners[$i] === $listener) {
					$n = $i;
					break;
				}
			}
			if ($n < 0)
				return;
			$results = array(0, count($this->listeners) - 1, NULL);
			//new PropertyChangeListener[listeners.Length - 1];
			$j = 0;
			for ($i = 0; $i < count($this->listeners); $i++) {
				if ($i != $n)
					$results[$j++] = $this->listeners[$i];
			}
			$this->listeners = $results;
		} finally {
			$this->unlock();
		}
	}

	public function firePropertyChange($propertyName,
			$oldValue, $newValue) {
				if ($oldValue != null && $newValue != null && $oldValue == $newValue) {
					return;
				}
				firePropertyChange(new PropertyChangeEvent($this->source, $propertyName,
						$oldValue, $newValue));
	}

	public function firePropertyChange(PropertyChangeEvent $evt) {
		$oldValue = $evt->getOldValue();
		$newValue = $evt->getNewValue();
		$propertyName = $evt->getPropertyName();
		if ($oldValue != null && $newValue != null && $oldValue == $newValue) {
			return;
		}
		$interested = $this->listeners;
		for ($i = 0; $i < count($interested); $i++)
			$interested[$i]->propertyChange($evt);
	}

	public function fireIndexedPropertyChange($propertyName, $index,
			$oldValue, $newValue)
	{
		firePropertyChange(new IndexedPropertyChangeEvent
				($this->source, $propertyName, $oldValue, $newValue, $index));
	}

}

