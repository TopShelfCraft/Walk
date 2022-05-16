<?php
namespace TopShelfCraft\Walk\helpers;

use Craft;
use craft\base\Element;
use craft\helpers\App;
use TopShelfCraft\Walk\queue\CallOnElementJob;
use TopShelfCraft\Walk\queue\CallOnValueJob;
use yii\base\InvalidConfigException;

class WalkHelper
{

	const ServiceCallablePattern = '/^([a-z]\w*)(\.([a-z]\w*)){1,2}$/';

	/**
	 * @todo Ditch boolean return and refactor to Exceptions in 5.0.
	 */
	public static function craftyArrayWalk(array &$array, string $callable, $userdata = null): bool
	{

		if (preg_match(static::ServiceCallablePattern, $callable))
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
	 * @todo Ditch boolean return and refactor to Exceptions in 5.0.
	 */
	public static function spawnJobs(string $type, array $elements, array $settings = [], string $valParam = 'value'): bool
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
				
				$job = Craft::$app->queue->push(new $type($settings));
				if (empty($job)) {
					return false;
				}
			}

		}

		return true;

	}

	/**
	 * @todo Ditch boolean return and refactor to Exceptions in 5.0.
	 */
	public static function spawnCallOnElementJobs(array $elements, string $callable): bool
	{
		return static::spawnJobs(CallOnElementJob::class, $elements, ['callable' => $callable], 'elementId');
	}

	/**
	 * @todo Ditch boolean return and refactor to Exceptions in 5.0.
	 */
	public static function spawnCallOnIdJobs(array $elements, string $callable): bool
	{
		return static::spawnJobs(CallOnValueJob::class, $elements, ['callable' => $callable], 'value');
	}

	public static function getComponentCallable(string $str): ?callable
	{

		$parts = explode('.', $str, 3);

		try
		{

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

		}
		catch(InvalidConfigException $e)
		{
			Craft::error($e->getMessage(), 'walk');
			return null;
		}

		return method_exists($component, $method) ? [$component, $method] : null;

	}

	public static function isComponentCallable(string $str): bool
	{
		$callable = static::getComponentCallable($str);
		return is_callable($callable);
	}

}
