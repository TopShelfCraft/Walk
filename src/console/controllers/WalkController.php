<?php
namespace topshelfcraft\walk\console\controllers;

use Craft;
use craft\base\Element;
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

	// (Options require the existence of a public member variable whose name is the option name.)
	public $queryClass;
	public $limit, $offset;
	public $title, $slug, $relatedTo;
	public $source, $sourceId, $kind, $filename, $folderId;
	// TODO: $size (?)
	public $group, $groupId;
	public $authorGroup, $authorGroupId, $authorId, $locale, $section, $status;
	public $dateOrdered, $datePaid, $email, $gatewayId, $hasPurchasables,
		$isCompleted, $isPaid, $isUnpaid, $orderStatus, $orderStatusId, $customerId;
	// TODO: Order total amount?

	// The parent Controller class already implements an $id varible for its own purposes,
	// but best I can tell, it's okay to overwrite it, so I'm including it here as a reminder to myself.
	public $id;

	// Runner options
	public $asJob = false;


	/*
	 * Private properties
	 */

	private $_elementTypeOptions = [
		'queryClass',
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
			$this->_elementTypeOptions,
			$this->_commandOptions
		);
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		$this->color = true;
		$this->_p('',2);
		$this->stdout("============= { Walk } =============", Console::BOLD);
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
		$this->stdout("====================================", Console::BOLD);
		$this->_p('',2);
		return parent::afterAction($action, $result);
	}


	/*
	 * Public methods --- Command actions
	 */


	/**
	 * Perform a Walk action - e.g. `entries` / `entryIds` / `countEntries` / etc.
	 *
	 * @param null $action
	 * @param null $callable
	 *
	 * @return int
	 */
	public function actionIndex($action = null, $callable = null)
	{

		switch ($action)
		{

			case 'assets':
				return $this->actionElements(Asset::class, $callable);
				break;

			case 'assetIds':
				return $this->actionElementIds(Asset::class, $callable);
				break;

			case 'countAssets':
				return $this->actionCount(Asset::class);
				break;

			case 'categories':
			case 'cats':
				return $this->actionElements(Category::class, $callable);
				break;

			case 'categoryIds':
			case 'catIds':
				return $this->actionElementIds(Category::class, $callable);
				break;

			case 'countCategories':
			case 'countCats':
				return $this->actionCount(Category::class);
				break;

			case 'entries':
				return $this->actionElements(Entry::class, $callable);
				break;

			case 'entryIds':
				return $this->actionElementIds(Entry::class, $callable);
				break;

			case 'countEntries':
				return $this->actionCount(Entry::class);
				break;

			case 'globalSets':
			case 'globals':
				return $this->actionElements(GlobalSet::class, $callable);
				break;

			case 'globalSetIds':
			case 'globalIds':
				return $this->actionElementIds(GlobalSet::class, $callable);
				break;

			case 'countGlobalSets':
			case 'countGlobals':
				return $this->actionCount(GlobalSet::class);
				break;

			case 'matrixBlocks':
				return $this->actionElements(MatrixBlock::class, $callable);
				break;

			case 'matrixBlockIds':
				return $this->actionElementIds(MatrixBlock::class, $callable);
				break;

			case 'countMatrixBlocks':
				return $this->actionCount(MatrixBlock::class);
				break;

			case 'tags':
				return $this->actionElements(Tag::class, $callable);
				break;

			case 'tagIds':
				return $this->actionElementIds(Tag::class, $callable);
				break;

			case 'countTags':
				return $this->actionCount(Tag::class);
				break;

			case 'users':
				return $this->actionElements(User::class, $callable);
				break;

			case 'userIds':
				return $this->actionElementIds(User::class, $callable);
				break;

			case 'countUsers':
				return $this->actionCount(User::class);
				break;

			case 'orders':
				return $this->actionElements(self::COMMERCE_ORDER_ELEMENT_CLASS, $callable);
				break;

			case 'orderIds':
				return $this->actionElementIds(self::COMMERCE_ORDER_ELEMENT_CLASS, $callable);
				break;

			case 'countOrders':
				return $this->actionCount(self::COMMERCE_ORDER_ELEMENT_CLASS);
				break;

		}

		$this->stderr("You must specify a valid action type (e.g. `entries` / `entryIds` / `countEntries`).", Console::FG_RED);
		return ExitCode::USAGE;

	}


	/**
	 * Walk over a set of elements, calling the specified callable on each Element object.
	 *
	 * @param $elementClass
	 * @param $callable
	 *
	 * @return int
	 */
	public function actionElements($elementClass = Element::class, $callable = null)
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
			if (WalkHelper::spawnCallOnElementJobs($ids, $callable))
			{
				return ExitCode::OK;
			}
		}
		else
		{
			App::maxPowerCaptain();
			$elements = $this->_getQuery($elementClass)->all();
			$count = count($elements);
			Walk::notice("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");
			Walk::notice("Calling [{$callable}] on each element.");
			if (WalkHelper::craftyArrayWalk($elements, $callable))
			{
				return ExitCode::OK;
			}
		}

		return ExitCode::UNSPECIFIED_ERROR;

	}


	/**
	 * Walk over a set of elements, calling the specified callable on each element's ID.
	 *
	 * @param $elementClass
	 * @param $callable
	 *
	 * @return int
	 */
	public function actionElementIds($elementClass = Element::class, $callable = null)
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
			if (WalkHelper::spawnCallOnIdJobs($elements, $callable))
			{
				return ExitCode::OK;
			}
		}
		else
		{
			App::maxPowerCaptain();
			Walk::notice("Calling [{$callable}] on each element ID.");
			if (WalkHelper::craftyArrayWalk($elements, $callable))
			{
				return ExitCode::OK;
			}
		}

		return ExitCode::UNSPECIFIED_ERROR;

	}

	/**
	 * Count how many elements match the given criteria.
	 *
	 * @param $elementClass
	 * @param $callable
	 *
	 * @return int
	 */
	public function actionCount($elementClass = Element::class)
	{

		$count = $this->_getQuery($elementClass)->count();

		$this->_p("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");

		return ExitCode::OK;

	}


	/*
	 * Private methods
	 */


	/**
	 * @param string $elementClass
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
				return new AssetQuery(Asset::class, $config);

			case Category::class :
				return new CategoryQuery(Category::class, $config);

			case Entry::class :
				return new EntryQuery(Entry::class, $config);

			case GlobalSet::class :
				return new GlobalSetQuery(GlobalSet::class, $config);

			case MatrixBlock::class :
				return new MatrixBlockQuery(MatrixBlock::class, $config);

			case Tag::class :
				return new TagQuery(Tag::class, $config);

			case User::class :
				return new UserQuery(User::class, $config);

			case self::COMMERCE_ORDER_ELEMENT_CLASS :
				if (Craft::$app->getPlugins()->isPluginInstalled('commerce'))
				{
					$queryClass = self::COMMERCE_ORDER_QUERY_CLASS;
					return new $queryClass(self::COMMERCE_ORDER_ELEMENT_CLASS, $config);
				}
				$this->stderr("Craft Commerce is not installed.", Console::FG_RED);
				return new ElementQuery(self::COMMERCE_ORDER_ELEMENT_CLASS, $config);

		}

		if ($this->queryClass)
		{
			$queryClass = $this->queryClass;
			return new $queryClass($elementClass, $config);
		}

		return new ElementQuery($elementClass, $config);

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
