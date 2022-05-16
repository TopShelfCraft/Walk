<?php
namespace TopShelfCraft\Walk\queue;

use Craft;
use craft\queue\BaseJob;
use TopShelfCraft\Walk\helpers\WalkHelper;

class CallOnElementJob extends BaseJob
{

    public int $elementId = 0;

    public string $callable = '';

	/**
	 * @param \craft\queue\QueueInterface $queue
	 */
	public function execute($queue): void
	{
		$element = Craft::$app->getElements()->getElementById($this->elementId);
		if ($element)
		{
			$elements = [$element];
			WalkHelper::craftyArrayWalk($elements, $this->callable);
		}
	}

	protected function defaultDescription(): string
	{
		return "Calling [{$this->callable}] on element: {$this->elementId}";
	}

}
