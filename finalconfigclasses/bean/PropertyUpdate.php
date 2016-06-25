<?php

namespace finalconfigclasses\bean;

use finalconfigclasses\util\BasicEnum;
use Mysidia\Resource\Native\Objective;
use finalconfigclasses\util\Utils;

class UpdateType extends BasicEnum {
	const CHANGE = 0;
	const ADD = 1;
	const REMOVE = 2;
}

class PropertyUpdate implements Objective {
	protected $propertyName;
	protected $updateType;
	protected $addedOrRemoved;
	
/*	public function __construct($propertyName) {
		$this->propertyName = $propertyName;
		$this->updateType = UpdateType::CHANGE;
	}*/
	
	public function __construct($propertyName, $updateType, $addedOrRemoved) {
		$this->propertyName = $propertyName;
		$this->updateType = $updateType;
		$this->addedOrRemoved = $addedOrRemoved;
	}
	
	public function getPropertyName() {
		return $this->propertyName;
	}
	
	public function getUpdateType() {
		return $this->updateType;
	}
	
	public function getAddedObject() {
		return $this->updateType != UpdateType::ADD ? null : $this->addedOrRemoved;
	}
	
	public function resetAddedObject($obj) {
		$this->addedOrRemoved = $obj;
	}
	
	public function getRemovedObject() {
		return $this->updateType != UpdateType::REMOVE ? null : $this->addedOrRemoved;
	}
	
	public function __toString()
	{
		switch ($this->updateType) {
			case UpdateType::CHANGE:
				return "" . $this->propertyName . " (CHANGE)";
			case UpdateType::ADD:
				return "" . $this->propertyName . " (ADD " . $this->addedOrRemoved . ")";
			case UpdateType::REMOVE:
				return $this->propertyName ." (REMOVE " . $this->addedOrRemoved . ")";
		}
		throw new \AssertionError(
				"Change type " . $this->updateType . " illegal");
	}
	
	public function equals(Objective $obj) {
		if (!($obj instanceof PropertyUpdate))
			return false;
		$propertyupdate = $obj;
		if (!$this->propertyName.equals($propertyupdate->propertyName))
			return false;
		if ($this->updateType != $propertyupdate->updateType)
			return false;
		else
			return $this->addedOrRemoved === $propertyupdate->addedOrRemoved;
	}
	
	public function hashCode() {
		return Utils::stringHashCode($this->propertyName);
	}
	
	public function isRemoveUpdate() {
		return $this->updateType == UpdateType::REMOVE;
	}
	
	public function isChangeUpdate() {
		return $this->updateType == UpdateType::CHANGE;
	}
	
}