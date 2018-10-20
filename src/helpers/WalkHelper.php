<?php
namespace topshelfcraft\walk\helpers;

use Craft;
use craft\base\Element;
use craft\tasks\MissingTask;
use topshelfcraft\walk\tasks\CallOnElementTask;
use topshelfcraft\walk\tasks\CallOnValueTask;
use craft\helpers\App;

/**
 * WalkHelper
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016+ Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     3.0
 */
class WalkHelper
{


	/*
	 * Regex patterns for matching callables
	 */

	const ServiceCallablePattern = '/^([a-z]\w*)(\.([a-z]\w*)){1,2}$/';

	const Craft2_ServiceCallablePattern = '/^([a-z]\w*)\.([a-z]\w*)$/';
	const Craft2_HelperCallablePattern = '/^[A-Z]\w*Helper\w*::([a-z]\w*)$/';
	const Craft2_TaskCallablePattern = '/^[A-Z]\w*Task$/';


	/*
	 * Public methods
	 */


	/**
	 * @param array $array
	 * @param string $callable
	 * @param null $userdata
	 *
	 * @return bool
	 */
	public static function craftyArrayWalk(&$array, $callable, $userdata = null)
	{

		if (is_string($callable) && preg_match(static::ServiceCallablePattern, $callable))
		{
			$callable = static::getComponentCallable($callable);
		}

		if (is_callable($callable))
		{
			if ($userdata)
			{
				return array_walk($array, $callable, $userdata);
			}
			else
			{
				return array_walk($array, $callable);
			}
		}
		else
		{
			return false;
		}

	}


	/**
	 * @param string $type
	 * @param array $elements
	 * @param array $settings
	 * @param string $valParam
	 *
	 * @return bool
	 */
	public static function spawnTasks($type, $elements, $settings = [], $valParam = 'value')
	{

		if (!is_array($elements)) $elements = [$elements];
		
		// This could take a while. We'd prefer not to get hung up in the middle...
		App::maxPowerCaptain();

		foreach($elements as $el)
		{

			if ($el instanceof Element)
			{
				$val = $el->id;
			}
			else
			{
				$val = $el;
			}

			if ($val)
			{
				$settings = is_array($settings) ? $settings : [];
				$settings[$valParam] = $val;
				$task = Craft::$app->getTasks()->createTask(['type' => $type, 'settings' => $settings]);
				if ($task instanceof MissingTask)
					return false;
			}

		}

		return true;

	}


	/**
	 * @param array $elements
	 * @param string $callable
	 *
	 * @return bool
	 */
	public static function spawnCallOnElementTasks($elements, $callable)
	{
		return static::spawnTasks(CallOnElementTask::class, $elements, ['callable' => $callable], 'elementId');
	}


	/**
	 * @param array $elements
	 * @param string $callable
	 *
	 * @return bool
	 */
	public static function spawnCallOnIdTasks($elements, $callable)
	{
		return static::spawnTasks(CallOnValueTask::class, $elements, ['callable' => $callable], 'value');
	}


	/**
	 * @param string $str
	 *
	 * @return array
	 *
	 */
	public static function getComponentCallable($str)
	{

		$parts = explode('.', $str, 3);

		if (count($parts) === 2)
		{
			// A Craft service method, e.g. `users.activateUser`
			$component = Craft::$app->get($parts[0]);
			$method = $parts[1];
		}
		elseif (count($parts) === 3)
		{
			// A module service method, e.g. `walk.walk.dummy`
			$component = Craft::$app->getModule($parts[0])->get($parts[1]);
			$method = $parts[2];
		}
		else
		{
			return null;
		}

		return method_exists($component, $method) ? [$component, $method] : null;

	}


	/**
	 * @param string $str
	 *
	 * @return bool
	 *
	 */
	public static function isComponentCallable($str)
	{
		$callable = static::getComponentCallable($str);
		return is_callable($callable);
	}


}
