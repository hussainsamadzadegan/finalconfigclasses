<?php

namespace finalconfigclasses\cfg;b

use finalconfigclasses\bean\DiffableBean;
use finalconfigclasses\bean\SettableBean;
use finalconfigclasses\bean\misc\PropertyChangeListener;
use finalconfigclasses\bean\BeanUpdateListener;
use finalconfigclasses\cfg\misc\NodeChangeListener;

interface ConfigBean extends DiffableBean, SettableBean/*, ICloneable, ISerializable*/
{
	public function _getParent();

	public function _getXPath();

	/* Useless function in php version of config library. there is no readwritelock.*/
	public function _getLockID();
	 
	//	ConfigDiffHelper _newDiffHelper();

	/* Useless function in php version of config library. just return 'this'.*/
	public function _getPropertiesLock();

	//void _setPropertiesLock(ReaderWriterLock propertiesLock);

	/**
	 * Listeners which are just interested in 'attr changes'.
	 */
	public function addPropertyChangeListener(PropertyChangeListener $listener);
	public function removePropertyChangeListener(PropertyChangeListener $listener);
	public function getPropertyChangeListeners();

	/**
	 * Listeners which are just interested in 'prop change'(i.e.
	 * 'relation changes').
	 */
	public function addNodeChangeListener(NodeChangeListener $listener);
	public function removeNodeChangeListener(NodeChangeListener $listener);
	public function getNodeChangeListeners();

	/**
	 * Listeners which are interested in 'batch validation' of
	 * changes.
	 */
	public function addBeanUpdateListener(BeanUpdateListener $listener);
	public function removeBeanUpdateListener(BeanUpdateListener $listener);
	public function getBeanUpdateListeners();
	 
	public function _conditionalUnset($isUnsetUpdate, $propertyName);
	 
	/**
	 * The name of this function is clone() but in php 'clone' is keyword so it is renamed to cloneThis().
	 * 
	 * shallow clone.
	 */
	public function cloneThis();

	/**
	 * The name of this function is clone() but in php 'clone' is keyword so it is renamed to cloneThis().
	 * 
	 * shallow clone.
	 */
	public function cloneThis2(ConfigBean $parentOfCloned);
	 
	/**
	 * deep clone(all descendants would be cloned).
	 */
	public function cloneSubtree();

	/**
	 * deep clone(all descendants would be cloned).
	 */
	public function cloneSubtree2(ConfigBean $parentOfCloned);
	 
	/**
	 * This method is useful for UI.
	 * if you set the cloneDepth to zero then you
	 * would get the result of clone() method.
	 */
	public function cloneSubtree3($cloneDepth);

	/**
	 * This method is useful for UI.
	 * if you set the cloneDepth to zero then you
	 * would get the result of clone() method.
	 */
	public function cloneSubtree4(ConfigBean $parentOfCloned, $cloneDepth);

	/**
	 * Visits this config bean and all its sub beans(related beans). This method provides a simple
	 * means for going through a hierarchical structure of configuration beans.
	 *
	 * @see ConfigBeanVisitor
	 * @param visitor the visitor
	 */
	public function accept(ConfigBeanVisitor $visitor);

	/**
	 * Listeners which are just interested in 'property change'.
	 *
	 * Think about following methods. If following methods present
	 * then they provide flexible way to set and get Attrs/Props. They
	 * provide simple way for <code>ConfigDiffHelper</code> to set/get
	 * Attrs/Props without using <code>PropertyDescriptors</code> and reflection.
	 * Alos by providing these methods you can register ConfigBeans as
	 * DynamicBeans.
	 *
	 * Think which of these method must be inside ConfigMBean interface
	 * for remote access.
	 */
	//	Object getAttr(String name) throws IllegalArgumentException;
	//	void setAttr(String name, Object value) throws IllegalArgumentException;
	//	Object getProp(String name) throws IllegalArgumentException;
	//	void setProp(String name, Object value) throws IllegalArgumentException;

	/**
	 * Loads the config bean from its backing store(e.g. from properties file).
	 */
	public function load();

	/**
	 * Saves the config bean into backing store.
	 */
	public function save();

	/**
	 * Returns the default value of property.
	 * This method lets the UI to provide the 'Restore to defaults' functionality.
	 *
	 * UI would clones the config bean and then writes the
	 * default values into cloned version and then writes back
	 * the cloned version on config bean.
	 */
	public function getDefValue($propertyName);

	/**
	 * Specifies whether the property change is appliable at runtime(the
	 * property is dynamic) or needs the system restart(static property).
	 */
	public function isDynamic($propertyName);
}
