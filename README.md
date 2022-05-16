# Walk

_Provides a Craft-aware `array_walk()` method, plus some super-convenient console commands, allowing you to easily call Craft service methods on a collection of elements or values._

**A [Top Shelf Craft](https://topshelfcraft.com) creation**  
[Michael Rog](https://michaelrog.com), Proprietor


### TL;DR.

You want to perform some action, in bulk, on a bunch of elements.

Maybe you want to do it ad-hoc with an easy [console command](#walk-console-commands).

Or, maybe you want to do it in [one line of PHP](#walk-php-usage), in a custom component.

And you want to do this with several different sets of elements, and with several different bulk actions, without needing to write new code every time.

**Walk** gives you a special Craft-aware `array_walk` method to apply a Craft component method to each element in a list.

* * *


### Installation

Install with Composer by running `composer require topshelfcraft/walk` from your project directory.

Then, visit the _Settings > Plugins_ page of the CP, and click to _Install_ the **Walk** plugin.


### Walk a what?

Applying a method to every item in a list is known as "walking" an array. In fact, PHP provides a method — [`array_walk()`](http://php.net/manual/en/function.array-walk.php) — to do just that.

However, PHP's [`array_walk`](http://php.net/manual/en/function.array-walk.php) method isn't aware of how Craft's _components_ are set up, and it doesn't know anything about Elements.
  
So, where PHP's `array_walk` is useful for applying a _PHP_ method to each item in an array, this plugin provides a _Craft-aware_ walk function — `craftyArrayWalk()` — to run a _Craft_ component method on each item in a list. 


### Yes! This will save me _minutes_! How do I start?

First, you need to have a _callable_ in mind. A _callable_ is the method that will be run on each item in the list.

<a name="walk-callable-formats" id="walk-callable-formats"></a>
**A callable is specified by its name as a string.** With this plugin, a _callable_ can be:

- a method in one of Craft's native components: `'component.method'`
- a method in a plugin's module components: `'plugin.component.method'`
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

_Technically, you can supply any valid PHP callable object to the `craftyArrayWalk` method. But if you already have a callable object in-hand, you probably don't have much need for this plugin. The advantage that this plugin's method provides over the native `array_walk` is that it recognizes those special callable strings, which represent methods in Craft._  

You can also provide extra some custom data if needed, by adding a third parameter, like this:
 
```php
$success = WalkHelper::craftyArrayWalk($elements, $callable, $userdata);
```

The `$userdata` will be passed as the third parameter to the _callable_ method on each step.

So, in technical terms, a _callable_ method has the following _signature_:
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
$myEntries = Entry::find()->all();

foreach ($myEntries as $entry)
{
    Craft::$app->getElements->saveElement($entry);
}
```
But this is tighter:
```php
WalkHelper::craftyArrayWalk($myEntries, 'elements.saveElement');
```

In both examples, each Entry in the query will be re-saved. 


<a name="walk-console-commands" id="walk-console-commands"></a>
### Nifty... but I don't want to write any PHP. What about some **_console commands_**?

That's actually why I wrote this plugin: I needed a fast, convenient way to do a bunch of indexing/re-saving, preferably from the CLI, without having to write a new custom command for each job/criteria combo.

So, without further ado, I give you... the `walk` _CLI command_.

```shell
./craft walk [list] [callable] --[options]=blah --asJob
```

So, let's break that down:

- `[list]` is:
   - an element type identifier (`assets`, `entries`, etc.)
   - an element _IDs_ identifier (`assetIds`, `entryIds`, etc.)
   - a "count" directive (`countAssets`, `countEntries`, etc.)
- `[callable]` is the method/task you want to run on each item, as described [above](#walk-callable-formats).
- There are several supported `[options]`, described [below](#walk-command-options).
- The special (optional) `--asJob` option... I'll get to that [later](#walk-jobs-info).
- The order of _options_ is arbitrary.

If you want to re-save all your blog entries...
```shell
./craft walk entries --section=blog --limit=null elements.saveElement
```

If you have a custom service method, and you want to run it once on each user:
```shell
./craft walk users --limit=null myPlugin.myComponent.myMethod
```

What if your custom method takes an element _ID_ rather than an element _object_?
```
./craft walk entryIds myModule.myComponent.methodThatTakesAnId
```
Tada!

Or, perhaps you want to get a _count_ of elements in a criteria, without actually _doing_ anything to them?

```
./craft walk countEntries --section=blog
```


<a name="walk-command-options" id="walk-command-options"></a>
### Command options

The following Element Criteria attributes can be set via CLI option:
- `id`
- `limit` _(a number, or `null` for no limit)_
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

(...plus, for Commerce Orders...)

- `dateOrdered`
- `datePaid`
- `email`
- `gatewayId`
- `hasPurchasables`
- `isCompleted`
- `isPaid`
- `isUnpaid`
- `orderStatus`
- `orderStatusId`
- `customerId`


<a name="walk-third-party-elements" id="walk-third-party-elements"></a>
### Does it work with custom element types?

The Walk CLI command provides easy shorthands for Craft's built-in element types. However, you can use Walk with **any element type** by supplying its [fully-qualified] class name, along with the [fully-qualified] class name of the associated element _query_.

```shell
./craft walk/elements "mynamespace\elements\MyElementClass" --queryClass="mynamespace\elements\db\MyElementQuery" myPlugin.myComponent.myMethod

./craft walk/element-ids "craft\commerce\elements\Donation" --queryClass="craft\commerce\elements\db\DonationQuery" myPlugin.myComponent.methodThatTakesAnId

./craft walk/count "craft\elements\Entry" --queryClass="craft\elements\db\EntryQuery" elements.saveElement
```


<a name="walk-jobs-info" id="walk-jobs-info"></a>
### Oh, and you said something about _Jobs_...?

Say you have a lot of elements... or your callable methods are performance-intensive... or you need things to keep running even if your CLI connection is closed... or you just prefer to schedule things one-at-a-time using the Craft queue...

There are a couple ways **Walk** might help.

#### 1. Schedule callables as Jobs using the CLI

You can use the special `--asJob` [command option](#walk-command-options) to schedule each walk step as a Job:

```shell
./craft walk entries elements.saveElement --asJob

./craft walk entryIds myModule.someComponent.aMethod --asJob
```

This will still invoke the callable once for each element or ID in the criteria, but it will do so from inside a Job
— i.e. one request per element/step.

This is nice if you want to keep track of the queue progress, or if you want to be able to conveniently re-run any steps that fail due to an error.

In the Control Panel sidebar these show up as `CallOnElement` and `CallOnValue` jobs.

#### 2. Use a Job as a _callable_ via the PHP methods
 
```php
WalkHelper::spawnJobs(MyJob::class, $elementsOrIds, $settings = [], $valParam = 'elementId')
```

The `spawnJobs` method can be used to schedule one instance of a specified job per element or ID in the provided set.

The job is specified by full class name, just as if you were using `Craft::$app->queue->push()`.

You can supply an array of extra settings in the `$settings` argument, which will be applied to each job.

The specified job should expect to receive an `elementId` setting containing the element ID. Alternatively, you can also change the name of the setting that will receive the ID of the element, using the `$valParam` argument.


### Can I use this stuff with _any_ array? Does it _have_ to be elements/IDs?

The console commands are designed to perform bulk actions on sets of Elements or IDs.

However, if you're feeling clever, you can use the `craftyArrayWalk()` helper method **with any array**.

For example, if you have an array of email addresses, and a `processEmailAddress` service method that takes an email address as its first argument...

```php
$success = WalkHelper::craftyArrayWalk($emailAddresses, 'myPlugin.myService.processEmailAddress');
```

If you want to make your own console command that walks through some other set (i.e. not Elements or Element IDs), just check out the source code of the _WalkController_. You'll find it pretty easy to copy/paste your way to success!


### This is great! I still have questions.

Ask a question on [StackExchange](http://craftcms.stackexchange.com/), and ping me with a URL via email or Discord.


### What are the system requirements?

Craft 4.0+ and PHP 8.0+


### I found a bug.

Please open a GitHub Issue, or submit a PR to the `4.x.dev` branch.


* * *

#### Contributors:

  - Plugin development: [Michael Rog](http://michaelrog.com) / @michaelrog
  - Icon: [Margot Nadot](http://margotnadot.com/), via [The Noun Project](https://thenounproject.com/search/?q=walking&i=79275)
