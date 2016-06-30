<?php

namespace finalconfigclasses\bean;

use finalconfigclasses\bean\BeanDiff;

//require __DIR__ . '/../../vendor/autoload.php';
spl_autoload_register(function($className)
{

	$namespace=str_replace("\\","/",__NAMESPACE__);
	$className=str_replace("\\","/",$className);
	$class="{$className}.php";
	//if($class == 'finalconfigclasses/util/Threaded.php')
	//	return ;
	include_once($class);
	
});

$var1 = "source1";
$var2 = "proposed1";
$beandiff = new BeanDiff($var1, $var2);
$beandiff->recordAddition("prop1", "val1");
echo $beandiff;

