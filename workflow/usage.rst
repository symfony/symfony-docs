.. index::
    single: Workflow; Usage

How to Use the Workflow
=======================

The workflow component gives you an object oriented way to work with state
machines. A state machine lets you define *places*  and *transitions*.
A transition describes the action to get from one place to another.

.. image:: /_images/components/workflow/states_transitions.png

A set of places and transitions creates a **definition**. A workflow needs
a ``Definition`` and a way to write the states to the objects, (i.e. an
instance of a :class:`Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface`.

Consider the following example for a blog post. A post can have places:
'draft', 'review', 'rejected', 'published'. You can define the workflow
like this::

    use Symfony\Component\Workflow\Definition;
    use Symfony\Component\Workflow\Transition;
    use Symfony\Component\Workflow\Workflow;
    use Symfony\Component\Workflow\MarkingStore\ScalarMarkingStore;

    $states = ['draft', 'review', 'rejected', 'published'];
    $transitions[] = new Transition('to_review', ['draft', 'rejected'], 'review');
    $transitions[] = new Transition('publish', 'review', 'published');
    $transitions[] = new Transition('reject', 'review', 'rejected');

    $definition = new Definition($states, $transitions);
    $definition->setInitialPlace('draft');

    $marking = new ScalarMarkingStore('currentState');
    $workflow = new Workflow($definition, $marking);

The ``Workflow`` can now help you to decide what actions that are allowed
on a blog post.

.. code-block:: php

    // ...
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

    // See all the available transition for the post in the current state
    $transitions = $workflow->getEnabledTransitions($post);

Using Events
------------

To make your workflows even more powerful you could construct the ``Workflow``
object with an ``EventDispatcher``. You can now create event listeners to
block transitions ie depending on the data in the blog post. The following
events are dispatched:

* ``workflow.guard``
* ``workflow.[workflow name].guard``
* ``workflow.[workflow name].guard.[transition name]``

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
                'workflow.blogpost.guard.to_review' => array('guardReview'),
            );
        }
    }

With help from the ``EventDispatcher`` and the ``AuditTrailListener`` you
could easily enable logging::

    $logger = new PSR3Logger();
    $subscriber = new AuditTrailListener($logger);
    $dispatcher->addSubscriber($subscriber);

