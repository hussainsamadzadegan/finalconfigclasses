<?php

namespace finalconfigclasses\cfg\misc;

use finalconfigclasses\cfg\ConfigBean;

class IndexedNodeChangeEvent extends NodeChangeEvent
{
	private $index;

	public function __construct(ConfigBean $source, $propertyName,
			$oldValue, $newValue, $index)
			{
				parent::__construct($source, $propertyName, $oldValue, $newValue);
				$this->index = $index;
	}

	public function getIndex()
	{
		return $this->index;
	}
}