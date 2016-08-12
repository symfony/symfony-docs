.. index::
   single: Workflow
   single: Components; Workflow

The Workflow Component
======================

    The Workflow component provides tools for managing a workflow or finite state
    machine.

.. versionadded:: 3.2
    The Workflow component was introduced in Symfony 3.2.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/workflow`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/workflow).

.. include:: /components/require_autoload.rst.inc

Usage
-----

The workflow component gives you an object oriented way to work with state machines. A state machine lets you
define *places* (or *states*) and *transactions*. A transaction describes the action to get from one place to another.

.. image:: /_images/components/workflow/states_transactions.png

A set of places and ``Transaction's`` creates a ``Definition``. A ``Workflow`` needs a ``Definition`` and a way to write
the states to the objects, ie an instance of a ``MarkingStoreInterface``.

Consider the following example for a blog post. A post can have places: 'draft', 'review', 'rejected', 'published'. You
can define the workflow like this::

    $states = ['draft', 'review', 'rejected', 'published'];
    $transactions[] = new Transition('to_review', ['draft', 'rejected'], 'review');
    $transactions[] = new Transition('publish', 'review', 'published');
    $transactions[] = new Transition('reject', 'review', 'rejected');

    $definition = new Definition($states, $transactions);
    $definition->setInitialPlace('draft');

    $marking = new ScalarMarkingStore('currentState');
    $workflow = new Workflow($definition, $marking);

The ``Workflow`` can now help you to decide what actions that are allowed on a blog post.

.. code-block:: php
    $post = new \stdClass();
    $post->currentState = null;
    $workflow->can($post, 'publish'); // False
    $workflow->can($post, 'to_draft'); // True

    // Update the currentState on the post
    try {
        $workflow->apply($post, 'to_review');
    } catch (LogicException $e) {
        // ...
    }

    // See all the available transaction for the post in the current state
    $transactions = $workflow->getEnabledTransitions($post);



Using events
------------

To make your workflows even more powerful you could construct the ``Workflow`` object with an ``EventDispatcher``. You
can now create event listeners to block transactions ie depending on the data in the blog post. The following events
are dispatched:

* workflow.guard
* workflow.[workflow name].guard
* workflow.[workflow name].guard.[transaction name]

See example to make sure no blog post without title is moved to "review"::

    $marking = new ScalarMarkingStore('currentState');
    $workflow = new Workflow($definition, $marking, $dispatcher, 'blogpost');

.. code-block:: php
    class BlogPostReviewListener implements EventSubscriberInterface
    {
        public function guardReview(GuardEvent $event)
        {
            $post = $event->getSubject();
            $title = $post->getTitle();

            if (empty($title)) {
                // Posts with no title should not be allowed
                $event->setBlocked(true);
            }
        }

        public static function getSubscribedEvents()
        {
            return array(
                'workflow.blogpost.guad.to_review' => array('guardReview'),
            );
        }
    }

With help from the ``EventDispatcher`` and the ``AuditTrailListener`` you could easily enable logging::

    $logger = new PSR3Logger();
    $subscriber = new AuditTrailListener($logger);
    $dispatcher->addSubscriber($subscriber);

Dumper
------

To help you debug you could dump a representation of your workflow with the use of a ``DumperInterface``. Use the
``GraphvizDumper`` to create a PNG image of the workflow defined above::

    // dump-graph.php
    $dumper = new GraphvizDumper();
    echo $dumper->dump($definition);

.. code-block:: bash

    $ php dump-graph-php > out.dot
    $ dot -Tpng out.dot -o graph.png

The result will look like this:

.. image:: /_images/components/workflow/blogpost.png


.. _Packagist: https://packagist.org/packages/symfony/workflow
