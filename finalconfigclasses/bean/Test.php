<?php

namespace finalconfigclasses\bean;

use finalconfigclasses\bean\BeanDiff;
use finalconfigclasses\bean\misc\PropertyChangeSupport;
use finalconfigclasses\bean\misc\BeanUpdateSupport;
use finalconfigclasses\cfg\ConfigBeanDiff;
use finalconfigclasses\util\Utils;
use finalconfigclasses\cfg\ConfigBean;
use finalconfigclasses\bean\misc\PropertyChangeListener;
use finalconfigclasses\cfg\misc\NodeChangeListener;
use finalconfigclasses\cfg\ConfigBeanVisitor;
use Mysidia\Resource\Utility\Hash;
use Mysidia\Resource\Collection\HashMap;
use Mysidia\Resource\Native\StringWrapper;
use finalconfigclasses\cfg\gen\CacheConfigImpl;

echo "hi\n";
//require __DIR__ . '/../../vendor/autoload.php';
spl_autoload_register(function($className)
{

	$namespace=str_replace("\\","/",__NAMESPACE__);
	$className=str_replace("\\","/",$className);
	$className = "../../" . $className;
	echo $className;
	$class="{$className}.php";
	//if($class == 'finalconfigclasses/util/Threaded.php')
	//	return ;
	include_once($class);
	
});

$hashmap = new HashMap();
$hashmap->put(new StringWrapper("a"), new StringWrapper("b"));
$hashmap->put(new StringWrapper("c"), new StringWrapper("d"));

$itr = $hashmap->valueIterator();
while($itr->hasNext()) {
	$ent = $itr->nextEntry();
	echo $ent->getKey()->value() . " -> " . $ent->getValue()->value() . "\n";
}

$var1 = "source1";
$var2 = "proposed1";
$beandiff = new BeanDiff($var1, $var2);
$beandiff->recordAddition("prop1", "val1");
echo $beandiff;

class X { public function __toString() { return 'hi there!';}}
$x = new X();
$pcs = new PropertyChangeSupport($x);
echo $x;

$bus = new BeanUpdateSupport($x);
echo $x;
echo "\n";

class Y implements ConfigBean {
	

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::_getParent()
	 */
	public function _getParent() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::_getXPath()
	 */
	public function _getXPath() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::_getLockID()
	 */
	public function _getLockID() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::_getPropertiesLock()
	 */
	public function _getPropertiesLock() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::addPropertyChangeListener()
	 */
	public function addPropertyChangeListener(PropertyChangeListener $listener) {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::removePropertyChangeListener()
	 */
	public function removePropertyChangeListener(PropertyChangeListener $listener) {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::getPropertyChangeListeners()
	 */
	public function getPropertyChangeListeners() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::addNodeChangeListener()
	 */
	public function addNodeChangeListener(NodeChangeListener $listener) {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::removeNodeChangeListener()
	 */
	public function removeNodeChangeListener(NodeChangeListener $listener) {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::getNodeChangeListeners()
	 */
	public function getNodeChangeListeners() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::addBeanUpdateListener()
	 */
	public function addBeanUpdateListener(BeanUpdateListener $listener) {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::removeBeanUpdateListener()
	 */
	public function removeBeanUpdateListener(BeanUpdateListener $listener) {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::getBeanUpdateListeners()
	 */
	public function getBeanUpdateListeners() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::_conditionalUnset()
	 */
	public function _conditionalUnset($isUnsetUpdate, $propertyName) {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::cloneThis()
	 */
	public function cloneThis() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::cloneThis2()
	 */
	public function cloneThis2(ConfigBean $parentOfCloned) {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::cloneSubtree()
	 */
	public function cloneSubtree() {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::cloneSubtree2()
	 */
	public function cloneSubtree2(ConfigBean $parentOfCloned) {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::cloneSubtree3()
	 */
	public function cloneSubtree3($cloneDepth) {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::cloneSubtree4()
	 */
	public function cloneSubtree4(ConfigBean $parentOfCloned, $cloneDepth) {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::accept()
	 */
	public function accept(ConfigBeanVisitor $visitor) {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::load()
	 */
	public function load() {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::save()
	 */
	public function save() {
		// TODO: Auto-generated method stub

	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::getDefValue()
	 */
	public function getDefValue($propertyName) {
		// TODO: Auto-generated method stub
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \finalconfigclasses\cfg\ConfigBean::isDynamic()
	 */
	public function isDynamic($propertyName) {
		// TODO: Auto-generated method stub
		return null;
	}
	
	/**
	 * Specifies that SubDiff needed for property.
	 * Specifies that sub-element inside xml is reached.
	 *
	 * Specifies current property is an independent bean for computing beanDiff and
	 * its changes should be recorded to new BeanDiff object, not in parent BeanDiff.
	 */
	public function needsSubdiff($propertyName) {return false;}
	
	/**
	 * Returns the object which should be used to compute
	 * the beandiff for this bean.
	 */
	public function _newDiffHelper() {return null;}
	
	/**
	 * The beanID should be unique for each bean instance(except of cloned one
	 * which has a same beanID). If you reuse the same beanID for different
	 * objects then the beanDiff algorithm can not distinct between them. So
	 * do not change the default implementation and use what is provided in
	 * API.
	 */
	public function _getBeanID() {return null;}
	
	/**
	 * The interface or class which this method returns
	 * would be used to find properties for beandiff algorithm.
	 */
	public function _getBeanClass() {return null;}
	
	public function isSet($propertyName) {return false;}
	public function unSet($propertyName) {}
	
	public function __toString() {
		return "instance of y( " . get_class($this) . " )";
	}

}

class Z extends Y {}
$z = new Z();
echo "\n";
$y = new Y();
$cbeandiff = new ConfigBeanDiff($z, $z);
$cbeandiff->recordAddition("prop2", "val2", true);
echo $cbeandiff;


echo "\nresult=" . (Utils::isArrayOfType(array($y), new \ReflectionClass('finalconfigclasses\cfg\ConfigBean')));

$defValue = new HashMap();
//the default value for attributes
$defValue->put(new \Mysidia\Resource\Native\StringWrapper("cacheSize"), new \Mysidia\Resource\Native\Integer(100));
$defValue->put(new \Mysidia\Resource\Native\StringWrapper("cachePolicy"), new \Mysidia\Resource\Native\StringWrapper("LFU"));

$dynaProp = new HashMap();
//all attributes are dyanamic for this test
$dynaProp->put(new \Mysidia\Resource\Native\StringWrapper("cacheSize"), new \Mysidia\Resource\Native\Boolean(true));
$dynaProp->put(new \Mysidia\Resource\Native\StringWrapper("cachePolicy"), new \Mysidia\Resource\Native\Boolean(false));

$map = new HashMap();
$ccimpl = new CacheConfigImpl("beanid", $defValue, $dynaProp, /*$parent*/null, /*$propertiesFile*/null, /*$lockID*/"lockID", /*$document*/null, /*$name*/null, /*$keyPrefix*/null);