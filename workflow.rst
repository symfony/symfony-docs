Workflow
========

Using the Workflow component inside a Symfony application requires knowing first
some basic theory and concepts about workflows and state machines.
:doc:`Read this article </workflow/workflow-and-state-machine>` for a quick overview.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the workflow feature before using it:

.. code-block:: terminal

    $ composer require symfony/workflow

Configuration
-------------

To see all configuration options, if you are using the component inside a
Symfony project run this command:

.. code-block:: terminal

    $ php bin/console config:dump-reference framework workflows

Creating a Workflow
-------------------

A workflow is a process or a lifecycle that your objects go through. Each
step or stage in the process is called a *place*. You also define *transitions*,
which describe the action needed to get from one place to another.

.. image:: /_images/components/workflow/states_transitions.png
    :alt: An example state diagram for a workflow, showing transitions and places.

A set of places and transitions creates a **definition**. A workflow needs
a ``Definition`` and a way to write the states to the objects (i.e. an
instance of a :class:`Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface`.)

Consider the following example for a blog post. A post can have these places:
``draft``, ``reviewed``, ``rejected``, ``published``. You could define the workflow as
follows:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                blog_publishing:
                    type: 'workflow' # or 'state_machine'
                    audit_trail:
                        enabled: true
                    marking_store:
                        type: 'method'
                        property: 'currentPlace'
                    supports:
                        - App\Entity\BlogPost
                    initial_marking: draft
                    places:
                        - draft
                        - reviewed
                        - rejected
                        - published
                    transitions:
                        to_review:
                            from: draft
                            to:   reviewed
                        publish:
                            from: reviewed
                            to:   published
                        reject:
                            from: reviewed
                            to:   rejected

    .. code-block:: xml

        <!-- config/packages/workflow.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
            https://symfony.com/schema/dic/services/services-1.0.xsd
            http://symfony.com/schema/dic/symfony
            https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- or type="state_machine" -->
                <framework:workflow name="blog_publishing" type="workflow">
                    <framework:audit-trail enabled="true"/>
                    <framework:marking-store type="single_state">
                        <framework:argument>currentPlace</framework:argument>
                    </framework:marking-store>
                    <framework:support>App\Entity\BlogPost</framework:support>
                    <framework:initial-marking>draft</framework:initial-marking>
                    <framework:place>draft</framework:place>
                    <framework:place>reviewed</framework:place>
                    <framework:place>rejected</framework:place>
                    <framework:place>published</framework:place>
                    <framework:transition name="to_review">
                        <framework:from>draft</framework:from>
                        <framework:to>reviewed</framework:to>
                    </framework:transition>
                    <framework:transition name="publish">
                        <framework:from>reviewed</framework:from>
                        <framework:to>published</framework:to>
                    </framework:transition>
                    <framework:transition name="reject">
                        <framework:from>reviewed</framework:from>
                        <framework:to>rejected</framework:to>
                    </framework:transition>
                </framework:workflow>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/workflow.php
        use App\Entity\BlogPost;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $blogPublishing = $framework->workflows()->workflows('blog_publishing');
            $blogPublishing
                ->type('workflow') // or 'state_machine'
                ->supports([BlogPost::class])
                ->initialMarking(['draft']);

            $blogPublishing->auditTrail()->enabled(true);
            $blogPublishing->markingStore()
                ->type('method')
                ->property('currentPlace');

            $blogPublishing->place()->name('draft');
            $blogPublishing->place()->name('reviewed');
            $blogPublishing->place()->name('rejected');
            $blogPublishing->place()->name('published');

            $blogPublishing->transition()
                ->name('to_review')
                    ->from(['draft'])
                    ->to(['reviewed']);

            $blogPublishing->transition()
                ->name('publish')
                    ->from(['reviewed'])
                    ->to(['published']);

            $blogPublishing->transition()
                ->name('reject')
                    ->from(['reviewed'])
                    ->to(['rejected']);
        };

.. tip::

    If you are creating your first workflows, consider using the ``workflow:dump``
    command to :doc:`debug the workflow contents </workflow/dumping-workflows>`.

.. tip::

    You can use PHP constants in YAML files via the ``!php/const `` notation.
    E.g. you can use ``!php/const App\Entity\BlogPost::STATE_DRAFT`` instead of
    ``'draft'`` or ``!php/const App\Entity\BlogPost::TRANSITION_TO_REVIEW``
    instead of ``'to_review'``.

The configured property will be used via its implemented getter/setter methods by the marking store::

    // src/Entity/BlogPost.php
    namespace App\Entity;

    class BlogPost
    {
        // the configured marking store property must be declared
        private $currentPlace;
        private $title;
        private $content;

        // getter/setter methods must exist for property access by the marking store
        public function getCurrentPlace()
        {
            return $this->currentPlace;
        }

        public function setCurrentPlace($currentPlace, $context = [])
        {
            $this->currentPlace = $currentPlace;
        }

        // you don't need to set the initial marking in the constructor or any other method;
        // this is configured in the workflow with the 'initial_marking' option
    }

.. note::

    The marking store type could be "multiple_state" or "single_state". A single
    state marking store does not support a model being on multiple places at the
    same time. This means a "workflow" must use a "multiple_state" marking store
    and a "state_machine" must use a "single_state" marking store. Symfony
    configures the marking store according to the "type" by default, so it's
    preferable to not configure it.

    A single state marking store uses a ``string`` to store the data. A multiple
    state marking store uses an ``array`` to store the data. If no state marking
    store is defined you have to return ``null`` in both cases (e.g. the above
    example should define a return type like ``App\Entity\BlogPost::getCurrentPlace(): ?array``
    or like ``App\Entity\BlogPost::getCurrentPlace(): ?string``).

.. tip::

    The ``marking_store.type`` (the default value depends on the ``type`` value)
    and ``property`` (default value ``['marking']``) attributes of the
    ``marking_store`` option are optional. If omitted, their default values will
    be used. It's highly recommended to use the default value.

.. tip::

    Setting the ``audit_trail.enabled`` option to ``true`` makes the application
    generate detailed log messages for the workflow activity.

With this workflow named ``blog_publishing``, you can get help to decide
what actions are allowed on a blog post::

    use App\Entity\BlogPost;
    use Symfony\Component\Workflow\Exception\LogicException;

    $post = new BlogPost();
    // you don't need to set the initial marking with code; this is configured
    // in the workflow with the 'initial_marking' option

    $workflow = $this->container->get('workflow.blog_publishing');
    $workflow->can($post, 'publish'); // False
    $workflow->can($post, 'to_review'); // True

    // Update the currentState on the post
    try {
        $workflow->apply($post, 'to_review');
    } catch (LogicException $exception) {
        // ...
    }

    // See all the available transitions for the post in the current state
    $transitions = $workflow->getEnabledTransitions($post);
    // See a specific available transition for the post in the current state
    $transition = $workflow->getEnabledTransition($post, 'publish');

Accessing the Workflow in a Class
---------------------------------

You can use the workflow inside a class by using
:doc:`service autowiring </service_container/autowiring>` and using
``camelCased workflow name + Workflow`` as parameter name. If it is a state
machine type, use ``camelCased workflow name + StateMachine``::

    use App\Entity\BlogPost;
    use Symfony\Component\Workflow\WorkflowInterface;

    class MyClass
    {
        private $blogPublishingWorkflow;

        // Symfony will inject the 'blog_publishing' workflow configured before
        public function __construct(WorkflowInterface $blogPublishingWorkflow)
        {
            $this->blogPublishingWorkflow = $blogPublishingWorkflow;
        }

        public function toReview(BlogPost $post)
        {
            // Update the currentState on the post
            try {
                $this->blogPublishingWorkflow->apply($post, 'to_review');
            } catch (LogicException $exception) {
                // ...
            }
            // ...
        }
    }

.. tip::

    You can find the list of available workflow services with the
    ``php bin/console debug:autowiring workflow`` command.

Using Events
------------

To make your workflows more flexible, you can construct the ``Workflow``
object with an ``EventDispatcher``. You can now create event listeners to
block transitions (i.e. depending on the data in the blog post) and do
additional actions when a workflow operation happened (e.g. sending
announcements).

Each step has three events that are fired in order:

* An event for every workflow;
* An event for the workflow concerned;
* An event for the workflow concerned with the specific transition or place name.

When a state transition is initiated, the events are dispatched in the following
order:

``workflow.guard``
    Validate whether the transition is blocked or not (see
    :ref:`guard events <workflow-usage-guard-events>` and
    :ref:`blocking transitions <workflow-blocking-transitions>`).

    The three events being dispatched are:

    * ``workflow.guard``
    * ``workflow.[workflow name].guard``
    * ``workflow.[workflow name].guard.[transition name]``

``workflow.leave``
    The subject is about to leave a place.

    The three events being dispatched are:

    * ``workflow.leave``
    * ``workflow.[workflow name].leave``
    * ``workflow.[workflow name].leave.[place name]``

``workflow.transition``
    The subject is going through this transition.

    The three events being dispatched are:

    * ``workflow.transition``
    * ``workflow.[workflow name].transition``
    * ``workflow.[workflow name].transition.[transition name]``

``workflow.enter``
    The subject is about to enter a new place. This event is triggered right
    before the subject places are updated, which means that the marking of the
    subject is not yet updated with the new places.

    The three events being dispatched are:

    * ``workflow.enter``
    * ``workflow.[workflow name].enter``
    * ``workflow.[workflow name].enter.[place name]``

``workflow.entered``
    The subject has entered in the places and the marking is updated.

    The three events being dispatched are:

    * ``workflow.entered``
    * ``workflow.[workflow name].entered``
    * ``workflow.[workflow name].entered.[place name]``

``workflow.completed``
    The object has completed this transition.

    The three events being dispatched are:

    * ``workflow.completed``
    * ``workflow.[workflow name].completed``
    * ``workflow.[workflow name].completed.[transition name]``

``workflow.announce``
    Triggered for each transition that now is accessible for the subject.

    The three events being dispatched are:

    * ``workflow.announce``
    * ``workflow.[workflow name].announce``
    * ``workflow.[workflow name].announce.[transition name]``

    After a transition is applied, the announce event tests for all available
    transitions. That will trigger all :ref:`guard events <workflow-usage-guard-events>`
    once more, which could impact performance if they include intensive CPU or
    database workloads.

    If you don't need the announce event, disable it using the context::

        $workflow->apply($subject, $transitionName, [Workflow::DISABLE_ANNOUNCE_EVENT => true]);

    .. versionadded:: 5.1

        The ``Workflow::DISABLE_ANNOUNCE_EVENT`` constant was introduced in Symfony 5.1.

.. versionadded:: 5.2

    In Symfony 5.2, the context is customizable for all events except for
    ``workflow.guard`` events, which will not receive the custom ``$context``::

        // $context must be an array
        $context = ['context_key' => 'context_value'];
        $workflow->apply($subject, $transitionName, $context);

        // in an event listener
        $context = $event->getContext(); // returns ['context']

.. note::

    The leaving and entering events are triggered even for transitions that stay
    in the same place.

.. note::

    If you initialize the marking by calling ``$workflow->getMarking($object);``,
    then the ``workflow.[workflow_name].entered.[initial_place_name]`` event will
    be called with the default context (``Workflow::DEFAULT_INITIAL_CONTEXT``).

Here is an example of how to enable logging for every time a "blog_publishing"
workflow leaves a place::

    // src/App/EventSubscriber/WorkflowLoggerSubscriber.php
    namespace App\EventSubscriber;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\Event;

    class WorkflowLoggerSubscriber implements EventSubscriberInterface
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function onLeave(Event $event)
        {
            $this->logger->alert(sprintf(
                'Blog post (id: "%s") performed transition "%s" from "%s" to "%s"',
                $event->getSubject()->getId(),
                $event->getTransition()->getName(),
                implode(', ', array_keys($event->getMarking()->getPlaces())),
                implode(', ', $event->getTransition()->getTos())
            ));
        }

        public static function getSubscribedEvents()
        {
            return [
                'workflow.blog_publishing.leave' => 'onLeave',
            ];
        }
    }

If some listeners update the context during a transition, you can retrieve
it via the marking::

    $marking = $workflow->apply($post, 'to_review');

    // contains the new value
    $marking->getContext();

.. versionadded:: 5.4

    The ability to get the new value from the marking was introduced in Symfony 5.4.

.. _workflow-usage-guard-events:

Guard Events
~~~~~~~~~~~~

There are special types of events called "Guard events". Their event listeners
are invoked every time a call to ``Workflow::can()``, ``Workflow::apply()`` or
``Workflow::getEnabledTransitions()`` is executed. With the guard events you may
add custom logic to decide which transitions should be blocked or not. Here is a
list of the guard event names.

* ``workflow.guard``
* ``workflow.[workflow name].guard``
* ``workflow.[workflow name].guard.[transition name]``

This example stops any blog post being transitioned to "reviewed" if it is
missing a title::

    // src/App/EventSubscriber/BlogPostReviewSubscriber.php
    namespace App\EventSubscriber;

    use App\Entity\BlogPost;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\GuardEvent;

    class BlogPostReviewSubscriber implements EventSubscriberInterface
    {
        public function guardReview(GuardEvent $event)
        {
            /** @var BlogPost $post */
            $post = $event->getSubject();
            $title = $post->title;

            if (empty($title)) {
                $event->setBlocked(true, 'This blog post cannot be marked as reviewed because it has no title.');
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                'workflow.blog_publishing.guard.to_review' => ['guardReview'],
            ];
        }
    }

.. versionadded:: 5.1

    The optional second argument of ``setBlocked()`` was introduced in Symfony 5.1.

.. _workflow-chosing-events-to-dispatch:

Choosing which Events to Dispatch
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    Ability to choose which events to dispatch was introduced in Symfony 5.2.

If you prefer to control which events are fired when performing each transition,
use the ``events_to_dispatch`` configuration option. This option does not apply
to :ref:`Guard events <workflow-usage-guard-events>`, which are always fired:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                blog_publishing:
                    # you can pass one or more event names
                    events_to_dispatch: ['workflow.leave', 'workflow.completed']

                    # pass an empty array to not dispatch any event
                    events_to_dispatch: []

                    # ...

    .. code-block:: xml

        <!-- config/packages/workflow.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:workflow name="blog_publishing">
                    <!-- you can pass one or more event names -->
                    <framework:event-to-dispatch>workflow.leave</framework:event-to-dispatch>
                    <framework:event-to-dispatch>workflow.completed</framework:event-to-dispatch>

                    <!-- pass an empty array to not dispatch any event -->
                    <framework:event-to-dispatch></framework:event-to-dispatch>

                    <!-- ... -->
                </framework:workflow>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/workflow.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...

            $blogPublishing = $framework->workflows()->workflows('blog_publishing');

            // ...
            // you can pass one or more event names
            $blogPublishing->eventsToDispatch([
                'workflow.leave',
                'workflow.completed',
            ]);

            // pass an empty array to not dispatch any event
            $blogPublishing->eventsToDispatch([]);

            // ...
        };

You can also disable a specific event from being fired when applying a transition::

    use App\Entity\BlogPost;
    use Symfony\Component\Workflow\Exception\LogicException;

    $post = new BlogPost();

    $workflow = $this->container->get('workflow.blog_publishing');

    try {
        $workflow->apply($post, 'to_review', [
            Workflow::DISABLE_ANNOUNCE_EVENT => true,
            Workflow::DISABLE_LEAVE_EVENT => true,
        ]);
    } catch (LogicException $exception) {
        // ...
    }

Disabling an event for a specific transition will take precedence over any
events specified in the workflow configuration. In the above example the
``workflow.leave`` event will not be fired, even if it has been specified as an
event to be dispatched for all transitions in the workflow configuration.

.. versionadded:: 5.1

    The ``Workflow::DISABLE_ANNOUNCE_EVENT`` constant was introduced in Symfony 5.1.

.. versionadded:: 5.2

    The constants for other events (as seen below) were introduced in Symfony 5.2.

    * ``Workflow::DISABLE_LEAVE_EVENT``
    * ``Workflow::DISABLE_TRANSITION_EVENT``
    * ``Workflow::DISABLE_ENTER_EVENT``
    * ``Workflow::DISABLE_ENTERED_EVENT``
    * ``Workflow::DISABLE_COMPLETED_EVENT``

Event Methods
~~~~~~~~~~~~~

Each workflow event is an instance of :class:`Symfony\\Component\\Workflow\\Event\\Event`.
This means that each event has access to the following information:

:method:`Symfony\\Component\\Workflow\\Event\\Event::getMarking`
    Returns the :class:`Symfony\\Component\\Workflow\\Marking` of the workflow.

:method:`Symfony\\Component\\Workflow\\Event\\Event::getSubject`
    Returns the object that dispatches the event.

:method:`Symfony\\Component\\Workflow\\Event\\Event::getTransition`
    Returns the :class:`Symfony\\Component\\Workflow\\Transition` that dispatches the event.

:method:`Symfony\\Component\\Workflow\\Event\\Event::getWorkflowName`
    Returns a string with the name of the workflow that triggered the event.

:method:`Symfony\\Component\\Workflow\\Event\\Event::getMetadata`
    Returns a metadata.

For Guard Events, there is an extended :class:`Symfony\\Component\\Workflow\\Event\\GuardEvent` class.
This class has these additional methods:

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::isBlocked`
    Returns if transition is blocked.

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::setBlocked`
    Sets the blocked value.

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::getTransitionBlockerList`
    Returns the event :class:`Symfony\\Component\\Workflow\\TransitionBlockerList`.
    See :ref:`blocking transitions <workflow-blocking-transitions>`.

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::addTransitionBlocker`
    Add a :class:`Symfony\\Component\\Workflow\\TransitionBlocker` instance.

.. _workflow-blocking-transitions:

Blocking Transitions
--------------------

The execution of the workflow can be controlled by calling custom logic to
decide if the current transition is blocked or allowed before applying it. This
feature is provided by "guards", which can be used in two ways.

First, you can listen to :ref:`the guard events <workflow-usage-guard-events>`.
Alternatively, you can define a ``guard`` configuration option for the
transition. The value of this option is any valid expression created with the
:doc:`ExpressionLanguage component </components/expression_language>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                blog_publishing:
                    # previous configuration
                    transitions:
                        to_review:
                            # the transition is allowed only if the current user has the ROLE_REVIEWER role.
                            guard: "is_granted('ROLE_REVIEWER')"
                            from: draft
                            to:   reviewed
                        publish:
                            # or "is_anonymous", "is_remember_me", "is_fully_authenticated", "is_granted", "is_valid"
                            guard: "is_authenticated"
                            from: reviewed
                            to:   published
                        reject:
                            # or any valid expression language with "subject" referring to the supported object
                            guard: "is_granted('ROLE_ADMIN') and subject.isRejectable()"
                            from: reviewed
                            to:   rejected

    .. code-block:: xml

        <!-- config/packages/workflow.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
            https://symfony.com/schema/dic/services/services-1.0.xsd
            http://symfony.com/schema/dic/symfony
            https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:workflow name="blog_publishing" type="workflow">

                    <!-- ... previous configuration -->

                    <framework:transition name="to_review">
                        <!-- the transition is allowed only if the current user has the ROLE_REVIEWER role. -->
                        <framework:guard>is_granted("ROLE_REVIEWER")</framework:guard>
                        <framework:from>draft</framework:from>
                        <framework:to>reviewed</framework:to>
                    </framework:transition>

                    <framework:transition name="publish">
                        <!-- or "is_anonymous", "is_remember_me", "is_fully_authenticated", "is_granted" -->
                        <framework:guard>is_authenticated</framework:guard>
                        <framework:from>reviewed</framework:from>
                        <framework:to>published</framework:to>
                    </framework:transition>

                    <framework:transition name="reject">
                        <!-- or any valid expression language with "subject" referring to the post -->
                        <framework:guard>is_granted("ROLE_ADMIN") and subject.isStatusReviewed()</framework:guard>
                        <framework:from>reviewed</framework:from>
                        <framework:to>rejected</framework:to>
                    </framework:transition>

                </framework:workflow>

            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/workflow.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $blogPublishing = $framework->workflows()->workflows('blog_publishing');
            // ... previous configuration

            $blogPublishing->transition()
                ->name('to_review')
                    // the transition is allowed only if the current user has the ROLE_REVIEWER role.
                    ->guard('is_granted("ROLE_REVIEWER")')
                    ->from(['draft'])
                    ->to(['reviewed']);

            $blogPublishing->transition()
                ->name('publish')
                    // or "is_anonymous", "is_remember_me", "is_fully_authenticated", "is_granted"
                    ->guard('is_authenticated')
                    ->from(['reviewed'])
                    ->to(['published']);

            $blogPublishing->transition()
                ->name('reject')
                    // or any valid expression language with "subject" referring to the post
                    ->guard('is_granted("ROLE_ADMIN") and subject.isStatusReviewed()')
                    ->from(['reviewed'])
                    ->to(['rejected']);
        };

You can also use transition blockers to block and return a user-friendly error
message when you stop a transition from happening.
In the example we get this message from the
:class:`Symfony\\Component\\Workflow\\Event\\Event`'s metadata, giving you a
central place to manage the text.

This example has been simplified; in production you may prefer to use the
:doc:`Translation </translation>` component to manage messages in one
place::

    // src/App/EventSubscriber/BlogPostPublishSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\GuardEvent;
    use Symfony\Component\Workflow\TransitionBlocker;

    class BlogPostPublishSubscriber implements EventSubscriberInterface
    {
        public function guardPublish(GuardEvent $event)
        {
            $eventTransition = $event->getTransition();
            $hourLimit = $event->getMetadata('hour_limit', $eventTransition);

            if (date('H') <= $hourLimit) {
                return;
            }

            // Block the transition "publish" if it is more than 8 PM
            // with the message for end user
            $explanation = $event->getMetadata('explanation', $eventTransition);
            $event->addTransitionBlocker(new TransitionBlocker($explanation , '0'));
        }

        public static function getSubscribedEvents()
        {
            return [
                'workflow.blog_publishing.guard.publish' => ['guardPublish'],
            ];
        }
    }

Creating Your Own Marking Store
-------------------------------

You may need to implement your own store to execute some additional logic
when the marking is updated. For example, you may have some specific needs
to store the marking on certain workflows. To do this, you need to implement
the
:class:`Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface`::

    namespace App\Workflow\MarkingStore;

    use Symfony\Component\Workflow\Marking;
    use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

    final class BlogPostMarkingStore implements MarkingStoreInterface
    {
        public function getMarking(BlogPost $subject): Marking
        {
            return new Marking([$subject->getCurrentPlace() => 1]);
        }

        public function setMarking(BlogPost $subject, Marking $marking): void
        {
            $marking = key($marking->getPlaces());
            $subject->setCurrentPlace($marking);
        }
    }

Once your marking store is implemented, you can configure your workflow to use
it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                blog_publishing:
                    # ...
                    marking_store:
                        service: 'App\Workflow\MarkingStore\BlogPostMarkingStore'

    .. code-block:: xml

        <!-- config/packages/workflow.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:workflow name="blog_publishing">
                    <!-- ... -->
                    <framework:marking-store service="App\Workflow\MarkingStore\BlogPostMarkingStore"/>
                </framework:workflow>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/workflow.php
        use App\Workflow\MarkingStore\ReflectionMarkingStore;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...

            $blogPublishing = $framework->workflows()->workflows('blog_publishing');
            // ...

            $blogPublishing->markingStore()
                ->service(BlogPostMarkingStore::class);
        };

Usage in Twig
-------------

Symfony defines several Twig functions to manage workflows and reduce the need
of domain logic in your templates:

``workflow_can()``
    Returns ``true`` if the given object can make the given transition.

``workflow_transitions()``
    Returns an array with all the transitions enabled for the given object.

``workflow_transition()``
    Returns a specific transition enabled for the given object and transition name.

``workflow_marked_places()``
    Returns an array with the place names of the given marking.

``workflow_has_marked_place()``
    Returns ``true`` if the marking of the given object has the given state.

``workflow_transition_blockers()``
    Returns :class:`Symfony\\Component\\Workflow\\TransitionBlockerList` for the given transition.

The following example shows these functions in action:

.. code-block:: html+twig

    <h3>Actions on Blog Post</h3>
    {% if workflow_can(post, 'publish') %}
        <a href="...">Publish</a>
    {% endif %}
    {% if workflow_can(post, 'to_review') %}
        <a href="...">Submit to review</a>
    {% endif %}
    {% if workflow_can(post, 'reject') %}
        <a href="...">Reject</a>
    {% endif %}

    {# Or loop through the enabled transitions #}
    {% for transition in workflow_transitions(post) %}
        <a href="...">{{ transition.name }}</a>
    {% else %}
        No actions available.
    {% endfor %}

    {# Check if the object is in some specific place #}
    {% if workflow_has_marked_place(post, 'reviewed') %}
        <p>This post is ready for review.</p>
    {% endif %}

    {# Check if some place has been marked on the object #}
    {% if 'reviewed' in workflow_marked_places(post) %}
        <span class="label">Reviewed</span>
    {% endif %}

    {# Loop through the transition blockers #}
    {% for blocker in workflow_transition_blockers(post, 'publish') %}
        <span class="error">{{ blocker.message }}</span>
    {% endfor %}

Storing Metadata
----------------

In case you need it, you can store arbitrary metadata in workflows, their
places, and their transitions using the ``metadata`` option. This metadata can
be only the title of the workflow or very complex objects:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                blog_publishing:
                    metadata:
                        title: 'Blog Publishing Workflow'
                    # ...
                    places:
                        draft:
                            metadata:
                                max_num_of_words: 500
                        # ...
                    transitions:
                        to_review:
                            from: draft
                            to:   review
                            metadata:
                                priority: 0.5
                        publish:
                            from: reviewed
                            to:   published
                            metadata:
                                hour_limit: 20
                                explanation: 'You can not publish after 8 PM.'

    .. code-block:: xml

        <!-- config/packages/workflow.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:workflow name="blog_publishing">
                    <framework:metadata>
                        <framework:title>Blog Publishing Workflow</framework:title>
                    </framework:metadata>
                    <!-- ... -->
                    <framework:place name="draft">
                        <framework:metadata>
                            <framework:max-num-of-words>500</framework:max-num-of-words>
                        </framework:metadata>
                    </framework:place>
                    <!-- ... -->
                    <framework:transition name="to_review">
                        <framework:from>draft</framework:from>
                        <framework:to>review</framework:to>
                        <framework:metadata>
                            <framework:priority>0.5</framework:priority>
                        </framework:metadata>
                    </framework:transition>
                    <framework:transition name="publish">
                        <framework:from>reviewed</framework:from>
                        <framework:to>published</framework:to>
                        <framework:metadata>
                            <framework:hour_limit>20</framework:hour_limit>
                            <framework:explanation>You can not publish after 8 PM.</framework:explanation>
                        </framework:metadata>
                    </framework:transition>
                </framework:workflow>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/workflow.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $blogPublishing = $framework->workflows()->workflows('blog_publishing');
            // ... previous configuration

            $blogPublishing->metadata([
                'title' => 'Blog Publishing Workflow'
            ]);

            // ...

            $blogPublishing->place()
                ->name('draft')
                ->metadata([
                    'max_num_of_words' => 500,
                ]);

            // ...

            $blogPublishing->transition()
                ->name('to_review')
                    ->from(['draft'])
                    ->to(['reviewed'])
                    ->metadata([
                        'priority' => 0.5,
                    ]);

            $blogPublishing->transition()
                ->name('publish')
                    ->from(['reviewed'])
                    ->to(['published'])
                    ->metadata([
                        'hour_limit' => 20,
                        'explanation' => 'You can not publish after 8 PM.',
                    ]);
        };

Then you can access this metadata in your controller as follows::

    // src/App/Controller/BlogPostController.php
    use App\Entity\BlogPost;
    use Symfony\Component\Workflow\WorkflowInterface;
    // ...

    public function myAction(WorkflowInterface $blogPublishingWorkflow, BlogPost $post)
    {
        $title = $blogPublishingWorkflow
            ->getMetadataStore()
            ->getWorkflowMetadata()['title'] ?? 'Default title'
        ;

        $maxNumOfWords = $blogPublishingWorkflow
            ->getMetadataStore()
            ->getPlaceMetadata('draft')['max_num_of_words'] ?? 500
        ;

        $aTransition = $blogPublishingWorkflow->getDefinition()->getTransitions()[0];
        $priority = $blogPublishingWorkflow
            ->getMetadataStore()
            ->getTransitionMetadata($aTransition)['priority'] ?? 0
        ;

        // ...
    }

There is a ``getMetadata()`` method that works with all kinds of metadata::

    // get "workflow metadata" passing the metadata key as argument
    $title = $workflow->getMetadataStore()->getMetadata('title');

    // get "place metadata" passing the metadata key as the first argument and the place name as the second argument
    $maxNumOfWords = $workflow->getMetadataStore()->getMetadata('max_num_of_words', 'draft');

    // get "transition metadata" passing the metadata key as the first argument and a Transition object as the second argument
    $priority = $workflow->getMetadataStore()->getMetadata('priority', $aTransition);

In a :ref:`flash message <flash-messages>` in your controller::

    // $transition = ...; (an instance of Transition)

    // $workflow is an injected Workflow instance
    $title = $workflow->getMetadataStore()->getMetadata('title', $transition);
    $this->addFlash('info', "You have successfully applied the transition with title: '$title'");

Metadata can also be accessed in a Listener, from the :class:`Symfony\\Component\\Workflow\\Event\\Event` object.

In Twig templates, metadata is available via the ``workflow_metadata()`` function:

.. code-block:: html+twig

    <h2>Metadata of Blog Post</h2>
    <p>
        <strong>Workflow</strong>:<br>
        <code>{{ workflow_metadata(blog_post, 'title') }}</code>
    </p>
    <p>
        <strong>Current place(s)</strong>
        <ul>
            {% for place in workflow_marked_places(blog_post) %}
                <li>
                    {{ place }}:
                    <code>{{ workflow_metadata(blog_post, 'max_num_of_words', place) ?: 'Unlimited'}}</code>
                </li>
            {% endfor %}
        </ul>
    </p>
    <p>
        <strong>Enabled transition(s)</strong>
        <ul>
            {% for transition in workflow_transitions(blog_post) %}
                <li>
                    {{ transition.name }}:
                    <code>{{ workflow_metadata(blog_post, 'priority', transition) ?: 0 }}</code>
                </li>
            {% endfor %}
        </ul>
    </p>
    <p>
        <strong>to_review Priority</strong>
        <ul>
            <li>
                to_review:
                <code>{{ workflow_metadata(blog_post, 'priority', workflow_transition(blog_post, 'to_review')) }}</code>
            </li>
        </ul>
    </p>

Learn more
----------

.. toctree::
   :maxdepth: 1

   /workflow/workflow-and-state-machine
   /workflow/dumping-workflows
