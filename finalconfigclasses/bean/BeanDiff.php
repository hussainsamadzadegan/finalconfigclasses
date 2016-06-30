<?php

namespace finalconfigclasses\bean;

use finalconfigclasses\bean\BeanUpdateEvent;
use finalconfigclasses\util\Collections;
use Mysidia\Resource\Collection\ArrayList;
use Mysidia\Resource\Collection\LinkedHashSet;

class BeanDiff extends BeanUpdateEvent {
	private $updateSet;
	private $updateList;
	
	public function __construct($sourceBean, $proposedBean/*, int updateID, int beanDiffID*/)
	{
		parent::__construct($sourceBean, $proposedBean/*, updateID*/);
		$this->updateSet = Collections::getInstance()->EMPTY_SET;
		/*this.beanDiffID = beanDiffID;*/
	}
	
	public function recordChange($propertyName)
	{
		$this->addUpdate(new PropertyUpdate(
				$propertyName,
				UpdateType::CHANGE,
				null));
	}
	
	public function recordRemoval($propertyName, $removedObj)
	{
		$this->addUpdate(new PropertyUpdate(
				$propertyName,
				UpdateType::REMOVE,
				$removedObj));
	}
	
	public function recordAddition($propertyName, $addedObj)
	{
		$this->addUpdate(new PropertyUpdate(
				$propertyName,
				UpdateType::ADD,
				$addedObj));
	}
	
	public function size()
	{
		return $this->updateSet->size();
	}
	
	public /*List<PropertyUpdate>*/ function getUpdateList()
	{
		if($this->updateList == null) {
			$this->updateList = new ArrayList()/*<PropertyUpdate>*/;
			$this->updateList->addAll($this->updateSet);
		}
		return $this->updateList;
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
	
	private function addUpdate($propertyupdate)
	{
		if($this->updateSet === Collections::getInstance()->EMPTY_SET)
			$this->updateSet = new LinkedHashSet();
		$this->updateSet->add($propertyupdate);
		$this->updateList = null;
	}
	
}