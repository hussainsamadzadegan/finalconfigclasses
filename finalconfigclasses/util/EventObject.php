<?php

namespace finalconfigclasses\util;

class EventObject {
	protected $source;
	
	public function __construct($source) {
		if(is_null($source)) {
			throw new \InvalidArgumentException('null source');
		}
		
		$this->source = $source;
	}
	
	public function getSource() {
		return $this->source;
	}
	
	public function __toString()
	{
		return get_class($this) . '[source=' . $this->source . ']';
	}
}