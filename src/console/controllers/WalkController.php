<?php
namespace topshelfcraft\walk\console\controllers;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\db\AssetQuery;
use craft\elements\db\CategoryQuery;
use craft\elements\db\EntryQuery;
use craft\elements\db\GlobalSetQuery;
use craft\elements\db\MatrixBlockQuery;
use craft\elements\db\TagQuery;
use craft\elements\db\UserQuery;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\elements\MatrixBlock;
use craft\elements\Tag;
use craft\elements\User;
use craft\helpers\App;
use topshelfcraft\walk\helpers\WalkHelper;
use topshelfcraft\walk\Walk;
use yii\console\Controller;
use yii\helpers\BaseInflector;
use yii\helpers\Console;



/**
 * WalkCommand walks, like the walking man.
 *
 * @author    Michael Rog <michael@michaelrog.com>
 * @copyright Copyright (c) 2016+ Michael Rog
 * @see       https://topshelfcraft.com
 * @package   craft.plugins.walk
 * @since     3.0
 */
class WalkController extends Controller
{


	/*
	 * Public properties
	 */

	// (Options require the existence of a public member varible whose name is the option name.)
	public $limit, $offset;
	public $title, $slug, $relatedTo;
	public $source, $sourceId, $kind, $filename, $folderId;
	public $group, $groupId;
	public $authorGroup, $authorGroupId, $authorId, $locale, $section, $status;

	// The parent Controller class already implements an $id varible for its own purposes,
	// but best I can tell, it's okay to overwrite it, so I'm including it here as a reminder to myself.
	public $id;

	// Runner options
	public $asJob = false;


	/*
	 * Private properties
	 */


	private $_queryOptions = [
		'id', 'limit', 'offset', 'title', 'slug', 'relatedTo',
		'source', 'sourceId', 'kind', 'filename', 'folderId',
		'group', 'groupId',
		'authorGroup', 'authorGroupId', 'authorId', 'locale', 'section', 'status',
	];

	private $_commandOptions = [
		'asJob',
	];



	/*
	 * Public methods --- Class customization
	 */


	/**
	 * @inheritdoc
	 */
	public function options($actionID)
	{
		return array_merge(
			parent::options($actionID),
			$this->_queryOptions,
			$this->_commandOptions
		);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws Exception if the 'plugin' option isn't valid
	 */
	public function beforeAction($action)
	{
		$this->color = true;
		$this->_p('',2);
		$this->stdout("============= { Walk } =============", Console::FG_BLUE, Console::BOLD);
		$this->_p('',2);
		return parent::beforeAction($action);
	}


	/**
	 * @param \yii\base\Action $action
	 * @param mixed $result
	 *
	 * @return mixed
	 */
	public function afterAction($action, $result)
	{
		$this->_p('',2);
		$this->stdout("====================================", Console::FG_BLUE, Console::BOLD);
		$this->_p('',2);
		return parent::afterAction($action, $result);
	}


	/*
	 * Public methods --- Command actions
	 */


	/**
	 *
	 */
	public function actionIndex($action = '', $callable = null)
	{

		if (empty($action))
		{
			$this->stderr("You must specify an action type (i.e. `entries` or `entryIds`).", Console::FG_RED);
			return 1;
		}

		switch ($action)
		{

			case 'assets':
				return $this->actionWalkElements('Asset', $callable);
				break;

			case 'assetIds':
				return $this->actionWalkElementIds('Asset', $callable);
				break;

			case 'categories':
			case 'cats':
				return $this->actionWalkElements('Category', $callable);
				break;

			case 'categoryIds':
			case 'catIDs':
				return $this->actionWalkElementIds('Category', $callable);
				break;

			case 'entries':
				return $this->actionWalkElements('Entry', $callable);
				break;

			case 'entryIds':
				return $this->actionWalkElementIds('Entry', $callable);
				break;

			case 'globalSets':
			case 'globals':
				return $this->actionWalkElements('GlobalSet', $callable);
				break;

			case 'globalSetIds':
			case 'globalIds':
				return $this->actionWalkElementIds('GlobalSet', $callable);
				break;

			case 'matrixBlocks':
				return $this->actionWalkElements('MatrixBlock', $callable);
				break;

			case 'matrixBlockIds':
				return $this->actionWalkElementIds('MatrixBlock', $callable);
				break;

			case 'tags':
				return $this->actionWalkElements('Tag', $callable);
				break;

			case 'tagIds':
				return $this->actionWalkElementIds('Tag', $callable);
				break;

			case 'users':
				return $this->actionWalkElements('User', $callable);
				break;

			case 'userIds':
				return $this->actionWalkElementIds('User', $callable);
				break;

		}

	}


	/**
	 * @param $elementType
	 * @param $callable
	 *
	 * @return int
	 */
	public function actionWalkElements($elementType = '', $callable = null)
	{

		$elements = $this->_getQuery($elementType)->all();
		$count = count($elements);

		$this->_p("Found {$count} " . ($count != 1 ? BaseInflector::pluralize($elementType) : $elementType));

		if (!WalkHelper::isComponentCallable($callable))
		{
			$this->_p("Please specify a valid callable method.");
			return 1;
		}

		if ($this->asJob)
		{
			Walk::notice("Creating jobs to call [{$callable}] on each {$elementType}.");
			if (WalkHelper::spawnCallOnElementJobs($elements, $callable)) return 0;
		}
		else
		{
			App::maxPowerCaptain();
			Walk::notice("Calling [{$callable}] on each {$elementType}.");
			if (WalkHelper::craftyArrayWalk($elements, $callable)) return 0;
		}

		return 1;

	}


	/**
	 * @param $elementType
	 * @param $callable
	 *
	 * @return int
	 */
	public function actionWalkElementIds($elementType = '', $callable = null)
	{

		$elements = $this->_getQuery($elementType)->ids();
		$count = count($elements);

		$this->_p("Found {$count} " . ($count != 1 ? BaseInflector::pluralize($elementType) : $elementType));

		if (!WalkHelper::isComponentCallable($callable))
		{
			$this->_p("Please specify a valid callable method.");
			return 1;
		}

		if ($this->asJob)
		{
			Walk::notice("Creating jobs to call [{$callable}] on each ID.");
			if (WalkHelper::spawnCallOnIdJobs($elements, $callable)) return 0;
		}
		else
		{
			Walk::notice("Calling [{$callable}] on each ID.");
			if (WalkHelper::craftyArrayWalk($elements, $callable)) return 0;
		}

		return 1;

	}


	/*
	 * Private methods
	 */


	/**
	 * @param string $type
	 *
	 * @return ElementQuery
	 */
	private function _getQuery($type)
	{

		$criteria = [];

		// TODO: Get the attribute list from the Element
		foreach($this->_queryOptions as $opt)
		{
			if (isset($this->passedOptionValues[$opt]))
			{

				$val = $this->passedOptionValues[$opt];

				if (in_array($opt, ['limit', 'status']))
				{
					$criteria[$opt] = in_array($val, ['null', '*']) ? null : $val;
				}
				else
				{
					$criteria[$opt] = $val;
				}

			}
		}

		switch ($type)
		{

			case 'Asset':
				$query = new AssetQuery(Asset::class, $criteria);
				break;

			case 'Category':
				$query = new CategoryQuery(Category::class, $criteria);
				break;

			case 'Entry':
				$query = new EntryQuery(Entry::class, $criteria);
				break;

			case 'GlobalSet':
				$query = new GlobalSetQuery(GlobalSet::class, $criteria);
				break;

			case 'MatrixBlock':
				$query = new MatrixBlockQuery(MatrixBlock::class, $criteria);
				break;

			case 'Tag':
				$query = new TagQuery(Tag::class, $criteria);
				break;

			case 'User':
				$query = new UserQuery(User::class, $criteria);
				break;

		}

		return $query;

	}


	/**
	 * Outputs spacer lines
	 *
	 * @param int $lines The number of blank lines
	 * @param string $msg The message to append after the lines
	 */
	private function _p($msg = '', $lines = 1)
	{
		$this->stdout(str_repeat("\n", $lines) . print_r($msg, true));
	}


}
