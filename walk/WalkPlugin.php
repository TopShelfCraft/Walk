<?php
namespace Craft;


/**
 * WalkPlugin
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016, Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     1.0
 */
class WalkPlugin extends BasePlugin
{


	/**
	 * @return string
	 */
	public function getName()
	{
		return "Walk";
	}


	/**
	 * Return the plugin description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return "Applies a Service method, helper, or Task to each Element of a specified set.";
	}


	/**
	 * Return the plugin developer's name
	 *
	 * @return string
	 */
	public function getDeveloper()
	{
		return "Michael Rog";
	}


	/**
	 * Return the plugin developer's URL
	 *
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'https://topshelfcraft.com';
	}


	/**
	 * Return the plugin's documentation URL
	 *
	 * @return string
	 */
	public function getDocumentationUrl()
	{
		return null;
	}


	/**
	 * Return the plugin's current version
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return '0.2.0';
	}


	/**
	 * Return the plugin's db schema version
	 *
	 * @return string|null
	 */
	public function getSchemaVersion()
	{
		return '0.0.0.0';
	}


	/**
	 * Return the plugin's release feed URL
	 *
	 * @return string
	 */
	public function getReleaseFeedUrl()
	{
		return 'https://github.com/TopShelfCraft/Walk/raw/master/releases.json';
	}


	/**
	 * Return whether the plugin has a CP section
	 *
	 * @return bool
	 */
	public function hasCpSection()
	{
		return false;
	}


	/**
	 * @param string $msg
	 * @param string $level
	 * @param bool $force
	 *
	 * @return mixed
	 */
	public static function log($msg = '', $level = LogLevel::Info, $force = false)
	{
		$msg = "\n" . print_r($msg, true) . "\n";
		Craft::log($msg , $level, $force, 'plugin', 'walk');
	}


}
