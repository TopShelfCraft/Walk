<?php
namespace TopShelfCraft\Walk\queue;

use craft\queue\BaseJob;
use TopShelfCraft\Walk\helpers\WalkHelper;

class CallOnValueJob extends BaseJob
{

	/**
	 * @var mixed
	 */
	public $value;

	public string $callable = '';

	/**
	 * @param \craft\queue\QueueInterface $queue
	 */
	public function execute($queue): void
	{
		if (!empty($this->value))
		{
			$values = [$this->value];
			WalkHelper::craftyArrayWalk($values, $this->callable);
		}
	}

	protected function defaultDescription(): string
	{
		return "Calling [{$this->callable}] on value: {$this->value}";
	}

}
