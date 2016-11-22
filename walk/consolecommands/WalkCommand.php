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
	public $title, $slug;
	public $source, $sourceId, $kind, $filename, $folderId;
	public $group, $groupId;
	public $authorGroup, $authorGroupId, $authorId, $locale, $section, $status;

	// Runner options
	public $asTask = false;


	/*
	 * Private properties
	 */


	private $_criteriaOptions = [
		'id', 'limit', 'offset', 'title', 'slug',
		'source', 'sourceId', 'kind', 'filename', 'folderId',
		'group', 'groupId',
		'authorGroup', 'authorGroupId', 'authorId', 'locale', 'section', 'status',
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
			WalkPlugin::log("Applying [{$callable}] to " . count($elements) . " elements via tasks.");
			if (WalkHelper::spawnCallOnElementTasks($elements, $callable)) return 0;
		}
		else
		{
			$elements = $this->_getCriteria($type)->find();
			WalkPlugin::log("Applying [{$callable}] to " . count($elements) . " elements.");
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
			WalkPlugin::log("Applying [{$callable}] to " . count($ids) . " IDs via Tasks: \n" . print_r($ids, true));
			if (WalkHelper::spawnCallOnIdTasks($ids, $callable)) return 0;
		}
		else
		{
			WalkPlugin::log("Applying [{$callable}] to " . count($ids) . " IDs: \n" . print_r($ids, true));
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
		echo var_dump($args, $thing, $thing1, $thing2, $this->gopt);
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

		$criteria = craft()->elements->getCriteria($type, $attributes);

		WalkPlugin::log("Using {$type} criteria: \n" . print_r($attributes, true));

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
