<?php

namespace finalconfigclasses\cfg\misc;

use finalconfigclasses\util\EventObject;
use finalconfigclasses\cfg\ConfigBean;

class NodeChangeEvent extends EventObject
{
	private $propertyName;
	private $oldValue;
	private $newValue;

	public function __construct(ConfigBean $source, $propertyName,
			$oldValue, $newValue)
			 {
			parent::__construct($source);
				$this->propertyName = $propertyName;
				$this->newValue = $newValue;
				$this->oldValue = $oldValue;
	}

	public function getPropertyName()
	{
		return $this->propertyName;
	}

	public function getOldValue()
	{
		return $this->oldValue;
	}

	public function getNewValue()
	{
		return $this->newValue;
	}
}
