<?php
namespace Craft;


/**
 * WalkHelper
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016, Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     1.0
 */
class WalkHelper
{


	/*
	 * Regex patterns for matching callables
	 */
	const ServiceCallablePattern = '/^([a-z]\w*)\.([a-z]\w*)$/';
	const HelperCallablePattern = '/^[A-Z]\w*Helper\w*::([a-z]\w*)$/';
	const TaskCallablePattern = '/^[A-Z]\w*Task$/';


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

		if (is_string($callable))
		{

			if (preg_match(static::ServiceCallablePattern, $callable))
			{
				$callable = static::getServiceCallable($callable);
			}
			elseif (preg_match(static::HelperCallablePattern, $callable))
			{
				if (!is_callable($callable))
				{
					$callable = '\Craft\\' . $callable;
				}
			}
			elseif (preg_match(static::TaskCallablePattern, $callable))
			{
				$task = substr($callable, 0, -4);
				return static::spawnTasks($task, $array, $userdata);
			}

		}

		if (is_callable($callable))
		{
			return array_walk($array, $callable, $userdata);
		}
		else
		{
			return false;
		}

	}


	/**
	 * @param string $task
	 * @param array $elements
	 * @param array $settings
	 * @param string $idParam
	 *
	 * @return bool
	 */
	public static function spawnTasks($task, $elements, $settings = [], $idParam = 'id')
	{

		if (!is_array($elements)) $elements = [$elements];

		// This could take a while. We'd prefer not to get hung up in the middle...
		craft()->config->maxPowerCaptain();

		foreach($elements as $el)
		{

			if ($el instanceof BaseElementModel)
			{
				$id = $el->id;
			}
			elseif (is_numeric($el))
			{
				$id = intval($el);
			}
			else
			{
				$id = $el;
			}

			if ($id)
			{
				$settings = is_array($settings) ? $settings : [];
				$settings[$idParam] = $id;
				// WalkPlugin::log("Creating {$task} task with ID {$id}.");
				craft()->tasks->createTask($task, null, $settings);
			}

		}

		return true;
		// TODO: Only report success if the tasks actually get created (i.e. are valid)

	}


	/**
	 * @param array $elements
	 * @param string $callable
	 *
	 * @return bool
	 */
	public static function spawnCallOnElementTasks($elements, $callable)
	{
		return static::spawnTasks('Walk_CallOnElement', $elements, ['callable' => $callable]);
	}


	/**
	 * @param array $elements
	 * @param string $callable
	 *
	 * @return bool
	 */
	public static function spawnCallOnIdTasks($elements, $callable)
	{
		return static::spawnTasks('Walk_CallOnId', $elements, ['callable' => $callable]);
	}


	/**
	 * @param string $str
	 *
	 * @return array
	 */
	public static function getServiceCallable($str)
	{
		$serviceAndMethod = explode('.', $str, 2);
		$service = $serviceAndMethod[0];
		$method = $serviceAndMethod[1];
		return [craft()->$service, $method];
	}


	/**
	 * @param mixed $var
	 */
	public static function dummy($var)
	{
		WalkPlugin::log("Dummy helper method! " . print_r($var, true));
	}


}
