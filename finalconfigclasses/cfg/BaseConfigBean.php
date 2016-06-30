<?php

namespace finalconfigclasses\cfg;

use Mysidia\Resource\Collection\HashMap;
use finalconfigclasses\bean\misc\PropertyChangeSupport;
use finalconfigclasses\cfg\misc\NodeChangeSupport;
use finalconfigclasses\bean\misc\BeanUpdateSupport;
use finalconfigclasses\util\Utils;

abstract class BaseConfigBean extends \Threaded implements ConfigBean {
	/** The unique ID of bean(needed for clone and merge algorithms). */
	private $beanID;
	/** Contains the value of simple attributes. */
	private $attr = new HashMap();
	/** Contains the reference to other related ConfigBeans. */
	private $prop = new HashMap();
	/** Contains whether attribute/property has been explicitly set in this bean. */
	private $setProp = new HashMap();
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
				if(beanID == null)
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
				
				$changeSupport = new PropertyChangeSupport($this);
				$nodeSupport = new NodeChangeSupport($this);
				$updateSupport = new BeanUpdateSupport($this);
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
			return $this->_isSet(propertyName);
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
	
	public final ConfigBean cloneThis() {
		readLock();
		try {
			return _clone(null, 0);
		} finally {
			readUnlock();
		}
	}
	
	public final ConfigBean cloneThis2(final ConfigBean parentOfCloned) {
		readLock();
		try {
			return _clone(parentOfCloned, 0);
		} finally {
			readUnlock();
		}
	}
	
	public final ConfigBean cloneSubtree() {
		readLock();
		try {
			return _clone(null, Integer.MAX_VALUE);
		} finally {
			readUnlock();
		}
	}
	
	public final ConfigBean cloneSubtree2(final ConfigBean parentOfCloned) {
		readLock();
		try {
			return _clone(parentOfCloned, Integer.MAX_VALUE);
		} finally {
			readUnlock();
		}
	}
	
	public final ConfigBean cloneSubtree3(int cloneDepth) {
		readLock();
		try {
			return _clone(null, cloneDepth);
		} finally {
			readUnlock();
		}
	}
	
	public final ConfigBean cloneSubtree4(final ConfigBean parentOfCloned, int cloneDepth) {
		readLock();
		try {
			return _clone(parentOfCloned, cloneDepth);
		} finally {
			readUnlock();
		}
	}
	
	protected final ConfigBean _clone(final ConfigBean parentOfCloned, int cloneDepth) {
		BaseConfigBean cloneObj = null;
		try {
			if(parentOfCloned != null && parentOfCloned == this) {
				System.out.println("The parent of config bean can not be the bean itself!");
				return null;
			}
			//using full constructor to instantiate cloned object...
			Constructor c = getClass().getConstructor(new Class[] {String.class, HashMap.class, HashMap.class,
			ConfigBean.class, String.class, String.class, ReentrantReadWriteLock.class, String.class, String.class, String.class});
			HashMap<String, Object> cloneDefValue = null;
			if(_getDefValue() != null)
				cloneDefValue = (HashMap<String, Object>)_getDefValue().clone();
				HashMap<String, Boolean> cloneDynaProp = null;
				if(dynaProp != null)
					cloneDynaProp = (HashMap<String, Boolean>)dynaProp.clone();
					cloneObj = (BaseConfigBean)c.newInstance(_getBeanID(), cloneDefValue,
							cloneDynaProp,
							parentOfCloned,
							propertiesFile,
							lockID,
							null,//we do not place locks on cloned version
							document,
							name,
							keyPrefix);
						
					cloneObj._getSetProp().putAll((HashMap<String, Object>)_getSetProp());
						
					//processing Attr map
					//cloneObj._getAttr().putAll(_getAttr());
					for(Map.Entry<String, Object> ent : _getAttr().entrySet()) {
						String key = ent.getKey();
						Object obj = ent.getValue();
						if(obj == null) {
							cloneObj._getAttr().put(key, null);
						} else {
							if(obj.getClass().isArray()) {
								int length = Array.getLength(obj);
								Object clonedArr = Array.newInstance(obj.getClass().getComponentType(), length);
								for(int i = 0 ; i < length; i++) {
									Array.set(clonedArr, i, Array.get(obj, i));
								}
								cloneObj._getAttr().put(key, clonedArr);
							} else {
								cloneObj._getAttr().put(key, obj);
							}
						}
					}
						
					//processing Prop map
					if(cloneDepth > 0) {
						for(Map.Entry<String, Object> ent : _getProp().entrySet()) {
							String key = ent.getKey();
							Object obj = ent.getValue();
							if(obj == null) {
								cloneObj._getProp().put(key, null);
							} else if(obj instanceof ConfigBean) {
								ConfigBean cb = (ConfigBean) obj;
								if(cb == this) {
									System.out.println("Warining: loop in config bean "+this+" for prop "+key+", ignoring...");
									continue;
								}
								if(cb._getParent() == null) {
									ConfigBean clonedCb = cb.cloneSubtree(cloneDepth - 1);
									cloneObj._getProp().put(key, clonedCb);
								} else if(cb._getParent().equals(this)) {
									ConfigBean clonedCb = cb.cloneSubtree(cloneObj, cloneDepth - 1);
									cloneObj._getProp().put(key, clonedCb);
								} else {
									System.out.println("Warning: could not find proper parent for prop "+key+", ignoring...");
								}
							} else if(obj instanceof ConfigBean[]) {
								ConfigBean[] cbArr = (ConfigBean[]) obj;
								ArrayList<ConfigBean> list = new ArrayList<ConfigBean>();
								for(ConfigBean cb : cbArr) {
									if(cb == this) {
										System.out.println("Warining: loop in config bean "+this+" for prop "+key+", ignoring...");
										continue;
									}
									if(cb._getParent() == null) {
										ConfigBean clonedCb = cb.cloneSubtree(cloneDepth - 1);
										list.add(clonedCb);
									} else if(cb._getParent().equals(this)) {
										ConfigBean clonedCb = cb.cloneSubtree(cloneObj, cloneDepth - 1);
										list.add(clonedCb);
									} else {
										System.out.println("Warning: could not find proper parent for prop "+key+", ignoring...");
									}
								}
								Object clonedCbArr = Array.newInstance(obj.getClass().getComponentType(), list.size());
								for(int i = 0 ; i < list.size(); i++)
									Array.set(clonedCbArr, i, list.get(i));
									cloneObj._getProp().put(key, clonedCbArr);
							}
						}
					}
						
		} catch (NoSuchMethodException e) {
			e.printStackTrace();
		} catch (InvocationTargetException e) {
			e.printStackTrace();
		} catch (InstantiationException e) {
			e.printStackTrace();
		} catch (IllegalAccessException e) {
			e.printStackTrace();
		}
		return cloneObj;
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	// Remaining methods...
	//
	////////////////////////////////////////////////////////////////////////////
	
	protected final function _isSet($propertyName) {
		$b = $this->setProp->get($propertyName);
		return b == null ? false : b;
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
		return array(oldVal, defVal);
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
			throw new \InvalidArgumentException(("Visitor must not be null!");
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
			//here!!!
			for(Object obj : values) {
				if(obj instanceof ConfigBean) {
					((ConfigBean) obj).accept(visitor);
				} else if(obj instanceof ConfigBean[]) {
					ConfigBean[] arrCopy;
					readLock();
					try {
						arrCopy = (ConfigBean[])(((ConfigBean[])obj).clone());
					} finally {
						readUnlock();
					}
					for(ConfigBean cb : arrCopy) {
						cb.accept(visitor);
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
		
		@SuppressWarnings("unchecked")
		protected static final ArrayList arrayToList(Object array) {
			ArrayList list = new ArrayList();
			if(array != null) {
				int length = Array.getLength(array);
				for(int i = 0; i < length; i++)
					list.add(Array.get(array, i));
			}
			return list;
		}
		
		protected static final Object listToArray(ArrayList list, Class componentType) {
			Object arr = Array.newInstance(componentType, list.size());
			for(int i = 0; i < list.size(); i++)
				Array.set(arr, i, list.get(i));
			return arr;
		}
		
		//this methods works by 'equality of references' not 'equality of values'.
		protected static final <T> int indexOf(ArrayList<T> list, T obj) {
			int idx = -1;		
			for(int i = 0; i < list.size(); i++) {
				T elem = list.get(i);			
				if(elem == obj) {//reference equality...
					idx = i;
					break;
				}
			}
			return idx;
		}
		
		protected static final String eval(String localLocPrefix, String ct, String name) {
			StringBuilder sb = new StringBuilder();		
			if(localLocPrefix != null) {
				sb.append(localLocPrefix);
				sb.append('/');
			}
			sb.append(ct);
			if(name != null) {
				sb.append("[@name='");
				sb.append(name);
				sb.append("']");
			}
			return sb.toString();
		}
		
		protected static final Object typedArr(String[] arr, Class type) {
			if(boolean.class.equals(type)) {
				boolean[] result = new boolean[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Boolean.parseBoolean(arr[i]);
				return result;
			}
			if(char.class.equals(type)) {
				char[] result = new char[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = arr[i].charAt(0);
				return result;			
			}
	//		if("byte".equals(type)) {
	//				return "getByte";
	//		}
			if(short.class.equals(type)) {
				short[] result = new short[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Short.parseShort(arr[i]);
				return result;			
			}
			if(int.class.equals(type)) {
				int[] result = new int[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Integer.parseInt(arr[i]);
				return result;			
			}
			if(long.class.equals(type)) {
				long[] result = new long[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Long.parseLong(arr[i]);
				return result;	
			}
			if(float.class.equals(type)) {
				float[] result = new float[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Float.parseFloat(arr[i]);
				return result;
			}
			if(double.class.equals(type)) {
				double[] result = new double[arr.length];
				for(int i = 0; i < arr.length; i++)
					result[i] = Double.parseDouble(arr[i]);
				return result;
			}
			///////////////////////////////////
			if(Boolean.class.equals(type)) {
				Boolean[] result = new Boolean[arr.length];
				for(int i = 0; i < arr.length; i++)
					if(arr[i] != null)
						result[i] = Boolean.valueOf(arr[i]);
				return result;
			}
			if(Character.class.equals(type)) {
				Character[] result = new Character[arr.length];
				for(int i = 0; i < arr.length; i++)
					if(arr[i] != null)
						result[i] = Character.valueOf(arr[i].charAt(0));
				return result;			
			}
	//		if("byte".equals(type)
	//				|| "Byte".equals(type)) {
	//				return "getByte";
	//		}
			if(Short.class.equals(type)) {
				Short[] result = new Short[arr.length];
				for(int i = 0; i < arr.length; i++)
					if(arr[i] != null)
						result[i] = Short.valueOf(arr[i]);
				return result;			
			}
			if(Integer.class.equals(type)) {
				Integer[] result = new Integer[arr.length];
				for(int i = 0; i < arr.length; i++)
					if (arr[i] != null)
						result[i] = Integer.valueOf(arr[i]);
				return result;			
			}
			if(Long.class.equals(type)) {
				Long[] result = new Long[arr.length];
				for(int i = 0; i < arr.length; i++)
					if (arr[i] != null)
						result[i] = Long.valueOf(arr[i]);
				return result;	
			}
			if(Float.class.equals(type)) {
				Float[] result = new Float[arr.length];
				for(int i = 0; i < arr.length; i++)
					if (arr[i] != null)
						result[i] = Float.valueOf(arr[i]);
				return result;
			}
			if(Double.class.equals(type)) {
				Double[] result = new Double[arr.length];
				for(int i = 0; i < arr.length; i++)
					if (arr[i] != null)
						result[i] = Double.valueOf(arr[i]);
				return result;
			}		
			if(String.class.equals(type)) {
				return arr;
			}
			return null;
		}
		
		private static final HashMap<String, Object> createDP(final HashMap<String, Object> defValue) {
			if(defValue == null)
				return null;
			HashMap<String, Object> result = new HashMap<String, Object>();
			for(String key : defValue.keySet()) {
				//all properties are dynamic
				result.put(key, Boolean.TRUE);
			}
			return result;
		}
		
}