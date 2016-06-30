<?php

namespace finalconfigclasses\bean;

use Exception;

class BeanUpdateRejectedException extends Exception {
	
	public function __construct($message, $code = 0, Exception $previous = null) {
		// some code
	
		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);
	}
}