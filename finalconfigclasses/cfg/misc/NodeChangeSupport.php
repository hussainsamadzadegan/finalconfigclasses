<?php

namespace finalconfigclasses\cfg\misc;

use finalconfigclasses\cfg\ConfigBean;

final class NodeChangeSupport extends \Threaded {
	/**
	 * The source component for lifecycle events that we will fire.
	 */
	private $source = null;
	
	/**
	 * The set of registered LifecycleListeners for event notifications.
	 */
	private $listeners = array();//new NodeChangeListener[0];
	
	//private readonly Object listenersLock = new Object(); // Lock object for changes to listeners
	
	/**
	 * Construct a new LifecycleSupport object associated with the specified
	 * Lifecycle component.
	 *
	 * @param lifecycle
	 *            The Lifecycle component that will be the source of events that
	 *            we fire
	 */
	public function __construct(ConfigBean $source) {
		if ($source == null) {
			throw new \InvalidArgumentException('null source');
		}
		$this->source = $source;
	}
	
	// --------------------------------------------------------- Public Methods
	
	/**
	 * Add a lifecycle event listener to this component.
	 *
	 * @param listener
	 *            The listener to add
	 */
	public function addNodeChangeListener(NodeChangeListener $listener) {
		try {
			this-lock();
			$results = array_fill(0, count($this->listeners) + 1, NULL);
			for ($i = 0; $i < count(listeners); $i++)
				$results[$i] = $this->listeners[$i];
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
	public function getNodeChangeListeners() {
		return $this->listeners;
	}
	
	/**
	 * Remove a lifecycle event listener from this component.
	 *
	 * @param listener
	 *            The listener to remove
	 */
	public function removeNodeChangeListener(NodeChangeListener $listener) {
		try {
			$this-lock();
			$n = -1;
			for ($i = 0; $i < count($this->listeners); $i++) {
				if ($this->listeners[$i] === $listener) {
					$n = $i;
					break;
				}
			}
			if ($n < 0)
				return;
				$results = array_fill(0, count($this->listeners) - 1, NULL);//new NodeChangeListener[listeners.Length - 1];
				$j = 0;
				for ($i = 0; $i < count($this->listeners); $i++) {
					if ($i != $n)
						$results[$j++] = $this->listeners[$i];
				}
				$this->listeners = $results;
		} finally {
			$this-unlock();
		}
	}
	
	public function fireNodeChange($propertyName, $oldValue,
			$newValue) {
				if ($oldValue != null && $newValue != null && $oldValue == $newValue) {
					return;
				}
				fireNodeChange2(new NodeChangeEvent($this->source, $propertyName,
						$oldValue, $newValue));
	}
	
	public function fireNodeChange2(NodeChangeEvent $evt) {
		$oldValue = $evt->getOldValue();
		$newValue = $evt->getNewValue();
		$propertyName = $evt->getPropertyName();
		if ($oldValue != null && $newValue != null && $oldValue == $newValue) {
			return;
		}
		$interested = $this->listeners;
		for ($i = 0; $i < count($interested); $i++)
			$interested[$i]->nodeChange($evt);
	}
	
	public function fireIndexedNodeChange($propertyName, $index,
			$oldValue, $newValue) {
				fireNodeChange2(new IndexedNodeChangeEvent($this->source, $propertyName,
						$oldValue, $newValue, $index));
	}
	
}