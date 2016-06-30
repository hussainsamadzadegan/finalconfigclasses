<?php

namespace finalconfigclasses\bean\misc;

use finalconfigclasses\util\EventListener;

interface PropertyChangeListener extends EventListener {

	public function propertyChange(PropertyChangeEvent $evt);
	 
}