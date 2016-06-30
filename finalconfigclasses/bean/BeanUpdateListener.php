<?php

namespace finalconfigclasses\bean;

use finalconfigclasses\util\EventListener;

interface BeanUpdateListener extends EventListener {

	public function prepareUpdate(BeanUpdateEvent $evt);

	public function activateUpdate(BeanUpdateEvent $evt);

	public function rollbackUpdate(BeanUpdateEvent $evt);

}