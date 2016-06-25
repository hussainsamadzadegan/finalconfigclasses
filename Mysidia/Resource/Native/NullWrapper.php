<?php

namespace Mysidia\Resource\Native;

/**
 * A null type wrapper
 *
 * @author Ordland
 */
final class NullWrapper extends Object
{
    public function __construct()
    {
        $this->value = null;
    }
}
