<?php

namespace finalconfigclasses\bean;

interface SettableBean {
	public function isSet($propertyName);
	public function unSet($propertyName);
}