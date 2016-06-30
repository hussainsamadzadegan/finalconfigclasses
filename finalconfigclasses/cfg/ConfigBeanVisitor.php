<?php

namespace finalconfigclasses\cfg;

interface ConfigBeanVisitor
{
	/**
	 * Visits the specified bean. This method is called before eventually
	 * existing children(relations) of this bean are processed.
	 *
	 * @param bean the bean to be visited
	 */
	public function visitBeforeChildren(ConfigBean $bean);

	/**
	 * Visits the specified bean. This method is called after eventually
	 * existing children(relations) of this bean have been processed.
	 *
	 * @param bean the bean to be visited
	 */
	public function visitAfterChildren(ConfigBean $bean);

	/**
	 * Returns a flag whether the actual visit process should be aborted. This
	 * method allows a visitor implementation to state that it does not need any
	 * further data. It may be used e.g. by visitors that search for a certain
	 * bean in the hierarchy. After that bean was found, there is no need to
	 * process the remaining beans, too.
	 *
	 * @return a flag if the visit process should be stopped
	 */
	public function terminate();
}
