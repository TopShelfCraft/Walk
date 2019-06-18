<?php
namespace topshelfcraft\walk\console\controllers;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\db\AssetQuery;
use craft\elements\db\CategoryQuery;
use craft\elements\db\ElementQuery;
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
use yii\console\ExitCode;
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

	const COMMERCE_ORDER_ELEMENT_CLASS = 'craft\commerce\elements\Order';
	const COMMERCE_ORDER_QUERY_CLASS = 'craft\commerce\elements\db\OrderQuery';

	/*
	 * Public properties
	 */

	// (Options require the existence of a public member varible whose name is the option name.)
	public $elementClass, $queryClass;
	public $limit, $offset;
	public $title, $slug, $relatedTo;
	public $source, $sourceId, $kind, $filename, $folderId;
	public $group, $groupId;
	public $authorGroup, $authorGroupId, $authorId, $locale, $section, $status;
	public $dateOrdered, $datePaid, $email, $gatewayId, $hasPurchasables,
		$isCompleted, $isPaid, $isUnpaid, $orderStatus, $orderStatusId, $customerId;

	// The parent Controller class already implements an $id varible for its own purposes,
	// but best I can tell, it's okay to overwrite it, so I'm including it here as a reminder to myself.
	public $id;

	// Runner options
	public $asJob = false;


	/*
	 * Private properties
	 */

	private $_elementTypeOptions = [
		'elementClass', 'queryClass',
	];

	private $_queryConfigOptions = [
		'id',
		'limit', 'offset',
		'title', 'slug', 'relatedTo',
		'source', 'sourceId', 'kind', 'filename', 'folderId',
		'group', 'groupId',
		'authorGroup', 'authorGroupId', 'authorId', 'locale', 'section', 'status',
		'dateOrdered', 'datePaid', 'email', 'gatewayId', 'hasPurchasables',
		'isCompleted', 'isPaid', 'isUnpaid', 'orderStatus', 'orderStatusId', 'customerId',
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
			$this->_queryConfigOptions,
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
			$this->stderr("You must specify an action type (e.g. `entries` / `entryIds` / `countEntries`).", Console::FG_RED);
			return ExitCode::USAGE;
		}

		switch ($action)
		{

			case 'assets':
				return $this->actionWalkElements(Asset::class, $callable);
				break;

			case 'assetIds':
				return $this->actionWalkElementIds(Asset::class, $callable);
				break;

			case 'countAssets':
				return $this->actionCount(Asset::class);
				break;

			case 'categories':
			case 'cats':
				return $this->actionWalkElements(Category::class, $callable);
				break;

			case 'categoryIds':
			case 'catIDs':
				return $this->actionWalkElementIds(Category::class, $callable);
				break;

			case 'countCategories':
			case 'countCats':
				return $this->actionCount(Category::class);
				break;

			case 'entries':
				return $this->actionWalkElements(Entry::class, $callable);
				break;

			case 'entryIds':
				return $this->actionWalkElementIds(Entry::class, $callable);
				break;

			case 'countEntries':
				return $this->actionCount(Entry::class);
				break;

			case 'globalSets':
			case 'globals':
				return $this->actionWalkElements(GlobalSet::class, $callable);
				break;

			case 'globalSetIds':
			case 'globalIds':
				return $this->actionWalkElementIds(GlobalSet::class, $callable);
				break;

			case 'countGlobalSets':
			case 'countGlobals':
				return $this->actionCount(GlobalSet::class);
				break;

			case 'matrixBlocks':
				return $this->actionWalkElements(MatrixBlock::class, $callable);
				break;

			case 'matrixBlockIds':
				return $this->actionWalkElementIds(MatrixBlock::class, $callable);
				break;

			case 'countMatrixBlocks':
				return $this->actionCount(MatrixBlock::class);
				break;

			case 'tags':
				return $this->actionWalkElements(Tag::class, $callable);
				break;

			case 'tagIds':
				return $this->actionWalkElementIds(Tag::class, $callable);
				break;

			case 'countTags':
				return $this->actionCount(Tag::class);
				break;

			case 'users':
				return $this->actionWalkElements(User::class, $callable);
				break;

			case 'userIds':
				return $this->actionWalkElementIds(User::class, $callable);
				break;

			case 'countUsers':
				return $this->actionCount(User::class);
				break;

			case 'orders':
				return $this->actionWalkElements(self::COMMERCE_ORDER_ELEMENT_CLASS, $callable);
				break;

			case 'orderIds':
				return $this->actionWalkElementIds(self::COMMERCE_ORDER_ELEMENT_CLASS, $callable);
				break;

			case 'countOrders':
				return $this->actionCount(self::COMMERCE_ORDER_ELEMENT_CLASS);
				break;

		}

	}


	/**
	 * @param $elementClass
	 * @param $callable
	 *
	 * @return int
	 */
	public function actionWalkElements($elementClass = null, $callable = null)
	{

		if (!WalkHelper::isComponentCallable($callable))
		{
			$this->_p("Please specify a valid callable method.");
			return ExitCode::UNSPECIFIED_ERROR;
		}

		if ($this->asJob)
		{
			$ids = $this->_getQuery($elementClass)->ids();
			$count = count($ids);
			Walk::notice("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");
			Walk::notice("Spawning jobs to call [{$callable}] on each element.");
			if (WalkHelper::spawnCallOnElementJobs($ids, $callable)) return ExitCode::OK;
		}
		else
		{
			App::maxPowerCaptain();
			$elements = $this->_getQuery($elementClass)->all();
			$count = count($elements);
			Walk::notice("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");
			Walk::notice("Calling [{$callable}] on each element.");
			if (WalkHelper::craftyArrayWalk($elements, $callable)) return ExitCode::OK;
		}

		return ExitCode::UNSPECIFIED_ERROR;

	}


	/**
	 * @param $elementClass
	 * @param $callable
	 *
	 * @return int
	 */
	public function actionWalkElementIds($elementClass = null, $callable = null)
	{

		$elements = $this->_getQuery($elementClass)->ids();
		$count = count($elements);

		$this->_p("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");

		if (!WalkHelper::isComponentCallable($callable))
		{
			$this->_p("Please specify a valid callable method.");
			return ExitCode::USAGE;
		}

		if ($this->asJob)
		{
			Walk::notice("Creating jobs to call [{$callable}] on each element ID.");
			if (WalkHelper::spawnCallOnIdJobs($elements, $callable)) return ExitCode::OK;
		}
		else
		{
			App::maxPowerCaptain();
			Walk::notice("Calling [{$callable}] on each element ID.");
			if (WalkHelper::craftyArrayWalk($elements, $callable)) return ExitCode::OK;
		}

		return ExitCode::UNSPECIFIED_ERROR;

	}

	/**
	 * @param $elementClass
	 * @param $callable
	 *
	 * @return int
	 */
	public function actionCount($elementClass = null)
	{

		$count = $this->_getQuery($elementClass)->count();

		$this->_p("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");

		return ExitCode::OK;

	}


	/*
	 * Private methods
	 */


	/**
	 * @param string $type
	 *
	 * @return ElementQuery
	 */
	private function _getQuery($elementClass)
	{

		$config = [];

		// TODO: Get the attribute list from the Element
		foreach($this->_queryConfigOptions as $opt)
		{
			if (isset($this->passedOptionValues[$opt]))
			{

				$val = $this->passedOptionValues[$opt];

				if (in_array($opt, ['limit', 'status']))
				{
					$config[$opt] = in_array($val, ['null', '*']) ? null : $val;
				}
				else
				{
					$config[$opt] = $val;
				}

			}
		}

		switch ($elementClass)
		{

			case Asset::class :
				$query = new AssetQuery(Asset::class, $config);
				break;

			case Category::class :
				$query = new CategoryQuery(Category::class, $config);
				break;

			case Entry::class :
				$query = new EntryQuery(Entry::class, $config);
				break;

			case GlobalSet::class :
				$query = new GlobalSetQuery(GlobalSet::class, $config);
				break;

			case MatrixBlock::class :
				$query = new MatrixBlockQuery(MatrixBlock::class, $config);
				break;

			case Tag::class :
				$query = new TagQuery(Tag::class, $config);
				break;

			case User::class :
				$query = new UserQuery(User::class, $config);
				break;

			case self::COMMERCE_ORDER_ELEMENT_CLASS :
				if (Craft::$app->getPlugins()->isPluginInstalled('commerce'))
				{
					$queryClass = self::COMMERCE_ORDER_QUERY_CLASS;
					$query = new $queryClass(self::COMMERCE_ORDER_ELEMENT_CLASS, $config);
				}
				else
				{
					$this->stderr("Craft Commerce is not installed.", Console::FG_RED);
					$query = new ElementQuery(self::COMMERCE_ORDER_ELEMENT_CLASS, $config);
				}
				break;

			default :
				// TODO: Allow user-selected query classes via `queryClass` param
				$query = new ElementQuery(self::COMMERCE_ORDER_ELEMENT_CLASS, $config);
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

	/**
	 * Get a human-readable class name, without the fully-qualified path
	 *
	 * @param string $elementClass
	 * @param int $qty
	 * 
	 * @return mixed
	 */
	private function _getHumanReadableClassName($elementClass = '', $qty = 1)
	{
		$path = explode('\\', $elementClass);
		$name = array_pop($path);
		return ($qty != 1 ? BaseInflector::pluralize($name) : $name);
	}

}
