<?php

namespace finalconfigclasses\bean\misc;

use finalconfigclasses\bean\BeanUpdateListener;
use finalconfigclasses\bean\BeanUpdateEvent;

final class BeanUpdateSupport extends \Threaded
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
	public function __construct($source)
	{
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
	public function addBeanUpdateListener(BeanUpdateListener $listener)
	{
		try {
			$this->lock();
			$results = array_fill(0, count($this->listeners) + 1 , NULL);
			//new BeanUpdateListener[listeners.Length + 1];
			for ($i = 0; $i < count($this->listeners); $i++)
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
	public function getBeanUpdateListeners()
	{
		return $this->listeners;
	}

	/**
	 * Remove a lifecycle event listener from this component.
	 *
	 * @param listener The listener to remove
	 */
	public function removeBeanUpdateListener(BeanUpdateListener $listener)
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
				$results = array_fill(0, count($this->listeners) - 1, NULL);
				//new BeanUpdateListener[listeners.Length - 1];
				$j = 0;
				for ($i = 0; $i < count($this->listeners); $i++) {
					if ($i != $n)
						$results[$j++] = $this->listeners[i];
				}
				$this->listeners = $results;
		} finally {
			$this->unlock();
		}
	}

	public function firePrepareUpdate(BeanUpdateEvent $evt) {
		$interested = $this->listeners;
		for ($i = 0; $i < count($interested); $i++)
			$interested[$i]->prepareUpdate($evt);
	}

	public function fireActivateUpdate(BeanUpdateEvent $evt) {
		$interested = $this->listeners;
		for ($i = 0; $i < count($interested); $i++)
			$interested[$i]->activateUpdate($evt);
	}

	public function fireRollbackUpdate(BeanUpdateEvent $evt) {
		$interested = $this->listeners;
		for ($i = 0; $i < count($interested); $i++)
			$interested[$i]->rollbackUpdate($evt);
	}

}
