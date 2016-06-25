<?php

namespace Mysidia\Resource\Native;

interface Objective {
	public function equals(Objective $o);
	public function hashCode();
}