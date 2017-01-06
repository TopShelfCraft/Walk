# Walk

_A Craft-aware variant of the `array_walk()` method, plus some console commands for extra convenience, to easily apply Craft service/helper/Task methods to a collection of elements._

by [Michael Rog](https://topshelfcraft.com)



### TL;DR.

You want to perform some action, in bulk, on a bunch of elements.

Maybe you want to do it with an easy [console command](#walk-console-commands).

Or, maybe you want to do it in [one line of PHP](#walk-php-usage), in a custom plugin.

And you want to do this with several sets of elements, and with several different bulk actions, without needing to write new code every time.

**Walk** gives you methods to apply any Craft service method, helper method, or Task to each element in a list.

* * *


### Installation

Drop the `walk` directory into your Craft plugins directory, visit the _Settings_ page of the CP, and click to _Install_ the **Walk** plugin.


### Walk a what?

Applying a method to every item in a list is known as "walking" an array. In fact, PHP provides a method — [`array_walk()`](http://php.net/manual/en/function.array-walk.php) — to do just that.

However, PHP's [`array_walk`](http://php.net/manual/en/function.array-walk.php) method isn't aware of how Craft's _services_ and _helpers_ are set up, and it doesn't know anything about Elements.
  
So, where PHP's `array_walk` is useful for applying a _PHP_ method to each item in an array, this plugin provides a _Craft-aware_ walk function — `craftyArrayWalk()` — to run a _Craft_ service method, helper method, or Task on each item in a list. 


### Yes! This will save me _minutes_! How do I start?

First, you need to have a _callable_ in mind. A _callable_ is the method/task that will be run on each item in the list.

<a name="walk-callable-formats" id="walk-callable-formats"></a>
**A callable is specified by its name as a string.** With this plugin, a _callable_ can be:

- a Craft service method: `'service.method'`
- a helper method from the Craft namespace: `'SomeHelper::method'`
- a Craft task: `'DoSomethingTask'` _(more on this [later](#walk-tasks-info)...)_
- any public static method in the root namespace: `'SomeClass::method'`
- any accessible custom function: `'myCustomMethod'`
- any native PHP function, e.g. `'strtolower'`

Second, you need a list of stuff (an _array_).


<a name="walk-php-usage" id="walk-php-usage"></a>
Then, just call the `craftyArrayWalk()` method, like this:

```php
$success = WalkHelper::craftyArrayWalk($elements, $callable);
```

It works pretty much just like the native `array_walk` method:
- The _callable_ is run once for each item in the array.
- For each step, the _item_ is passed as the first parameter to the method.
- The array _index_ is passed as the second parameter to the method.
- The array item is passed _by reference_, meaning if you change the variable inside the method, it will be changed in the source array.
- `craftyArrayWalk()` returns a boolean: `true` if successful, `false` if there was an issue.

You can also provide extra some custom data if needed, by adding a third parameter, like this:
 
```php
$success = WalkHelper::craftyArrayWalk($elements, $callable, $userdata);
```

The `$userdata` will be passed as the third parameter to the _callable_ method on each step.

So, in technical terms, a _callable_ method has the following signature:
```php
public function myMethod( $element [, $index [, $userdata ]] )
```


### I need an example.

Let's start with an example of PHP's native `array_walk`:

You could do this:
```php
$myArray = ["Michael", "Aaron", "Andrew", "Brad", "Brandon"]

foreach ($myArray as $key => $value)
{
    $myArray[$key] = strtolower($value);
}
```
But this is way prettier:
```php
array_walk($myArray, 'strtolower');
```

These two examples accomplish the same thing: Afterwards, each string item in the array will have been transformed to lowercase.

**Now let's look at a _Crafty_ example.**

You could do this:
```php
$myEntries = craft()->elements->getCriteria(ElementType::Entry)->find();

foreach ($myEntries as $entry)
{
    craft()->entries->saveEntry($entry);
}
```
But this is tighter:
```php
WalkHelper::craftyArrayWalk($myEntries, 'entries.saveEntry');
```

In both examples, each Entry in the criteria will be re-saved. 


<a name="walk-console-commands" id="walk-console-commands"></a>
### Nifty... but I don't want to write any PHP. What about some **_console commands_**?

That's actually why I wrote this plugin: I needed a fast, convenient way to do a bunch of indexing/re-saving, preferably from the CLI, without having to write a new custom command for each task/criteria combo.

So, without further ado, I give you... the `walk` _CLI command_.

```shell
php yiic walk [list] [callable] --[options]=blah --asTask
```

So, let's break that down:

- `[list]` is either an element type identifier (`assets`, `entries`, etc.), or an element _IDs_ identifier (`assetIds`, `entryIds`, etc.).
- `[callable]` is the method/task you want to run on each item, as described [above](#walk-callable-formats).
- There are several supported `[options]`, described [below](#walk-command-options).
- The special (optional) `--asTask` option... I'll get to that [later](#walk-tasks-info).
- The order of options is unimportant.

If you want to re-save all your blog entries...
```shell
php yiic walk entries --section=blog --limit=null entries.save
```

If you have a custom service method, and you want to run it once on each user:
```shell
php yiic walk users --limit=null myService.myMethod
```

What if your custom method takes an element _ID_ rather than an element _model_?
```
php yiic walk entryIds SomeHelper:methodThatOperatesOnAnId
```
Tada!


<a name="walk-command-options" id="walk-command-options"></a>
### Command options

The following Element Criteria attributes can be set via CLI option:
- `id`
- `limit` (by default, `7`)
- `title`
- `slug`
- `relatedTo`
- `source`
- `sourceId`
- `kind`
- `filename`
- `folderId`
- `size`
- `group`
- `groupId`
- `authorGroup`
- `authorGroupId`
- `authorId`
- `locale`
- `section`
- `status`


<a name="walk-tasks-info" id="walk-tasks-info"></a>
### You said something about _Tasks_...?

Say you have a lot of elements... or your callable methods are performance-intensive... or you need things to keep running even if your CLI connection is closed... or you just prefer to schedule things one-at-a-time using Tasks...

There are a few ways **Walk** might help.

#### 1. Schedule callables as Tasks using the CLI

You can use the special `--asTask` [command option](#walk-command-options) to schedule each walk step as a Task:

```shell
php yiic walk entries entries.saveEntry --asTask

php yiic walk entryIds MyHelper::myMethod --asTask
```

This will still invoke the callable once for each element or ID in the criteria, but it will do so from inside a Task
— i.e. one request per element/step.

This is nice if you want to keep track of the queue progress, or if you want to be able to conveniently re-run any steps that fail due to an error.

In the Control Panel sidebar (or [Task Manager](https://github.com/boboldehampsink/taskmanager)), these show up as `CallOnElement` and `CallOnId` tasks. 

#### 2. Use a Task as a _callable_ from the CLI

You can also use any Task (from Craft or any installed plugin) as a callable in its own right: 

```shell
php yiic walk assetIds ModifyMyAssetTask
```

This example would schedule a `ModifyMyAsset` task for each element or ID in the criteria.

The specified task should expect to receive an `id` setting containing the element ID.
 
#### 3. Use a Task as a _callable_ via the PHP methods
 
```php
WalkHelper::spawnTasks('ModifyMyAsset', $elementsOrIds, $settings = [], $idParam = 'id')
```

The `spawnTasks` method can be used to schedule one instance of a specified task per element or ID in the provided set.

The task is specified by class name, without the "Task" suffix, just as if you were using `craft()->tasks->createTask()`.

You can supply an array of extra settings in the `$settings` argument, which will be applied to each task.

The specified task should expect to receive an `id` setting containing the element ID. Alternatively, you can also change the name of the setting that will receive the ID of the element, using the `$idParam` argument.


### Can I use this stuff with _any_ array? Does it _have_ to be elements/IDs?

The console commands are designed to perform bulk actions on sets of Elements or IDs.

However, if you're feeling clever, you can use the `craftyArrayWalk()` helper method, **with any array**.

For example, if you have an array of email addresses, and a `processEmailAddress` service method that takes an email address as its first argument...

```php
$success = WalkHelper::craftyArrayWalk($emailAddresses, 'myService.processEmailAddress');
```

Or if you have that same list of email addresses and a custom _ProcessEmailAddressTask_ that expects an email address in the `emailAddress` setting...
 
```php
WalkHelper::spawnTasks('ProcessEmailAddress', $emailAddresses, $settings = [], $idParam = 'emailAddress')
```

If you want to make your own console command that walks through some other set (i.e. not Elements or Element IDs), just check out the source code of the _WalkCommand_. You'll find it pretty easy to copy/paste your way to success!


### What if I want to get a _count_ of elements in a criteria, without actually _doing_ anything to them?

Okay, why not. You can use the `walk count` command for that. Make sure to specify the Element Type using an option, or as an inline argument:

`php yiic walk count entries --section=blog`

`php yiic walk count --type=assets --size=">42M"`


### This is great! I still have questions.

Ask a question on [StackExchange](http://craftcms.stackexchange.com/), and ping me with a URL via email or Slack.


### What are the system requirements?

Craft 2.5+ and PHP 7.0+


### I found a bug.

Please open a GitHub Issue, submit a PR to the `dev` branch, or just email me.


* * *

#### Contributors:

  - Plugin development: [Michael Rog](http://michaelrog.com) / @michaelrog
  - Icon: [Margot Nadot](http://margotnadot.com/), via [The Noun Project](https://thenounproject.com/search/?q=walking&i=79275)
