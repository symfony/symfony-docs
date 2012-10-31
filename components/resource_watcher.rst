.. index::
   single: ResourceWatcher

The ResourceWatcher Component
=============================

Problem to solve
----------------

The problem that this component solves is when we have a system of files and folders that we want to watch for any
actions and react accordingly upon specific actions. Actions can be creation, edition, moving or removing. Upon each
action there could be a need to perform a specific re-action therefore the need for events and callbacks.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/ResourceWatcher);
* Install it via PEAR ( `pear.symfony.com/ResourceWatcher`);
* Install it via Composer (`symfony/ResourceWatcher` on Packagist).

Architecture
------------

The way ResourceWatcher component is implemented to solve the problem is via main object resource watcher, a tracker
object, some resources objects to be tracked, events objects fired depending on what action happens on these resources,
generic event listeners already built-in to listen to the events fired when resources are acted upon, and state checkers
for these resources which will aid in discerning the state of a particular resource. Currently there are three types of
instances when we can react or types of events, namely: (when resource is) Created, Modified, or Deleted.

ResurceWatcher class can receive any custom tracker object that suits the developer, however the component comes with
two implementations of tracker ready to be used as sensitive defaults. In other words, if custom tracker is not provided
then the resource watcher will use an InotifyTracker or RecursiveIteratorTracker implementation. Once a tracker has been
specified and a watcher has been built with it then the next step is to determine what resources we want to track.

Usage
-----

Example using ResourceWatcher with a custom tracker explicitly set, with folder `src` as resource and it will output on
console the name of the file being acted upon checking every microsecond within the 10000 first microseconds:

.. code-block:: php

    use Symfony\Component\ResourceWatcher\ResourceWatcher;
    use Symfony\Component\ResourceWatcher\Tracker\InotifyTracker;
    use Symfony\Component\Config\Resource\DirectoryResource;

    $tracker = new InotifyTracker();
    $watcher = new ResourceWatcher($tracker);
    $resource = new DirectoryResource(realpath(__DIR__.'/../src'));
    $callBack = function ($event) {
                    $fileName = (string) $event->getResource();
                    echo $fileName;
                };
    $watcher->track($resource, $callBack);
    $watcher->start(1, 10000);

Example using ResourceWatcher using default sensitive tracker, lame callback and single file as resource, checking every
microsecond for a time limit of 1 microsecond, and finally making sure it stops tracking resource right after.

.. code-block::php

    use Symfony\Component\ResourceWatcher\ResourceWatcher;
    use Symfony\Component\Config\Resource\FileResource;

    $watcher = new ResourceWatcher();
    $cb = function(){};
    $resource = new FileResource('demo.txt');
    $watcher->track($resource, $cb);
    $watcher->start(1, 1);
    $watcher->stop();

