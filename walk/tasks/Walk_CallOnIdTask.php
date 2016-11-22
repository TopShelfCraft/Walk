<?php
namespace Craft;


/**
 * Walk_CallOnIdTask
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016, Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     1.0
 */
class Walk_CallOnIdTask extends BaseTask
{


	/**
	 * Defines the settings.
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'id' => array(AttributeType::Number, 'default' => 0),
			'callable' => array(AttributeType::String),
		);
	}

	/**
	 * Returns the default description for this task.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return "Calling " . $this->getSettings()->callable . " on ID " . $this->getSettings()->id;
	}

	/**
	 * Gets the total number of steps for this task.
	 *
	 * @return int
	 */
	public function getTotalSteps()
	{
		return 1;
	}

	/**
	 * Runs a task step.
	 *
	 * @param int $step
	 * @return bool
	 */
	public function runStep($step)
	{

		WalkPlugin::log($this->getDescription(), LogLevel::Info, true);

		try
		{
			$id = [$this->getSettings()->id];
			WalkHelper::craftyArrayWalk($id, $this->getSettings()->callable);
		}
		catch(Exception $e)
		{
			WalkPlugin::log("Error during CallOnId task: " . $e->getMessage(), LogLevel::Error);
			return false;
		}

		return true;

	}


}
