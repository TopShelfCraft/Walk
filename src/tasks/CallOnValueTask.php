<?php
namespace topshelfcraft\walk\tasks;

use Craft;
use craft\base\Task;
use topshelfcraft\walk\helpers\WalkHelper;


/**
 * CallOnValue
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016+ Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     3.0
 */
class CallOnValueTask extends Task
{


	/*
	 * Public properties
	 */


	/**
	 * @var int
	 */
	public $value;

	/**
	 * @var string
	 */
	public $callable = '';


	/*
	 * Public methods
	 */


	/**
	 * @inheritdoc
	 */
	public function getDescription(): string
	{
		return "Calling [{$this->callable}] on value: {$this->value}";
	}


	/**
	 * @inheritdoc
	 */
	public function getTotalSteps(): int
	{
		return 1;
	}


	/**
	 * @inheritdoc
	 * @see http://www.yiiframework.com/doc-2.0/guide-input-validation.html
	 */
	public function rules()
	{
		return [
			['callable', 'string'],
		];
	}


	/**
	 * @inheritdoc
	 */
	public function runStep(int $step)
	{

		try
		{
			if (!empty($this->value))
			{
				$values = [$this->value];
				WalkHelper::craftyArrayWalk($values, $this->callable);
			}
		}
		catch(\Exception $e)
		{
			Craft::error("Error during CallOnValue task: " . $e->getMessage());
			return $e->getMessage();
		}

		return true;

	}


}
