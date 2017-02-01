<?php
namespace topshelfcraft\walk\services;

use craft\base\Component;
use topshelfcraft\walk\Walk;


/**
 * DummyService
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016+ Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     3.0
 */
class DummyService extends Component
{


	public function bloop($value)
	{
		Walk::notice("Bloop: " . print_r($value, true));
	}


}
