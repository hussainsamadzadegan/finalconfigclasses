<?php

namespace finalconfigclasses\cfg;

use finalconfigclasses\bean\PropertyUpdate;
use finalconfigclasses\bean\UpdateType;
use Mysidia\Resource\Native\Objective;

class ConfigPropertyUpdate extends PropertyUpdate
{
	private $isDynamic;
	private $originalSetBit;
	private $proposedSetBit;

/*	public ConfigPropertyUpdate(String propertyName, bool isDynamic, bool originalSetBit,
			bool proposedSetBit) : base(propertyName)
			{
				this.isDynamic = isDynamic;
				this.originalSetBit = originalSetBit;
				this.proposedSetBit = proposedSetBit;
	}
*/
	public function __construct($propertyName, $updateType, $addedOrRemoved, $isDynamic,
			$originalSetBit, $proposedSetBit) /*: base(propertyName, updateType, addedOrRemoved)*/
			{
		parent::__construct($propertyName, $updateType, $addedOrRemoved);
		$this->isDynamic = $isDynamic;
		$this->originalSetBit = $originalSetBit;
		$this->proposedSetBit = $proposedSetBit;
	}

	public function __toString()
	{
		switch ($this->updateType)
		{
			case UpdateType::CHANGE:
				return '' . $this->propertyName . " (CHANGE)(Dynamic=" . $this->getIsDynamic() . ")";
			case UpdateType::ADD:
				return $this->propertyName . " (ADD " . $this->addedOrRemoved . ")(Dynamic=" . $this->getIsDynamic() . ")";
			case UpdateType::REMOVE:
				return $this->propertyName . " (REMOVE " . $this->addedOrRemoved . ")(Dynamic=" . $this->getIsDynamic() . ")";
		}
		throw new \AssertionError(
				"Change type " . $this->updateType . " illegal");
	}

	public function equals(Objective $obj) {
		if (!($obj instanceof ConfigPropertyUpdate))
			return false;
		$propertyupdate = $obj;
		if (!$this->propertyName.equals($propertyupdate->propertyName))
			return false;
		if ($this->updateType != $propertyupdate->updateType)
			return false;
		else
			return $this->addedOrRemoved === $propertyupdate->addedOrRemoved;
	}

	public function getIsDynamic()
	{
		return $this->isDynamic;
	}

	public function isDerivedUpdate()
	{
		return !$this->originalSetBit && !$this->proposedSetBit;
	}

	public function isUnsetUpdate()
	{
		return $this->originalSetBit && !$this->proposedSetBit;
	}
}
