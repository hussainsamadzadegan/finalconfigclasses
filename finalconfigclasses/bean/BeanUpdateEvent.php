<?php

namespace finalconfigclasses\bean;

use finalconfigclasses\util\EventObject;

abstract class BeanUpdateEvent extends EventObject {
	private $proposedBean;
	
	public function __construct($sourceBean, $proposedBean) {
		parent::__construct($sourceBean);
		$this->proposedBean = $proposedBean;
	}
	
	public function getSource() {
		return parent::getSource();
	}
	
	public function getSourceBean() {
		return getSource();
	}
	
	public function getProposedBean() {
		return $this->proposedBean;
	}
	
	public abstract function /*List<PropertyUpdate>*/ getUpdateList();
}