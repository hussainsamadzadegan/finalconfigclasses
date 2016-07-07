<?php

namespace finalconfigclasses\cfg\gen;

use finalconfigclasses\cfg\BaseConfigBean;
use Mysidia\Resource\Collection\HashMap;
use finalconfigclasses\cfg\ConfigBean;
use Mysidia\Resource\Native\StringWrapper;

class CacheConfigImpl extends BaseConfigBean {
	private $CLASS_KEY = "cache-config";
	
	private $KEY_1 = "cache-max-size";
	private $KEY_2 = "cache-policy-impl";
	
	// full constructor which should be defined for correct cloning
	public function __construct($beanID,
			HashMap $defValue,
			HashMap $dynaProp, $parent,
			$propertiesFile,
			/* final XmlContainer container, */
			$lockID/*, final ReentrantReadWriteLock propertiesLock*/,
			$document, $name, $keyPrefix) {
				parent::__construct($beanID, $defValue, $dynaProp, $parent, $propertiesFile, $lockID,
						/*propertiesLock,*/ $document, $name, $keyPrefix);
	}
	
	public function /*int*/ getCacheSize() {
		$this->readLock();
		try {
			return /*(Integer)*/ $this->getAttr(new StringWrapper("cacheSize"))->value();
		} finally {
			$this->readUnlock();
		}
	}
	
	public function /*void*/ setCacheSize(/*int*/ $cacheSize) {
		/*Integer*/ $oldVal = null;
		$wrapped = null;
		writeLock();
		try {
			$oldVal = /*(Integer)*/ $this->getAttr(new StringWrapper("cacheSize"))->value();
			$wrapped = new \Mysidia\Resource\Native\Integer($cacheSize);
			$this->setAttr(new StringWrapper("cacheSize"), /*cacheSize*/$wrapped);
			$this->_postSet("cacheSize", $oldVal, $cacheSize);
		} finally {
			$this->writeUnlock();
		}
		// calling listeners out of lock block to avoid dead-lock.
		$this->firePropertyChange("cacheSize", $oldVal, $cacheSize);
	}
	
	public function /*String*/ getCachePolicy() {
		$this->readLock();
		try {
			return /*(String)*/ $this->getAttr(new StringWrapper("cachePolicy"))->value();
		} finally {
			$this->readUnlock();
		}
	}
	
	public function /*void*/ setCachePolicy($cachePolicy) {
		$oldVal = null;
		$wrapped = null;
		$this->writeLock();
		try {
			$oldVal = $this->getAttr(new StringWrapper("cachePolicy"))->value();
			$wrapped = new \Mysidia\Resource\Native\StringWrapper($cachePolicy);
			$this->setAttr(new StringWrapper("cachePolicy"), /*cachePolicy*/$wrapped);
			$this->_postSet("cachePolicy", $oldVal, $cachePolicy);
		} finally {
			$this->writeUnlock();
		}
		// calling listeners out of lock block to avoid dead-lock.
		$this->firePropertyChange("cachePolicy", $oldVal, $cachePolicy);
	}
	
	public function load() {}
	public function save() {}
	public function _getXPath() {return "";}
}