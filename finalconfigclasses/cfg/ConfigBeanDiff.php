<?php

namespace finalconfigclasses\cfg;

use finalconfigclasses\util\Collections;
use Mysidia\Resource\Collection\LinkedHashSet;
use finalconfigclasses\bean\UpdateType;
use Mysidia\Resource\Collection\ArrayList;

class ConfigBeanDiff extends ConfigBeanUpdateEvent {
	private $updateSet;
	private $updateList;
	private $hasNonDynamicUpdates;
	
	public function __construct($sourceBean, $proposedBean/*, int updateID, int beanDiffID*/)
	{
		parent::__construct($sourceBean, $proposedBean/*, updateID*/);
		$this->updateSet = Collections::getInstance()->EMPTY_SET;
		/*this.beanDiffID = beanDiffID;*/
	}
	
	public function recordChange($propertyName, $isDynamic)
	{
		$this->addUpdate(new ConfigPropertyUpdate(
				$propertyName,
				UpdateType::CHANGE,
				null,
				$isDynamic,
				$this->getSourceBean()->isSet($propertyName),
				$this->getProposedBean()->isSet($propertyName)));
		$this->checkAndSetNonDynamicUpdates($isDynamic);
	}
	
	public function recordRemoval($propertyName, $removedObj, $isDynamic)
	{
		$this->addUpdate(new ConfigPropertyUpdate(
				$propertyName,
				UpdateType::REMOVE,
				$removedObj,
				$isDynamic,
				$this->getSourceBean()->isSet($propertyName),
				$this->getProposedBean()->isSet($propertyName)));
		$this->checkAndSetNonDynamicUpdates($isDynamic);
	}
	
	public function recordAddition($propertyName, $addedObj, $isDynamic)
	{
		$this->addUpdate(new ConfigPropertyUpdate(
				$propertyName,
				UpdateType::ADD,
				$addedObj,
				$isDynamic,
				$this->getSourceBean()->isSet($propertyName),
				$this->getProposedBean()->isSet($propertyName)));
		$this->checkAndSetNonDynamicUpdates($isDynamic);
	}
	
	public function size()
	{
		return $this->updateSet->size();
	}
	
	public /*List<PropertyUpdate>*/function getUpdateList()
	{
		if($this->updateList == null) {
			$this->updateList = new ArrayList()/*<PropertyUpdate>*/;
			$this->updateList->addAll($this->updateSet);
		}
		return $this->updateList;
	}
	
	private function checkAndSetNonDynamicUpdates($isDynamic)
	{
		if(!$isDynamic && !$this->hasNonDynamicUpdates)
			$this->hasNonDynamicUpdates = true;
	}
	
	public function hasNonDynamicUpdates()
	{
		return $this->hasNonDynamicUpdates;
	}
	
	public function __toString()
	{
		$res = "" . $this->getSource() . " (" . $this->updateSet->size() . " updateSet)";
		
		$iterator = $this->updateSet->iterator();
		
		while ($iterator->hasNext()) {
			$next = $iterator->next();
			$res .= "\n  " . $next;
		}
		
		return $res;
	}
	
	private function addUpdate(ConfigPropertyUpdate $propertyupdate)
	{
		if($this->updateSet === Collections::getInstance()->EMPTY_SET)
			$this->updateSet = new LinkedHashSet();
		$this->updateSet->add($propertyupdate);
		$this->updateList = null;	
	}
	
}