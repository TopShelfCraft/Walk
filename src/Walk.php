<?php
namespace TopShelfCraft\Walk;

use Craft;
use craft\console\Application as ConsoleApplication;
use TopShelfCraft\base\Plugin;
use TopShelfCraft\Walk\controllers\console\WalkController;

/**
 * @author Michael Rog <michael@michaelrog.com>
 * @link https://topshelfcraft.com
 * @copyright Copyright 2022, Top Shelf Craft (Michael Rog)
 */
class Walk extends Plugin
{

	public ?string $changelogUrl = "https://raw.githubusercontent.com/TopShelfCraft/Walk/4.x/CHANGELOG.md";
	public bool $hasCpSection = false;
	public bool $hasCpSettings = false;
	public string $schemaVersion = "0.0.0.0";

	public function init()
	{

		parent::init();

		if (Craft::$app instanceof ConsoleApplication)
		{
			// Index our controller actions under `walk` rather than the default `walk\walk` -- cuz, prettier.
			$this->controllerNamespace = 'TopShelfCraft\Walk\null';
			Craft::$app->controllerMap['walk'] = WalkController::class;
		}

	}

}
