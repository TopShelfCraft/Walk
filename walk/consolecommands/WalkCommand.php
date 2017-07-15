<?php
namespace Craft;


/**
 * WalkCommand walks, like the walking man.
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016, Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     1.0
 */
class WalkCommand extends BaseCommand
{


	/*
	 * Public properties (global command options)
	 */

	// Element criteria
	public $id, $limit=7, $offset;
	public $title, $slug, $relatedTo;
	public $source, $sourceId, $kind, $filename, $folderId, $size;
	public $group, $groupId;
	public $authorGroup, $authorGroupId, $authorId, $locale, $section, $status;
	public $completed, $isPaid, $isUnPaid, $orderStatusId;

	// Runner options
	public $asTask = false;


	/*
	 * Private properties
	 */


	private $_criteriaOptions = [
		'id', 'limit', 'offset', 'title', 'slug', 'relatedTo',
		'source', 'sourceId', 'kind', 'filename', 'folderId', 'size',
		'group', 'groupId',
		'authorGroup', 'authorGroupId', 'authorId', 'locale', 'section', 'status',
		'completed', 'isPaid', 'isUnPaid', 'orderStatusId',
	];

	private $_elementTypes = [
		'assets' => ElementType::Asset,
		'categories' => ElementType::Category,
		'entries' => ElementType::Entry,
		'globals' => ElementType::GlobalSet,
		'tags' => ElementType::Tag,
		'users' => ElementType::User,
	];


	/*
	 * Command actions
	 */


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionAssets($args)
	{
		return $this->actionWalkElements($args, ElementType::Asset);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionCategories($args)
	{
		return $this->actionWalkElements($args, ElementType::Category);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionEntries($args)
	{
		return $this->actionWalkElements($args, ElementType::Entry);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionGlobals($args)
	{
		return $this->actionWalkElements($args, ElementType::GlobalSet);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionTags($args)
	{
		return $this->actionWalkElements($args, ElementType::Tag);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionUsers($args)
	{
		return $this->actionWalkElements($args, ElementType::User);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionOrders($args)
	{
		return $this->actionWalkElements($args, 'Commerce_Order');
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionAssetIds($args)
	{
		return $this->actionWalkElementIds($args, ElementType::Asset);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionCategoryIds($args)
	{
		return $this->actionWalkElementIds($args, ElementType::Category);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionEntryIds($args)
	{
		return $this->actionWalkElementIds($args, ElementType::Entry);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionGlobalIds($args)
	{
		return $this->actionWalkElementIds($args, ElementType::GlobalSet);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionTagIds($args)
	{
		return $this->actionWalkElementIds($args, ElementType::Tag);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionUserIds($args)
	{
		return $this->actionWalkElementIds($args, ElementType::User);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionOrderIds($args)
	{
		return $this->actionWalkElementIds($args, 'Commerce_Order');
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionWalkElements($args, $type)
	{

		$callable = $args[0] ?? null;
		if (empty($callable))
		{
			$this->_l("Please specify the method.");
			return 1;
		}

		if ($this->asTask)
		{

			$elements = $this->_getCriteria($type)->ids();
			WalkPlugin::log("Applying [{$callable}] to " . count($elements) . " elements via tasks.", LogLevel::Profile, true);
			if (WalkHelper::spawnCallOnElementTasks($elements, $callable)) return 0;

		}
		else
		{

			// This could take a while. We'd prefer not to get hung up in the middle...
			craft()->config->maxPowerCaptain();

			$elements = $this->_getCriteria($type)->find();
			WalkPlugin::log("Applying [{$callable}] to " . count($elements) . " elements.", LogLevel::Profile, true);
			if (WalkHelper::craftyArrayWalk($elements, $callable)) return 0;

		}

		return 0;
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionWalkElementIds($args, $type)
	{

		$callable = $args[0] ?? null;
		if (empty($callable))
		{
			$this->_l("Please specify the method.");
			return 1;
		}

		$ids = $this->_getCriteria($type)->ids();

		if ($this->asTask)
		{
			WalkPlugin::log("Applying [{$callable}] to " . count($ids) . " IDs via Tasks: \n" . print_r($ids, true), LogLevel::Profile, true);
			if (WalkHelper::spawnCallOnIdTasks($ids, $callable)) return 0;
		}
		else
		{
			WalkPlugin::log("Applying [{$callable}] to " . count($ids) . " IDs: \n" . print_r($ids, true), LogLevel::Profile, true);
			if (WalkHelper::craftyArrayWalk($ids, $callable)) return 0;
		}

		return 1;

	}


	/**
	 * @param $args
	 * @param null $thing
	 * @param null $thing1
	 * @param null $thing2
	 */
	public function actionTest($args, $thing=null, $thing1=null, $thing2=null)
	{
		echo var_dump($args, $thing, $thing1, $thing2);
	}


	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function actionCount($args=[], $type=null)
	{

		if (empty($type) && !empty($args[0]))
		{
			$type = $args[0];
			if (in_array($type, array_keys($this->_elementTypes)))
			{
				$type = $this->_elementTypes[$args[0]];
			}
		}

		if (empty($type))
		{
			echo "Please specify an element --type param.";
			return 1;
		}

		$this->limit = 'null';
		$count = $this->_getCriteria($type)->count();

		echo "Found {$count} {$type} elements.";

		return 0;

	}


	/*
	 * Public Methods
	 */


	/**
	 *
	 * This method is invoked right before an action is to be executed.
	 *
	 * @param string $action The name of the action to run.
	 * @param array  $params The parameters to be passed to the action's method.
	 *
	 * @return bool Whether the action should be executed or not.
	 */
	public function beforeAction($action, $params)
	{
		echo "\n\n============= { WalkCommand } =============";
		echo "\n------ {$action} ... \n\n";
		return parent::beforeAction($action, $params);
	}

	/**
	 *
	 * This method is invoked right after an action is to be executed.
	 *
	 * @param string $action The name of the action to run.
	 * @param array  $params The parameters to be passed to the action's method.
	 *
	 * @return bool Whether the action should be executed or not.
	 */
	public function afterAction($action, $params, $exitCode = 0)
	{
		echo "\n\n------- / {$action} : code {$exitCode}";
		echo "\n=============================================\n\n";
		return parent::afterAction($action, $params, $exitCode);
	}


	/*
	 * Private Methods
	 */


	/**
	 * @param $type
	 *
	 * @return ElementCriteriaModel
	 */
	private function _getCriteria($type)
	{

		$attributes = [];

		foreach($this->_criteriaOptions as $opt)
		{
			if (isset($this->$opt)) $attributes[$opt] = $this->$opt;
		}

		if ($this->limit == 'null')
		{
			$attributes['limit'] = null;
		}

		if ($this->status == 'null' || $this->status == '*')
		{
			$attributes['status'] = null;
		}

		$criteria = craft()->elements->getCriteria($type, $attributes);

		WalkPlugin::log("Using {$type} criteria: \n" . print_r($attributes, true), LogLevel::Profile, true);

		return $criteria;

	}


	/**
	 * @param string $str
	 */
	private function _l($str = "")
	{
		echo "\n" . $str;
	}


}
