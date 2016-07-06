<?php

namespace finalconfigclasses\cfg;

use Mysidia\Resource\Collection\HashMap;
use finalconfigclasses\bean\misc\PropertyChangeSupport;
use finalconfigclasses\cfg\misc\NodeChangeSupport;
use finalconfigclasses\bean\misc\BeanUpdateSupport;
use finalconfigclasses\util\Utils;
use finalconfigclasses\util\Collections;
use Mysidia\Resource\Collection\ArrayList;
use finalconfigclasses\bean\misc\PropertyChangeListener;
use finalconfigclasses\cfg\misc\NodeChangeListener;
use finalconfigclasses\bean\BeanUpdateListener;
use Mysidia\Resource\Native\Object;

abstract class BaseConfigBean extends \Threaded implements ConfigBean {
	/** The unique ID of bean(needed for clone and merge algorithms). */
	private $beanID;
	/** Contains the value of simple attributes. */
	private $attr;
	/** Contains the reference to other related ConfigBeans. */
	private $prop;
	/** Contains whether attribute/property has been explicitly set in this bean. */
	private $setProp;
	/** Contains default values for attribute/property. */
	private $defValue;
	/** Whether change to attribute/property is appliable at run-time or needs system restart. */
	private $dynaProp;
	
	/**
	 * The transient is neccassary for clone process, the XStream would ignore the
	 * transient fields(needed for transfering configbeans via JMX).
	 */
	private $_parent;
	private $changeSupport;
	private $nodeSupport;
	private $updateSupport;
	//private volatile transient ReentrantReadWriteLock propertiesLock;
	private $propertiesFile;
	//private final transient XmlContainer container;
	
	private $lockID;
	private $document;
	private $name;
	private $keyPrefix;
	//private boolean _modified;
	
	////////////////////////////////////////////////////////////////////////////
	//
	// Constructors...
	//
	////////////////////////////////////////////////////////////////////////////	
	
	//full constructor which should be defined in all subclasses(the correct
	//behavior of clone() method is dependent to this constructor)
	public function __construct($beanID,
			$defValue,
			$dynaProp,
			$parent,
			$propertiesFile,
			/*final XmlContainer container,*/
			$lockID,
			//final ReentrantReadWriteLock propertiesLock,
			$document,
			$name,
			$keyPrefix) {
				$this->attr = new HashMap();
				$this->prop = new HashMap();
				$this->setProp = new HashMap();
				
				if($beanID == null)
					throw new \InvalidArgumentException("BeanID should not be null.");
				
				$this->beanID = $beanID;
				$this->defValue = $defValue;
				//setting all properties to their defualt values
				if($defValue != null)
					$this->_getAttr()->putAll($defValue);
				$this->_parent = $parent;
				$this->dynaProp = $dynaProp;
				//this.container = container;
				//if(propertiesLock != null) {
				//	if(lockID == null)
				//		throw new IllegalArgumentException("LockID should not be null.");
				//}
				$this->lockID = $lockID;
				//$this->propertiesLock = propertiesLock;
				$this->propertiesFile = $propertiesFile;
				$this->document = $document;
				$this->name = $name; 
				$this->keyPrefix = $keyPrefix;

				$this->changeSupport = new PropertyChangeSupport($this);
				$this->nodeSupport = new NodeChangeSupport($this);
				$this->updateSupport = new BeanUpdateSupport($this);
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	// Attribute and property getters/setters...
	//
	////////////////////////////////////////////////////////////////////////////
	
	protected final function getAttr($name) {
		/*if(_getIsWriteOnly())
		 throw new UnsupportedOperationException("The data is write only.");*/
		return $this->_getAttr()->get($name);
	}
	
	protected final function setAttr($name, $value) {
		/*if(_getIsReadOnly())
		 throw new UnsupportedOperationException("The data is read only.");*/
		$this->_getAttr()->put($name, $value);
	}
	
	protected final function getProp($name) {
		/*if(_getIsWriteOnly())
		 throw new UnsupportedOperationException("The data is write only.");*/
		return $this->_getProp()->get($name);
	}
	
	protected final function setProp($name, $value) {
		/*if(_getIsReadOnly())
		 throw new UnsupportedOperationException("The data is read only.");*/
		$this->_getProp()->put($name, $value);
	}
	
	public final function getDefValue($propertyName) {
		/*readLock();
			try {*/
		if ($this->defValue->containsKey($propertyName))
			return $this->defValue->get($propertyName);
			throw new \AssertionError("Unknown property '" . $propertyName . "'. Can not find its default value in map.");
			/*} finally {
				readUnlock();
				}*/
	}
	
	public final function isDynamic($propertyName) {
		/*readLock();
		 try {*/
		if ($this->dynaProp->containsKey($propertyName))
			return $this->dynaProp->get($propertyName);
			throw new \AssertionError("Unknown property '" . $propertyName . "'. Can not find its dynamic flag in map.");
			/*} finally {
			 readUnlock();
			 }*/
	}
	
	public final function unSet($propertyName) {
		$objArr = null;
		$this->writeLock();
		try {
			$objArr = $this->_unSet($propertyName);
		} finally {
			$this->writeUnlock();
		}
		//calling listeners out of lock block to avoid dead-lock.
		firePropertyChange($propertyName, $objArr[0], $objArr[1]);
	}
	
	public final function isSet($propertyName) {
		$this->readLock();
		try {
			return $this->_isSet($propertyName);
		} finally {
			$this->readUnlock();
		}
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	// Hashmap getters/setters...
	//
	////////////////////////////////////////////////////////////////////////////
	
	protected final function _getAttr() {
		return $this->attr;
	}
	
	protected final function _getProp() {
		return $this->prop;
	}
	
	protected final function _getDefValue() {
		return $this->defValue;
	}
	
	protected final function _getSetProp() {
		return $this->setProp;
	}
	
	protected final function _getDynaProp() {
		return $this->dynaProp;
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	// Lock/UnLock methods...
	//
	////////////////////////////////////////////////////////////////////////////
	
	protected final function readLock() {
		$this->lock();
	}
	
	protected final function readUnlock() {
		$this->unlock();
	}
	
	protected final function writeLock() {
		$this->lock();
	}
	
	protected final function writeUnlock() {
		$this->unlock();
	}
	
	public final function _getLockID() {
		return $this->lockID;
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	// Clone methods...
	//
	////////////////////////////////////////////////////////////////////////////
	
	public final function cloneThis() {
		$this->readLock();
		try {
			return $this->_clone(null, 0);
		} finally {
			$this->readUnlock();
		}
	}
	
	public final function cloneThis2(ConfigBean $parentOfCloned) {
		$this->readLock();
		try {
			return $this->_clone($parentOfCloned, 0);
		} finally {
			$this->readUnlock();
		}
	}
	
	public final function cloneSubtree() {
		$this->readLock();
		try {
			return $this->_clone(null, PHP_INT_MAX);
		} finally {
			$this->readUnlock();
		}
	}
	
	public final function  cloneSubtree2(ConfigBean $parentOfCloned) {
		$this->readLock();
		try {
			return $this->_clone($parentOfCloned, PHP_INT_MAX);
		} finally {
			$this->readUnlock();
		}
	}
	
	public final function cloneSubtree3($cloneDepth) {
		$this->readLock();
		try {
			return $this->_clone(null, $cloneDepth);
		} finally {
			$this->readUnlock();
		}
	}
	
	public final function  cloneSubtree4(ConfigBean $parentOfCloned, $cloneDepth) {
		$this->readLock();
		try {
			return $this->_clone($parentOfCloned, $cloneDepth);
		} finally {
			$this->readUnlock();
		}
	}
	
	protected final function _clone(ConfigBean $parentOfCloned, $cloneDepth) {
		$cloneObj = null;
		try {
			if($parentOfCloned != null && $parentOfCloned === this) {
				echo "The parent of config bean can not be the bean itself!";
				return null;
			}
			$clazz = new \ReflectionClass(get_class($this));
			$c = $clazz->getConstructor();	
			//using full constructor to instantiate cloned object...
			//Constructor c = getClass().getConstructor(new Class[] {String.class, HashMap.class, HashMap.class,
			//ConfigBean.class, String.class, String.class, ReentrantReadWriteLock.class, String.class, String.class, String.class});
			/*HashMap<String, Object>*/ $cloneDefValue = null;
			if($this->_getDefValue() != null)
				$cloneDefValue = Collections::cloneHashMap($this->_getDefValue());
				/*HashMap<String, Boolean>*/ $cloneDynaProp = null;
				if($this->dynaProp != null)
					$cloneDynaProp = Collections::cloneHashMap($this->dynaProp);
					
					$cloneObj = /*(BaseConfigBean)*/$c->invokeArgs(null, $this->_getBeanID(), $cloneDefValue,
							$cloneDynaProp,
							$parentOfCloned,
							$this->propertiesFile,
							$this->lockID,
							//null,//we do not place locks on cloned version
							$this->document,
							$this->name,
							$this->keyPrefix);
						
					$cloneObj->_getSetProp()->putAll($this->_getSetProp());
						
					//processing Attr map
					//cloneObj._getAttr().putAll(_getAttr());
					$itr = $this->_getAttr()->valueIterator();
					while($itr->hasNext()) {
						$ent = $itr->nextEntry();
						
						$key = $ent->getKey();
						$obj = $ent->getValue();
						if($obj == null) {
							$cloneObj->_getAttr()->put($key, null);
						} else {
							if(is_array($obj)) {
								$length = count($obj);
								$clonedArr = array();/* = Array.newInstance(obj.getClass().getComponentType(), length);*/
								for($i = 0 ; $i < $length; $i++) {
									$clonedArr[$i] = $obj[$i];
									//Array.set(clonedArr, i, Array.get(obj, i));
								}
								$cloneObj->_getAttr()->put($key, $clonedArr);
							} else {
								$cloneObj->_getAttr()->put($key, $obj);
							}
						}
						
					}
						
					//processing Prop map
					if($cloneDepth > 0) {
						$itr2 = $this->_getProp()->valueIterator();
						while($itr2->hasNext()) {
							$ent2 = $itr2->nextEntry();
						
						//for(Map.Entry<String, Object> ent : _getProp().entrySet()) {
							$key = $ent2->getKey();
							$obj = $ent2->getValue();
							if($obj == null) {
								$cloneObj->_getProp()->put($key, null);
							} else if($obj instanceof ConfigBean) {
								$cb = $obj;
								if($cb === $this) {
									echo "Warining: loop in config bean " . $this . " for prop " . $key . ", ignoring...";
									continue;
								}
								if($cb->_getParent() == null) {
									$clonedCb = $cb->cloneSubtree($cloneDepth - 1);
									$cloneObj->_getProp()->put($key, $clonedCb);
								} else if(cb._getParent()/*.equals(*/=== $this/*)*/) {
									$clonedCb = $cb->cloneSubtree($cloneObj, $cloneDepth - 1);
									$cloneObj->_getProp()->put($key, $clonedCb);
								} else {
									echo "Warning: could not find proper parent for prop " . $key . ", ignoring...";
								}
							} else if(/*obj instanceof ConfigBean[]*/Utils::isArrayOfType($obj, new ReflectionClass('finalconfigclasses\cfg\ConfigBean'))) {
								$cbArr = $obj;
								/*ArrayList<ConfigBean>*/$list = new ArrayList/*<ConfigBean>*/();
								foreach($cbArr as $cb) {
									if($cb === $this) {
										echo "Warining: loop in config bean " . $this . " for prop " . $key . ", ignoring...";
										continue;
									}
									if($cb->_getParent() == null) {
										$clonedCb = $cb->cloneSubtree($cloneDepth - 1);
										$list->add(new ObjectWrapper($clonedCb));
									} else if($cb->_getParent()/*.equals(*/ === this/*)*/) {
										$clonedCb = $cb->cloneSubtree($cloneObj, $cloneDepth - 1);
										$list.add(new ObjectWrapper(clonedCb));
									} else {
										echo "Warning: could not find proper parent for prop " . $key . ", ignoring...";
									}
								}
								$clonedCbArr = array();//Array.newInstance(obj.getClass().getComponentType(), list.size());
								for($i = 0 ; $i < $list->size(); $i++)
									$clonedCbArr[i] = $list->get($i)->value();
									//Array.set(clonedCbArr, i, list.get(i));
									$cloneObj->_getProp()->put($key, $clonedCbArr);
							}
						}
					}
						
		} catch (\Exception $e) {
			echo $e;
			//e.printStackTrace();
		}
		return $cloneObj;
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	// Remaining methods...
	//
	////////////////////////////////////////////////////////////////////////////
	
	protected final function _isSet($propertyName) {
		$b = $this->setProp->get($propertyName);
		return $b == null ? false : $b;
	}
	
	public final function _conditionalUnset($isUnsetUpdate, $propertyName) {
		$objArr = null;
		$this->writeLock();
		try {
			if($isUnsetUpdate)
				$objArr = $this->_unSet($propertyName);
		} finally {
			$this->writeUnlock();
		}
		//calling listeners out of lock block to avoid dead-lock.
		if($isUnsetUpdate)
			$this->firePropertyChange($propertyName, $objArr[0], $objArr[1]);
	}
	
	protected final function _markSet($propertyName, $set) {
		/*
		 * Difference between this method and _loadMarkSet() method is
		 * that this method is designed for setCacheSize/unset methods
		 * and will set modification field to true. Modification state
		 * will show the user changes to config object.
		 */
		if ($set)
			$this->setProp->put($propertyName, true);
			else
				$this->setProp->put($propertyName, null);
				//_setModified(true);
	}
	
	protected final function _loadMarkSet($propertyName, $set) {
		/*
		 * Difference between this method and _markSet() method is
		 * that this method is designed for load process and will NOT
		 * set modification field to true. Modification state is NOT
		 * for load() method changes on config object.
		 */
		if ($set)
			$this->setProp->put($propertyName, true);
			else
				$this->setProp->put($propertyName, null);
	}
	
	protected final function _postSet($propertyName, $oldValue, $newValue) {
		$this->_markSet($propertyName, true);
	}
	
	protected final function _unSet($propertyName) {
		$defVal = $this->getDefValue($propertyName);
		$oldVal = $this->getAttr($propertyName);
		$this->setAttr($propertyName, $defVal);
		$this->_markSet($propertyName, false);
		return array($oldVal, $defVal);
	}
	
	protected final function firePropertyChange($propertyName, $oldVal,
			$newVal) {
			 /* The if is necessary, it is because that a changeSupport is transient and
			  * you may call _postSet or unSet on a cloned version(which its changeSupport field is null).
			  * So the code should behavie as expected(it should not throw NullPointerException here).
			  */
				if ($this->changeSupport != null)
					$this->changeSupport->firePropertyChange($propertyName, $oldVal,
							$newVal);
	}
	
	protected final function fireIndexedPropertyChange($propertyName, $index,
			$oldVal, $newVal) {
			 /* The if is necessary, it is because that a changeSupport is transient and
			  * you may call _postSet or unSet on a cloned version(which its changeSupport field is null).
			  * So the code should behavie as expected(it should not throw NullPointerException here).
			  */
				if ($this->changeSupport != null)
					$this->changeSupport->fireIndexedPropertyChange($propertyName, $index, $oldVal,
							$newVal);
	}
	
	protected final function fireNodeChange($propertyName, $oldVal,
			$newVal) {
			 /* The if is necessary, it is because that a nodeSupport is transient and
			  * you may call _postSet or unSet on a cloned version(which its nodeSupport field is null).
			  * So the code should behavie as expected(it should not throw NullPointerException here).
			  */
				if ($this->nodeSupport != null)
					$this->nodeSupport->fireNodeChange($propertyName, $oldVal,
							$newVal);
	}
	
	protected final function fireIndexedNodeChange($propertyName, $index,
			$oldVal, $newVal) {
			 /* The if is necessary, it is because that a nodeSupport is transient and
			  * you may call _postSet or unSet on a cloned version(which its nodeSupport field is null).
			  * So the code should behavie as expected(it should not throw NullPointerException here).
			  */
				if ($this->nodeSupport != null)
					$this->nodeSupport->fireIndexedNodeChange($propertyName, $index, $oldVal,
							$newVal);
	}
	
	/*public boolean _isModified() {
	 return _modified;
	 }
	
	 public void _setModified(boolean modified) {
	 this._modified = modified;
	 }*/
	
	public function needsSubdiff($propertyName) {
		$this->readLock();
		try {
			$obj = $this->getProp($propertyName);
			return ($obj instanceof ConfigBean) || Utils::isArrayOfType(obj, new ReflectionClass('finalconfigclasses\cfg\ConfigBean')) /*(obj instanceof ConfigBean[])*/;
		} finally {
			$this->readUnlock();
		}
	}
	
	public function _newDiffHelper() {
		$diffHelper = new ConfigDiffHelper($this/*, updateSupport*/);
		return $diffHelper;
		//return "finalconfigclasses.cfg.ConfigDiffHelper";
	}
	
	public final function addPropertyChangeListener(
			PropertyChangeListener $propertychangelistener) {
				$this->changeSupport->addPropertyChangeListener($propertychangelistener);
	} 
	
	public final function removePropertyChangeListener(
			PropertyChangeListener $propertychangelistener) {
				$this->changeSupport->removePropertyChangeListener($propertychangelistener);
	}
	
	public final function getPropertyChangeListeners() {
		return $this->changeSupport->getPropertyChangeListeners();
	}
	
	public final function addNodeChangeListener(NodeChangeListener $nodechangelistener) {
		$this->nodeSupport->addNodeChangeListener($nodechangelistener);
	}
	
	public final function removeNodeChangeListener(NodeChangeListener $nodechangelistener) {
		$this->nodeSupport->removeNodeChangeListener($nodechangelistener);
	}
	
	public final function getNodeChangeListeners() {
		return $this->nodeSupport->getNodeChangeListeners();
	}
	
	public final function addBeanUpdateListener(BeanUpdateListener $beanupdatelistener) {
		$this->updateSupport->addBeanUpdateListener($beanupdatelistener);
	}
	
	public final function removeBeanUpdateListener(BeanUpdateListener $beanupdatelistener) {
		$this->updateSupport->removeBeanUpdateListener($beanupdatelistener);
	}
	
	public final function getBeanUpdateListeners() {
		return $this->updateSupport->getBeanUpdateListeners();
	}
	
	public function accept(ConfigBeanVisitor $visitor) {
		if ($visitor == null) {
			throw new \InvalidArgumentException("Visitor must not be null!");
		}
		if (!$visitor->terminate()) {
			$visitor->visitBeforeChildren($this);
	
			$snapshot = null;
			$this->readLock();
			try {
				$snapshot = new HashMap();
				$snapshot->putAll($this->_getProp());
			} finally {
				$this->readUnlock();
			}
			$itr = $snapshot->valueIterator();
			//$values = $snapshot->values();
			while($itr->hasNext()) {
				$obj = $itr->next();
				if($obj instanceof ConfigBean) {
					$obj->accept($visitor);
				} else if(Utils::isArrayOfType($obj, new ReflectionClass('finalconfigclasses\cfg\ConfigBean'))/*obj instanceof ConfigBean[]*/) {
					$arrCopy = null;
					$this->readLock();
					try {
						$arrCopy = Utils::cloneArray($obj);/*(ConfigBean[])(((ConfigBean[])obj).clone());*/
					} finally {
						$this->readUnlock();
					}
					foreach($arrCopy as $cb) {
						$cb->accept($visitor);
					}
				}
			}
	
			$visitor->visitAfterChildren($this);
		}
	}
	
	public abstract function _getXPath();
	
	public function _getBeanID() {
		return $this->beanID;
	}
	
	public function _getBeanClass() {
			return new \ReflectionClass(get_class($this));
		}
		
		public final function _getParent() {
			return $this->_parent;
		}
	
		public final function _getPropertiesLock() {
			return $this;
		}
	
		/*public final void _setPropertiesLock(ReentrantReadWriteLock propertiesLock) {
			this.propertiesLock = propertiesLock;
		}*/
		
		protected final function _getUpdateSupport() {
			return $this->updateSupport;
		}
		
	    public final function _getPropertiesFile() {
		    return $this->propertiesFile;
	    }	
	//	protected final XmlContainer _getXmlContainer() {
	//		return container;
	//	}
		
		protected final function _getDocument() {
			return $this->document;
		}
		
		protected final function _getName() {
			return $this->name;
		}
		
		protected final function _getKeyPrefix() {
			return $this->keyPrefix;
		}
		
	    ////////////////////////////////////////////////////////////////////////////
	    //
	    // Load/Save methods...
	    //
	    ////////////////////////////////////////////////////////////////////////////
	
		//every child must overwrite this method and must not use parent version
		public abstract function load();
	
		//every child must overwrite this method and must not use parent version
		public abstract function save();
		
	    ////////////////////////////////////////////////////////////////////////////
	    //
	    // Utility and Helper methods...
	    //
	    ////////////////////////////////////////////////////////////////////////////
		
		protected static final function arrayToList($array) {
			$list = new ArrayList();
			if($array != null) {
				$length = count($array);
				for($i = 0; $i < $length; $i++)
					$list->add(new ObjectWrapper($array[$i]));
			}
			return $list;
		}
		
		protected static final function listToArray(ArrayList $list/*, Class componentType*/) {
			$arr = array();//Array.newInstance(componentType, list.size());
			for($i = 0; $i < $list->size(); $i++)
				$arr[$i] = $list->get($i)->value();
				//Array.set(arr, i, list.get(i));
			return $arr;
		}
		
		//this methods works by 'equality of references' not 'equality of values'.
		protected static final function indexOf(ArrayList $list, $obj) {
			$idx = -1;		
			for($i = 0; $i < $list->size(); $i++) {
				$elem = $list->get($i)->value();			
				if($elem === $obj) {//reference equality...
					$idx = $i;
					break;
				}
			}
			return $idx;
		}
		
		protected static final function eval($localLocPrefix, $ct, $name) {
			$result = "";
			if($localLocPrefix != null) {
				$result .= localLocPrefix;
				$result .= '/';
			}
			$result .= ct;
			if($name != null) {
				$result .= "[@name='";
				$result .= $name;
				$result .= "']";
			}
			return $result;
		}
		
		protected static final function typedArr($arr, $type) {
			if("boolean" === type) {
				$result = array();//new boolean[count($arr)];
				for($i = 0; $i < count($arr); $i++)
					$result[$i] = Utils::parseBoolean($arr[$i]);
				return $result;
			}
			if("char" === type) {
				$result = array();//new char[arr.length];
				for($i = 0; $i < count($arr); $i++)
					$result[$i] = $arr[$i][0];
				return $result;			
			}
	//		if("byte".equals(type)) {
	//				return "getByte";
	//		}
			/*if(short.class.equals(type)) {
				short[] result = new short[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Short.parseShort(arr[i]);
				return result;			
			}*/
			if("int" === type) {
				$result = array();//new int[arr.length];
				for($i = 0; $i < count($arr); $i++)
					$result[$i] = (int)$arr[$i];
				return $result;			
			}
			/*
			if(long.class.equals(type)) {
				long[] result = new long[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Long.parseLong(arr[i]);
				return result;	
			}*/
				
			if("float" === type) {
				$result = array();//new float[arr.length];
				for($i = 0; $i < count($arr); $i++)
					$result[$i] = (float)$arr[$i];
				return $result;
			}
			/*
			if(double.class.equals(type)) {
				double[] result = new double[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Double.parseDouble(arr[i]);
				return result;
			}*/
			///////////////////////////////////
			if("Boolean" === type) {
				$result = array();//new Boolean[arr.length];
				for($i = 0; $i < count($arr); $i++)
					if($arr[$i] != null)
						$result[$i] = new \Mysidia\Resource\Native\Boolean(Utils::parseBoolean($arr[$i]));
				return $result;
			}
			if("Character" === type) {
				$result = array();//new Character[arr.length];
				for($i = 0; $i < count($arr); $i++)
					if($arr[$i] != null)
						$result[$i] = new \Mysidia\Resource\Native\Char($arr[$i][0]);
				return $result;			
			}
	//		if("byte".equals(type)
	//				|| "Byte".equals(type)) {
	//				return "getByte";
	//		}
			/*if(Short.class.equals(type)) {
				Short[] result = new Short[arr.length];
				for(int i = 0; i < arr.length; i++)
					if(arr[i] != null)
						result[i] = Short.valueOf(arr[i]);
				return result;			
			}*/
			if("Integer" === type) {
				$result = array();//new Integer[arr.length];
				for($i = 0; $i < count($arr); $i++)
					if ($arr[$i] != null)
						$result[$i] = new \Mysidia\Resource\Native\Integer((int)$arr[$i]);
				return $result;			
			}
			/*
			if(Long.class.equals(type)) {
				Long[] result = new Long[arr.length];
				for(int i = 0; i < arr.length; i++)
					if (arr[i] != null)
						result[i] = Long.valueOf(arr[i]);
				return result;	
			}
			*/
			if("Float" === type) {
				$result = array();//new Float[arr.length];
				for($i = 0; $i < count($arr); $i++)
					if ($arr[$i] != null)
						$result[$i] = new \Mysidia\Resource\Native\Float((float)$arr[$i]);
				return $result;
			}
			/*
			if(Double.class.equals(type)) {
				Double[] result = new Double[arr.length];
				for(int i = 0; i < arr.length; i++)
					if (arr[i] != null)
						result[i] = Double.valueOf(arr[i]);
				return result;
			}	
			*/	
			if("String" === type) {
				return $arr;
			}
			return null;
		}
		
		private static final function createDP(HashMap $defValue) {
			if($defValue == null)
				return null;
			$result = new HashMap();
			$itr = $defValue->valueIterator();
			while($itr->hasNext()) {
				$ent = $itr->nextEntry();
			
			//for(String key : defValue.keySet()) {
				//all properties are dynamic
				$result.put($ent->getKey(), new \Mysidia\Resource\Native\Boolean(true));
			}
			return result;
		}
		
}

class ObjectWrapper extends Object {
	/**
	 * Coerces and sets value
	 *
	 * @param mixed    $value
	 * @param null|int $flags
	 */
	public function __construct($value = null, $flags = null)
	{
		parent::__construct($value, $flags);
	}
}