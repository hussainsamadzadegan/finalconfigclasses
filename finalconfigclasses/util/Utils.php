<?php

namespace finalconfigclasses\util;

abstract class Utils {
	
	private static function overflow32($v)
	{
		$v = 4294967296 % $v;
		if ($v > 2147483647) return $v - 4294967296;
		elseif ($v < -2147483648) return $v + 4294967296;
		else return $v;
	}
	
	public static function stringHashCode( $s )
	{
		$h = 0;
		$len = strlen($s);
		for($i = 0; $i < $len; $i++)
		{
			$h = Utils::overflow32(31 * $h + ord($s[$i]));
		}
	
		return $h;
	}
	
	public static function isArrayOfType($array, $reflectionclass) {
		if(!is_array($array))
			return false;
		$result = true;
		for($i = 0; $i < count($array); $i++)
			if(is_object($array[$i]))
				$result = $result && $reflectionclass->isInstance($array[$i]);
			else
				$result = false;
		return $result;
	}
	
}