Workflow
========

Using the Workflow component inside a Symfony application requires first knowing
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

To see all configuration options, run this command if you are using the
component inside a Symfony project:

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
                    places:          # defining places manually is optional
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

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Entity\BlogPost;

        return App::config([
            'framework' => [
                'workflows' => [
                    'blog_publishing' => [
                        'type' => 'workflow', // or 'state_machine'
                        'audit_trail' => [
                            'enabled' => true,
                        ],
                        'marking_store' => [
                            'type' => 'method',
                            'property' => 'currentPlace',
                        ],
                        'supports' => [BlogPost::class],
                        'initial_marking' => 'draft',
                        'places' => [
                            'draft',
                            'reviewed',
                            'rejected',
                            'published',
                        ],
                        'transitions' => [
                            'to_review' => [
                                'from' => 'draft',
                                'to' => 'reviewed',
                            ],
                            'publish' => [
                                'from' => 'reviewed',
                                'to' => 'published',
                            ],
                            'reject' => [
                                'from' => 'reviewed',
                                'to' => 'rejected',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

.. tip::

    If you are creating your first workflows, consider using the ``workflow:dump``
    command to :doc:`debug the workflow contents </workflow/dumping-workflows>`.

.. tip::

    You can use PHP constants in YAML files via the ``!php/const `` notation.
    E.g. you can use ``!php/const App\Entity\BlogPost::STATE_DRAFT`` instead of
    ``'draft'`` or ``!php/const App\Entity\BlogPost::TRANSITION_TO_REVIEW``
    instead of ``'to_review'``.

.. tip::

    You can omit the ``places`` option if your transitions define all the places
    that are used in the workflow. Symfony will automatically extract the places
    from the transitions.

The configured property will be used via its implemented getter/setter methods by the marking store::

    // src/Entity/BlogPost.php
    namespace App\Entity;

    class BlogPost
    {
        // the configured marking store property must be declared
        private string $currentPlace;
        private string $title;
        private string $content;

        // getter/setter methods must exist for property access by the marking store
        public function getCurrentPlace(): string
        {
            return $this->currentPlace;
        }

        public function setCurrentPlace(string $currentPlace, array $context = []): void
        {
            $this->currentPlace = $currentPlace;
        }

        // you don't need to set the initial marking in the constructor or any other method;
        // this is configured in the workflow with the 'initial_marking' option
    }

It is also possible to use public properties for the marking store. The above
class would become the following::

    // src/Entity/BlogPost.php
    namespace App\Entity;

    class BlogPost
    {
        // the configured marking store property must be declared
        public string $currentPlace;
        public string $title;
        public string $content;
    }

When using public properties, context is not supported. In order to support it,
you must declare a setter to write your property::

    // src/Entity/BlogPost.php
    namespace App\Entity;

    class BlogPost
    {
        public string $currentPlace;
        // ...

        public function setCurrentPlace(string $currentPlace, array $context = []): void
        {
            // assign the property and do something with the context
        }
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

.. _using-enums-as-workflow-places:

Using Enums in Workflows
~~~~~~~~~~~~~~~~~~~~~~~~

Using Enums in Workflow Definitions
...................................

When using a state machine, you can use PHP backend enums as places in your
workflows. First, define your enum with backed values::

    // src/Enumeration/BlogPostStatus.php
    namespace App\Enumeration;

    enum BlogPostStatus: string
    {
        case Draft = 'draft';
        case Reviewed = 'reviewed';
        case Published = 'published';
        case Rejected = 'rejected';
    }

Then configure the workflow using the enum cases as places, initial marking,
and transitions:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                blog_publishing:
                    type: 'workflow'
                    marking_store:
                        type: 'method'
                        property: 'status'
                    supports:
                        - App\Entity\BlogPost
                    initial_marking: !php/enum App\Enumeration\BlogPostStatus::Draft
                    places: !php/enum App\Enumeration\BlogPostStatus
                    transitions:
                        to_review:
                            from: !php/enum App\Enumeration\BlogPostStatus::Draft
                            to:   !php/enum App\Enumeration\BlogPostStatus::Reviewed
                        publish:
                            from: !php/enum App\Enumeration\BlogPostStatus::Reviewed
                            to:   !php/enum App\Enumeration\BlogPostStatus::Published
                        reject:
                            from: !php/enum App\Enumeration\BlogPostStatus::Reviewed
                            to:   !php/enum App\Enumeration\BlogPostStatus::Rejected

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Entity\BlogPost;
        use App\Enumeration\BlogPostStatus;

        return App::config([
            'framework' => [
                'workflows' => [
                    'blog_publishing' => [
                        'type' => 'workflow',
                        'marking_store' => [
                            'type' => 'method',
                            'property' => 'status',
                        ],
                        'supports' => [BlogPost::class],
                        'initial_marking' => BlogPostStatus::Draft,
                        'places' => BlogPostStatus::cases(),
                        'transitions' => [
                            'to_review' => [
                                'from' => BlogPostStatus::Draft,
                                'to' => BlogPostStatus::Reviewed,
                            ],
                            'publish' => [
                                'from' => BlogPostStatus::Reviewed,
                                'to' => BlogPostStatus::Published,
                            ],
                            'reject' => [
                                'from' => BlogPostStatus::Reviewed,
                                'to' => BlogPostStatus::Rejected,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The component will now transparently cast the enum to its backing value
when needed and vice-versa when working with your objects::

    // src/Entity/BlogPost.php
    namespace App\Entity;

    class BlogPost
    {
        private BlogPostStatus $status;

        public function getStatus(): BlogPostStatus
        {
            return $this->status;
        }

        public function setStatus(BlogPostStatus $status): void
        {
            $this->status = $status;
        }
    }

.. tip::

    You can also use `glob patterns`_ of PHP constants and enums to list the places:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/workflow.yaml
            framework:
                workflows:
                    my_workflow_name:
                        # with constants:
                        places: 'App\Workflow\MyWorkflow::PLACE_*'

                        # with enums:
                        places: !php/enum App\Workflow\Places

                        # ...

        .. code-block:: php

            // config/packages/workflow.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\Enumeration\BlogPostStatus;

            return App::config([
                'framework' => [
                    'workflows' => [
                        'my_workflow_name' => [
                            // with constants:
                            'places' => 'App\Workflow\MyWorkflow::PLACE_*',
                            // with enums:
                            'places' => BlogPostStatus::cases(),
                        ],
                    ],
                ],
            ]);

Using Enums in Marking Stores
.............................

When using a single state marking store, you can type-hint the property with a
``BackedEnum`` instead of a string. The ``MethodMarkingStore`` will automatically
convert between the enum and its backing value::

    // src/Entity/Status.php
    namespace App\Entity;

    enum Status: string
    {
        case Draft = 'draft';
        case Reviewed = 'reviewed';
        case Published = 'published';
    }

    // src/Entity/BlogPost.php
    namespace App\Entity;

    class BlogPost
    {
        public ?Status $currentPlace = null;

        public function getCurrentPlace(): ?Status
        {
            return $this->currentPlace;
        }

        public function setCurrentPlace(Status $currentPlace, array $context = []): void
        {
            $this->currentPlace = $currentPlace;
        }
    }

Using Weighted Transitions
~~~~~~~~~~~~~~~~~~~~~~~~~~

A key feature of workflows (as opposed to state machines) is that an object can
be in multiple places simultaneously. For example, when building a product, you
might assemble several components in parallel. However, in the previous example,
each place could only record whether the object was there or not, like a binary flag.

**Weighted transitions** introduce multiplicity: a place can now track how many
times an object is in that place. Technically, weighted transitions allow you to
define transitions where multiple tokens (instances) are consumed from or produced
to places. This is useful for modeling complex workflows such as manufacturing
processes, resource allocation, or any scenario where multiple instances of something
need to be produced or consumed.

For example, imagine a table-making workflow where you need to create 4 legs, 1 top,
and track the process with a stopwatch. You can use weighted transitions to model this:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                make_table:
                    type: 'workflow'
                    marking_store:
                        type: 'method'
                        property: 'marking'
                    supports:
                        - App\Entity\TableProject
                    initial_marking: init
                    places:
                        - init
                        - prepare_leg
                        - prepare_top
                        - stopwatch_running
                        - leg_created
                        - top_created
                        - finished
                    transitions:
                        start:
                            from: init
                            to:
                                - place: prepare_leg
                                  weight: 4
                                - place: prepare_top
                                  weight: 1
                                - place: stopwatch_running
                                  weight: 1
                        build_leg:
                            from: prepare_leg
                            to: leg_created
                        build_top:
                            from: prepare_top
                            to: top_created
                        join:
                            from:
                                - place: leg_created
                                  weight: 4
                                - top_created  # weight defaults to 1
                                - stopwatch_running
                            to: finished

    .. code-block:: php

        // config/packages/workflow.php
        use App\Entity\TableProject;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $makeTable = $framework->workflows()->workflows('make_table');
            $makeTable
                ->type('workflow')
                ->supports([TableProject::class])
                ->initialMarking(['init']);

            $makeTable->markingStore()
                ->type('method')
                ->property('marking');

            $makeTable->place()->name('init');
            $makeTable->place()->name('prepare_leg');
            $makeTable->place()->name('prepare_top');
            $makeTable->place()->name('stopwatch_running');
            $makeTable->place()->name('leg_created');
            $makeTable->place()->name('top_created');
            $makeTable->place()->name('finished');

            $makeTable->transition()
                ->name('start')
                    ->from(['init'])
                    ->to([
                        ['place' => 'prepare_leg', 'weight' => 4],
                        ['place' => 'prepare_top', 'weight' => 1],
                        ['place' => 'stopwatch_running', 'weight' => 1],
                    ]);

            $makeTable->transition()
                ->name('build_leg')
                    ->from(['prepare_leg'])
                    ->to(['leg_created']);

            $makeTable->transition()
                ->name('build_top')
                    ->from(['prepare_top'])
                    ->to(['top_created']);

            $makeTable->transition()
                ->name('join')
                    ->from([
                        ['place' => 'leg_created', 'weight' => 4],
                        'top_created',  // weight defaults to 1
                        'stopwatch_running',
                    ])
                    ->to(['finished']);
        };

In this example, when the ``start`` transition is applied, it creates 4 tokens in
the ``prepare_leg`` place, 1 token in ``prepare_top``, and 1 token in
``stopwatch_running``. Then, the ``build_leg`` transition must be applied 4 times
(once for each token), and the ``build_top`` transition once. Finally, the ``join``
transition can only be applied when all 4 legs are created, the top is created,
and the stopwatch is still running.

Weighted transitions can also be defined programmatically using the
:class:`Symfony\\Component\\Workflow\\Arc` class::

    use Symfony\Component\Workflow\Arc;
    use Symfony\Component\Workflow\Definition;
    use Symfony\Component\Workflow\Transition;
    use Symfony\Component\Workflow\Workflow;

    $definition = new Definition(
        ['init', 'prepare_leg', 'prepare_top', 'stopwatch_running', 'leg_created', 'top_created', 'finished'],
        [
            new Transition('start', 'init', [
                new Arc('prepare_leg', 4),
                new Arc('prepare_top', 1),
                'stopwatch_running',  // defaults to weight 1
            ]),
            new Transition('build_leg', 'prepare_leg', 'leg_created'),
            new Transition('build_top', 'prepare_top', 'top_created'),
            new Transition('join', [
                new Arc('leg_created', 4),
                'top_created',
                'stopwatch_running',
            ], 'finished'),
        ]
    );

    $workflow = new Workflow($definition);
    $workflow->apply($subject, 'start');

    // Build each leg (4 times)
    $workflow->apply($subject, 'build_leg');
    $workflow->apply($subject, 'build_leg');
    $workflow->apply($subject, 'build_leg');
    $workflow->apply($subject, 'build_leg');

    // Build the top
    $workflow->apply($subject, 'build_top');

    // Now we can join all parts
    $workflow->apply($subject, 'join');

The ``Arc`` class takes two parameters: the place name and the weight (which must be
greater than or equal to 1). When a place is specified as a simple string instead of
an ``Arc`` object, it defaults to a weight of 1.

Using a Multiple State Marking Store
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you are creating a :doc:`workflow </workflow/workflow-and-state-machine>`,
your marking store may need to contain multiple places at the same time. That's why,
if you are using Doctrine, the matching column definition should use the type ``json``::

    // src/Entity/BlogPost.php
    namespace App\Entity;

    use Doctrine\DBAL\Types\Types;
    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    class BlogPost
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        private int $id;

        #[ORM\Column(type: Types::JSON)]
        private array $currentPlaces;

        // ...
    }

.. warning::

    You should not use the type ``simple_array`` for your marking store. Inside
    a multiple state marking store, places are stored as keys with a value of one,
    such as ``['draft' => 1]``. If the marking store contains only one place,
    this Doctrine type will store its value only as a string, resulting in the
    loss of the object's current place.

Accessing the Workflow in a Class
---------------------------------

Symfony creates a service for each workflow you define. Use the ``#[Target]``
attribute to inject a specific workflow in any service or controller. Symfony
creates a target with the same name as each workflow.

For example, to inject the ``blog_publishing`` workflow defined earlier::

    use App\Entity\BlogPost;
    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Component\Workflow\WorkflowInterface;

    class MyClass
    {
        public function __construct(
            #[Target('blog_publishing')] private WorkflowInterface $workflow,
        ) {
        }

        public function toReview(BlogPost $post): void
        {
            try {
                // update the currentState on the post
                $this->workflow->apply($post, 'to_review');
            } catch (LogicException $exception) {
                // ...
            }
            // ...
        }
    }

.. tip::

    If it is a state machine type, use the state machine name as the target
    (e.g. ``#[Target('my_state_machine')]``).

To get the enabled transition of a Workflow, you can use the
:method:`Symfony\\Component\\Workflow\\WorkflowInterface::getEnabledTransition`
method.

.. tip::

    If you want to retrieve all workflows, for documentation purposes for example,
    you can :doc:`inject all services </service_container/service_subscribers_locators>`
    with the following tag:

    * ``workflow``: all workflows and all state machines;
    * ``workflow.workflow``: all workflows;
    * ``workflow.state_machine``: all state machines.

    Note that workflow metadata are attached to tags under the ``metadata`` key,
    giving you more context and information about the workflow at your disposal.
    Learn more about :ref:`tag attributes <tags_additional-attributes>` and
    :ref:`storing workflow metadata <workflow_storing-metadata>`.

.. tip::

    You can find the list of available workflow services with the
    ``php bin/console debug:autowiring workflow`` command.

Injecting Multiple Workflows
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use the :ref:`AutowireLocator <service-locator_autowire-locator>` attribute to
lazy-load all workflows and get the one you need::

    use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
    use Symfony\Component\DependencyInjection\ServiceLocator;

    class MyClass
    {
        public function __construct(
            // 'workflow' is the service tag name and injects both workflows and state machines;
            // 'name' tells Symfony to index services using that tag property
            #[AutowireLocator('workflow', 'name')]
            private ServiceLocator $workflows,
        ) {
        }

        public function someMethod(): void
        {
            // if you use the 'name' tag property to index services (see constructor above),
            // you can get workflows by their name; otherwise, you must use the full
            // service name with the 'workflow.' prefix (e.g. 'workflow.user_registration')
            $workflow = $this->workflows->get('user_registration');

            // ...
        }
    }

.. tip::

    You can also inject only workflows or only state machines::

        public function __construct(
            #[AutowireLocator('workflow.workflow', 'name')]
            private ServiceLocator $workflows,
            #[AutowireLocator('workflow.state_machine', 'name')]
            private ServiceLocator $stateMachines,
        ) {
        }

.. _workflow_using-events:

Using Events
------------

To make your workflows more flexible, you can construct the ``Workflow``
object with an ``EventDispatcher``. You can now create event listeners to
block transitions (i.e. depending on the data in the blog post) and perform
additional actions when a workflow operation happens (e.g. sending
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
    The subject has entered the places and the marking is updated.

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
    Triggered for each transition that is now accessible for the subject.

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

.. note::

    The leaving and entering events are triggered even for transitions that stay
    in the same place.

.. note::

    If you initialize the marking by calling ``$workflow->getMarking($object);``,
    then the ``workflow.[workflow_name].entered.[initial_place_name]`` event will
    be called with the default context (``Workflow::DEFAULT_INITIAL_CONTEXT``).

Here is an example of how to enable logging for every time a "blog_publishing"
workflow leaves a place::

    // src/EventSubscriber/WorkflowLoggerSubscriber.php
    namespace App\EventSubscriber;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\Event;
    use Symfony\Component\Workflow\Event\LeaveEvent;

    class WorkflowLoggerSubscriber implements EventSubscriberInterface
    {
        public function __construct(
            private LoggerInterface $logger,
        ) {
        }

        public function onLeave(Event $event): void
        {
            $this->logger->alert(sprintf(
                'Blog post (id: "%s") performed transition "%s" from "%s" to "%s"',
                $event->getSubject()->getId(),
                $event->getTransition()->getName(),
                implode(', ', array_keys($event->getMarking()->getPlaces())),
                implode(', ', $event->getTransition()->getTos())
            ));
        }

        public static function getSubscribedEvents(): array
        {
            return [
                LeaveEvent::getName('blog_publishing') => 'onLeave',
                // if you prefer, you can write the event name manually like this:
                // 'workflow.blog_publishing.leave' => 'onLeave',
            ];
        }
    }

.. tip::

    All built-in workflow events define the ``getName(?string $workflowName, ?string $transitionOrPlaceName)``
    method to build the full event name without having to deal with strings.
    You can also use this method in your custom events via the
    :class:`Symfony\\Component\\Workflow\\Event\\EventNameTrait`.

If some listeners update the context during a transition, you can retrieve
it via the marking::

    $marking = $workflow->apply($post, 'to_review');

    // contains the new value
    $marking->getContext();

It is also possible to listen to these events by declaring event listeners
with the following attributes:

* :class:`Symfony\\Component\\Workflow\\Attribute\\AsAnnounceListener`
* :class:`Symfony\\Component\\Workflow\\Attribute\\AsCompletedListener`
* :class:`Symfony\\Component\\Workflow\\Attribute\\AsEnterListener`
* :class:`Symfony\\Component\\Workflow\\Attribute\\AsEnteredListener`
* :class:`Symfony\\Component\\Workflow\\Attribute\\AsGuardListener`
* :class:`Symfony\\Component\\Workflow\\Attribute\\AsLeaveListener`
* :class:`Symfony\\Component\\Workflow\\Attribute\\AsTransitionListener`

These attributes do work like the
:class:`Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener`
attributes::

    class ArticleWorkflowEventListener
    {
        #[AsTransitionListener(workflow: 'my-workflow', transition: 'published')]
        public function onPublishedTransition(TransitionEvent $event): void
        {
            // ...
        }

        // ...
    }

You may refer to the documentation about
:ref:`defining event listeners with PHP attributes <event-dispatcher_event-listener-attributes>`
for further use.

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

    // src/EventSubscriber/BlogPostReviewSubscriber.php
    namespace App\EventSubscriber;

    use App\Entity\BlogPost;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\GuardEvent;

    class BlogPostReviewSubscriber implements EventSubscriberInterface
    {
        public function guardReview(GuardEvent $event): void
        {
            /** @var BlogPost $post */
            $post = $event->getSubject();
            $title = $post->title;

            if (empty($title)) {
                $event->setBlocked(true, 'This blog post cannot be marked as reviewed because it has no title.');
            }
        }

        public static function getSubscribedEvents(): array
        {
            return [
                'workflow.blog_publishing.guard.to_review' => ['guardReview'],
            ];
        }
    }

.. _workflow-chosing-events-to-dispatch:

Choosing which Events to Dispatch
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'workflows' => [
                    'blog_publishing' => [
                        // you can pass one or more event names
                        'events_to_dispatch' => ['workflow.leave', 'workflow.completed'],
                        // pass an empty array to not dispatch any event
                        'events_to_dispatch' => [],
                        // ...
                    ],
                ],
            ],
        ]);

.. versionadded:: 8.2

    The ``!`` prefix syntax for ``events_to_dispatch`` was introduced in Symfony 8.2.

By default, ``events_to_dispatch`` uses an allow-list, so you must list every
event to dispatch. Prefix one or more event names with ``!`` to use a deny-list
instead, dispatching all events except those listed:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                blog_publishing:
                    # dispatch all events except 'workflow.announce'
                    events_to_dispatch: ['!workflow.announce']

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'workflows' => [
                    'blog_publishing' => [
                        // dispatch all events except 'workflow.announce'
                        'events_to_dispatch' => ['!workflow.announce'],
                    ],
                ],
            ],
        ]);

Unlike an allow-list, a deny-list is expanded into an allow-list internally when
the workflow is built, so any new events added in future Symfony versions will be
dispatched.

.. warning::

    The block-list does not apply to :ref:`Guard events <workflow-usage-guard-events>`,
    so ``'!workflow.guard'`` is rejected.

You can also disable a specific event from being fired when applying a transition::

    use App\Entity\BlogPost;
    use Symfony\Component\Workflow\Exception\LogicException;

    $post = new BlogPost();

    try {
        $blogPublishingWorkflow->apply($post, 'to_review', [
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

These are all the available constants:

    * ``Workflow::DISABLE_LEAVE_EVENT``
    * ``Workflow::DISABLE_TRANSITION_EVENT``
    * ``Workflow::DISABLE_ENTER_EVENT``
    * ``Workflow::DISABLE_ENTERED_EVENT``
    * ``Workflow::DISABLE_COMPLETED_EVENT``
    * ``Workflow::DISABLE_ANNOUNCE_EVENT``

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
    Returns metadata.

For Guard Events, there is an extended :class:`Symfony\\Component\\Workflow\\Event\\GuardEvent` class.
This class has these additional methods:

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::isBlocked`
    Returns whether the transition is blocked.

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::setBlocked`
    Sets the blocked value.

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::getTransitionBlockerList`
    Returns the event :class:`Symfony\\Component\\Workflow\\TransitionBlockerList`.
    See :ref:`blocking transitions <workflow-blocking-transitions>`.

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::addTransitionBlocker`
    Adds a :class:`Symfony\\Component\\Workflow\\TransitionBlocker` instance.

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
                            # or "is_remember_me", "is_fully_authenticated", "is_granted", "is_valid"
                            guard: "is_authenticated"
                            from: reviewed
                            to:   published
                        reject:
                            # or any valid expression language with "subject" referring to the supported object
                            guard: "is_granted('ROLE_ADMIN') and subject.isRejectable()"
                            from: reviewed
                            to:   rejected

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'workflows' => [
                    'blog_publishing' => [
                        'transitions' => [
                            'to_review' => [
                                'guard' => 'is_granted("ROLE_REVIEWER")',
                                'from' => ['draft'],
                                'to' => ['reviewed'],
                            ],
                            'publish' => [
                                // or "is_remember_me", "is_fully_authenticated", "is_granted"
                                'guard' => 'is_authenticated',
                                'from' => ['reviewed'],
                                'to' => ['published'],
                            ],
                            'reject' => [
                                // or any valid expression language with "subject" referring to the post
                                'guard' => 'is_granted("ROLE_ADMIN") and subject.isStatusReviewed()',
                                'from' => ['reviewed'],
                                'to' => ['rejected'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

You can also use transition blockers to block and return a user-friendly error
message when you stop a transition from happening.
In the example we get this message from the
:class:`Symfony\\Component\\Workflow\\Event\\Event`'s metadata, giving you a
central place to manage the text.

This example has been simplified; in production you may prefer to use the
:doc:`Translation </translation>` component to manage messages in one
place::

    // src/EventSubscriber/BlogPostPublishSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\GuardEvent;
    use Symfony\Component\Workflow\TransitionBlocker;

    class BlogPostPublishSubscriber implements EventSubscriberInterface
    {
        public function guardPublish(GuardEvent $event): void
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

        public static function getSubscribedEvents(): array
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
        /**
         * @param BlogPost $subject
         */
        public function getMarking(object $subject): Marking
        {
            return new Marking([$subject->getCurrentPlace() => 1]);
        }

        /**
         * @param BlogPost $subject
         */
        public function setMarking(object $subject, Marking $marking, array $context = []): void
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

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Workflow\MarkingStore\BlogPostMarkingStore;

        return App::config([
            'framework' => [
                'workflows' => [
                    'blog_publishing' => [
                        'marking_store' => [
                            'service' => BlogPostMarkingStore::class,
                        ],
                    ],
                ],
            ],
        ]);

Usage in Twig
-------------

Symfony defines several Twig functions to manage workflows and reduce the need
for domain logic in your templates:

``workflow_can(object $subject, string $transitionName, ?string $name = null)``
    Returns ``true`` if the given object can make the given transition.

``workflow_transitions(object $subject, ?string $name = null)``
    Returns an array with all the transitions enabled for the given object.

``workflow_transition(object $subject, string $transition, ?string $name = null)``
    Returns a specific transition enabled for the given object and transition name.

``workflow_marked_places(object $subject, bool $placesNameOnly = true, ?string $name = null)``
    Returns an array with the place names of the given marking.

``workflow_has_marked_place(object $subject, string $placeName, ?string $name = null)``
    Returns ``true`` if the marking of the given object has the given state.

``workflow_transition_blockers(object $subject, string $transitionName, ?string $name = null)``
    Returns :class:`Symfony\\Component\\Workflow\\TransitionBlockerList` for the given transition.

All workflow functions accept an optional ``name`` argument (the workflow name)
as the last parameter. This is only required when the object is associated with
multiple workflows:

.. code-block:: html+twig

    <h3>Actions on Blog Post</h3>
    {% if workflow_can(post, 'publish', 'blog_publishing') %}
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

.. _workflow_storing-metadata:

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

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'workflows' => [
                    'blog_publishing' => [
                        // ... previous configuration
                        'metadata' => [
                            'title' => 'Blog Publishing Workflow'
                        ],
                        // ...
                        'places' => [
                            [
                                'name' => 'draft',
                                'metadata' => [
                                    'max_num_of_words' => 500,
                                ],
                            ],
                            // ...
                        ],
                        'transitions' => [
                            'to_review' => [
                                'from' => 'draft',
                                'to' => 'reviewed',
                                'metadata' => [
                                    'priority' => 0.5,
                                ],
                            ],
                            'publish' => [
                                'from' => 'reviewed',
                                'to' => 'published',
                                'metadata' => [
                                    'hour_limit' => 20,
                                    'explanation' => 'You can not publish after 8 PM.',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

Then you can access this metadata in your controller as follows::

    // src/App/Controller/BlogPostController.php
    use App\Entity\BlogPost;
    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Component\Workflow\WorkflowInterface;
    // ...

    public function myAction(
        #[Target('blog_publishing')] WorkflowInterface $workflow,
        BlogPost $post,
    ): Response
    {
        $title = $workflow
            ->getMetadataStore()
            ->getWorkflowMetadata()['title'] ?? 'Default title'
        ;

        $maxNumOfWords = $workflow
            ->getMetadataStore()
            ->getPlaceMetadata('draft')['max_num_of_words'] ?? 500
        ;

        $aTransition = $workflow->getDefinition()->getTransitions()[0];
        $priority = $workflow
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

Validating Workflow Definitions
-------------------------------

Symfony allows you to validate workflow definitions using your own custom logic.
To do so, create a class that implements the
:class:`Symfony\\Component\\Workflow\\Validator\\DefinitionValidatorInterface`::

    namespace App\Workflow\Validator;

    use Symfony\Component\Workflow\Definition;
    use Symfony\Component\Workflow\Exception\InvalidDefinitionException;
    use Symfony\Component\Workflow\Validator\DefinitionValidatorInterface;

    final class BlogPublishingValidator implements DefinitionValidatorInterface
    {
        public function validate(Definition $definition, string $name): void
        {
            if (!$definition->getMetadataStore()->getMetadata('title')) {
                throw new InvalidDefinitionException(sprintf('The workflow metadata title is missing in Workflow "%s".', $name));
            }

            // ...
        }
    }

After implementing your validator, configure your workflow to use it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                blog_publishing:
                    # ...

                    definition_validators:
                        - App\Workflow\Validator\BlogPublishingValidator

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Workflow\Validator\BlogPublishingValidator;

        return App::config([
            'framework' => [
                'workflows' => [
                    'blog_publishing' => [
                        // ...
                        'definition_validators' => [
                            BlogPublishingValidator::class,
                        ],
                    ],
                ],
            ],
        ]);

The ``BlogPublishingValidator`` will be executed during container compilation
to validate the workflow definition.

Learn more
----------

.. toctree::
   :maxdepth: 1

   /workflow/workflow-and-state-machine
   /workflow/dumping-workflows

.. _`glob patterns`: https://php.net/glob
