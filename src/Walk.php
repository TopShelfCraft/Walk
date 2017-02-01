<?php
namespace topshelfcraft\walk;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use topshelfcraft\walk\console\controllers\WalkController;


/**
 * Walk Plugin
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016+ Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     3.0
 */
class Walk extends Plugin
{


	/**
	 * @var Walk $plugin
	 */
	public static $plugin;


	/**
	 * @inheritdoc
	 */
	public function init()
	{

		parent::init();
		self::$plugin = $this;

		if (Craft::$app instanceof ConsoleApplication)
		{
			Craft::$app->controllerMap['walk'] = WalkController::class;
		}

	}


	/**
	 * @param string $message
	 * @param string $category
	 */
	public static function notice($message = '', $category = 'Walk')
	{
		Craft::info("\n".print_r($message, true), $category);
		if (Craft::$app instanceof ConsoleApplication)
		{
			echo "\n" . print_r($message, true);
		}
	}


}
