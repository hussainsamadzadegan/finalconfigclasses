<?php

namespace finalconfigclasses\cfg\misc;

use finalconfigclasses\util\EventListener;

interface NodeChangeListener extends EventListener
{
	public function nodeChange(NodeChangeEvent $evt);
}