<?php
namespace TopShelfCraft\Walk\controllers\console;

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
use TopShelfCraft\base\controllers\console\ConsoleControllerTrait;
use TopShelfCraft\Walk\helpers\WalkHelper;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseInflector;
use yii\helpers\Console;

class WalkController extends Controller
{

	use ConsoleControllerTrait;

	const COMMERCE_ORDER_ELEMENT_CLASS = 'craft\commerce\elements\Order';
	const COMMERCE_ORDER_QUERY_CLASS = 'craft\commerce\elements\db\OrderQuery';

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
	 * Perform a Walk action - e.g. `entries` / `entryIds` / `countEntries` / etc.
	 */
	public function actionIndex(?string $action = null, ?string $callable = null): int
	{

		switch ($action)
		{

			case 'assets':
				return $this->actionElements(Asset::class, $callable);

			case 'assetIds':
				return $this->actionElementIds(Asset::class, $callable);

			case 'countAssets':
				return $this->actionCount(Asset::class);

			case 'categories':
			case 'cats':
				return $this->actionElements(Category::class, $callable);

			case 'categoryIds':
			case 'catIds':
				return $this->actionElementIds(Category::class, $callable);

			case 'countCategories':
			case 'countCats':
				return $this->actionCount(Category::class);

			case 'entries':
				return $this->actionElements(Entry::class, $callable);

			case 'entryIds':
				return $this->actionElementIds(Entry::class, $callable);

			case 'countEntries':
				return $this->actionCount(Entry::class);

			case 'globalSets':
			case 'globals':
				return $this->actionElements(GlobalSet::class, $callable);

			case 'globalSetIds':
			case 'globalIds':
				return $this->actionElementIds(GlobalSet::class, $callable);

			case 'countGlobalSets':
			case 'countGlobals':
				return $this->actionCount(GlobalSet::class);

			case 'matrixBlocks':
				return $this->actionElements(MatrixBlock::class, $callable);

			case 'matrixBlockIds':
				return $this->actionElementIds(MatrixBlock::class, $callable);

			case 'countMatrixBlocks':
				return $this->actionCount(MatrixBlock::class);

			case 'tags':
				return $this->actionElements(Tag::class, $callable);

			case 'tagIds':
				return $this->actionElementIds(Tag::class, $callable);

			case 'countTags':
				return $this->actionCount(Tag::class);

			case 'users':
				return $this->actionElements(User::class, $callable);

			case 'userIds':
				return $this->actionElementIds(User::class, $callable);

			case 'countUsers':
				return $this->actionCount(User::class);

			case 'orders':
				return $this->actionElements(self::COMMERCE_ORDER_ELEMENT_CLASS, $callable);

			case 'orderIds':
				return $this->actionElementIds(self::COMMERCE_ORDER_ELEMENT_CLASS, $callable);

			case 'countOrders':
				return $this->actionCount(self::COMMERCE_ORDER_ELEMENT_CLASS);

		}

		$this->stderr("You must specify a valid action type (e.g. `entries` / `entryIds` / `countEntries`).", Console::FG_RED);
		return ExitCode::USAGE;

	}

	/**
	 * Walk over a set of elements, calling the specified callable on each Element object.
	 */
	public function actionElements(string $elementClass = Element::class, ?string $callable = null): int
	{

		if (!WalkHelper::isComponentCallable($callable))
		{
			$this->writeErr("Please specify a valid callable method.");
			return ExitCode::UNSPECIFIED_ERROR;
		}

		if ($this->asJob)
		{
			$ids = $this->_getQuery($elementClass)->ids();
			$count = count($ids);
			$this->writeLine("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");
			$this->writeLine("Spawning jobs to call [{$callable}] on each element.");
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
			$this->writeLine("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");
			$this->writeLine("Calling [{$callable}] on each element.");
			if (WalkHelper::craftyArrayWalk($elements, $callable))
			{
				return ExitCode::OK;
			}
		}

		return ExitCode::UNSPECIFIED_ERROR;

	}

	/**
	 * Walk over a set of elements, calling the specified callable on each element's ID.
	 */
	public function actionElementIds(string $elementClass = Element::class, ?string $callable = null): int
	{

		$elements = $this->_getQuery($elementClass)->ids();
		$count = count($elements);

		$this->writeLine("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");

		if (!WalkHelper::isComponentCallable($callable))
		{
			$this->writeErr("Please specify a valid callable method.");
			return ExitCode::USAGE;
		}

		if ($this->asJob)
		{
			$this->writeLine("Creating jobs to call [{$callable}] on each element ID.");
			if (WalkHelper::spawnCallOnIdJobs($elements, $callable))
			{
				return ExitCode::OK;
			}
		}
		else
		{
			App::maxPowerCaptain();
			$this->writeLine("Calling [{$callable}] on each element ID.");
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

		$this->writeLine("Found {$count} " . $this->_getHumanReadableClassName($elementClass, $count) . ".");

		return ExitCode::OK;

	}

	private function _getQuery(string $elementClass): ElementQuery
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
	 * Get a human-readable class name, without the fully-qualified path.
	 */
	private function _getHumanReadableClassName(string $elementClass = '', int $qty = 1): string
	{
		$path = explode('\\', $elementClass);
		$name = array_pop($path);
		return ($qty != 1 ? BaseInflector::pluralize($name) : $name);
	}

}
