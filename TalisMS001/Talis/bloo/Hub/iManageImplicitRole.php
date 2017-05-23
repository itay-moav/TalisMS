<?php
interface BL_Hub_iManageImplicitRole{
	/**
	 * Gets need user information for adding a role
	 * @param array $params
	 * @return BL_Hub_Abstract
	 */
	function get_user_information_for_add_role(array $params);
	
	/**
	 *  Get user information for the user being removed from the role
	 *  @param array where for the delete or update
	 *  @return BL_Hub_Abstract
	 */

	function get_user_information_for_remove_role(array $params);

	/**
	 *  Calls add role on gathered elements
	 *  @return BL_Hub_Abstract
	 */
	function add_role();
	
	/**
	 *  Calls remove role on gathered elements
	 *  @return BL_Hub_Abstract
	 */
	function remove_role();
}