<?php
namespace topshelfcraft\walk\queue\jobs;

use Craft;
use craft\queue\BaseJob;
use topshelfcraft\walk\helpers\WalkHelper;


/**
 * CallOnElementJob
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016+ Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     3.0
 */
class CallOnElementJob extends BaseJob
{


    /*
     * Public properties
     */


    /**
     * @var int
     */
    public $elementId = 0;

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
			$element = Craft::$app->getElements()->getElementById($this->elementId);
			if ($element)
			{
				$elements = [$element];
				WalkHelper::craftyArrayWalk($elements, $this->callable);
			}
		}
		catch(\Exception $e)
		{
			Craft::error("Error during CallOnElement job: " . $e->getMessage());
			return $e->getMessage();
		}

		return true;
	}


	/**
	 * @return null|string
	 */
	protected function defaultDescription()
	{
		return "Calling [{$this->callable}] on element: {$this->elementId}";
	}


}
