<?php
namespace topshelfcraft\walk\queue\jobs;

use Craft;
use craft\queue\BaseJob;
use topshelfcraft\walk\helpers\WalkHelper;


/**
 * CallOnValueJob
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016+ Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     3.0
 */
class CallOnValueJob extends BaseJob
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
	 * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
	 * @return bool|string
	 */
	public function execute($queue)
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
			Craft::error("Error during CallOnValue job: " . $e->getMessage());
			return $e->getMessage();
		}
		
		return true;
	}
	
	
	/**
	 * @return null|string
	 */
	protected function defaultDescription()
	{
		return "Calling [{$this->callable}] on value: {$this->value}";
	}


}
