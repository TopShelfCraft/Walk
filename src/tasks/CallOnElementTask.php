<?php
namespace topshelfcraft\walk\tasks;

use Craft;
use craft\base\Task;
use topshelfcraft\walk\helpers\WalkHelper;


/**
 * CallOnElementTask
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016+ Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     3.0
 */
class CallOnElementTask extends Task
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
	 * @inheritdoc
	 */
	public function getDescription(): string
	{
		return "Calling [{$this->callable}] on element {$this->elementId}";
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
            ['elementId', 'int'],
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
		    $element = Craft::$app->getElements()->getElementById($this->elementId);
		    if ($element)
		    {
			    $elements = [$element];
			    WalkHelper::craftyArrayWalk($elements, $this->callable);
		    }
	    }
	    catch(\Exception $e)
	    {
		    Craft::error("Error during CallOnElement task: " . $e->getMessage());
		    return $e->getMessage();
	    }

	    return true;

    }


}
