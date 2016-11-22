<?php
namespace Craft;


/**
 * Walk_CallOnIdTask
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016, Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     1.0
 */
class WalkService extends BaseApplicationComponent
{


	/**
	 * @param mixed $var
	 */
	public function dummy($var)
	{
		WalkPlugin::log("Dummy service method! " . print_r($var, true));
	}


}


