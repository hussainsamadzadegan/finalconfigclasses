<?php

namespace finalconfigclasses\cfg;

use finalconfigclasses\bean\BeanUpdateEvent;

abstract class ConfigBeanUpdateEvent extends BeanUpdateEvent
{
	public function __construct(ConfigBean $sourceBean,
			ConfigBean $proposedBean)
			{
		parent::__construct($sourceBean, $proposedBean/*, updateID*/);

	}

	public function getSourceBean()
	{
		return parent::getSourceBean();
	}

	public function getProposedBean()
	{
		return parent::getProposedBean();
	}
}
