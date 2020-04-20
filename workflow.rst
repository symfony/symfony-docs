Workflow
========

Using the Workflow component inside a Symfony application requires to know first
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
step or stage in the process is called a *place*. You do also define *transitions*
to that describes the action to get from one place to another.

.. image:: /_images/components/workflow/states_transitions.png

A set of places and transitions creates a **definition**. A workflow needs
a ``Definition`` and a way to write the states to the objects (i.e. an
instance of a :class:`Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface`.)

Consider the following example for a blog post. A post can have these places:
``draft``, ``reviewed``, ``rejected``, ``published``. You can define the workflow
like this:

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

        $container->loadFromExtension('framework', [
            'workflows' => [
                'blog_publishing' => [
                    'type' => 'workflow', // or 'state_machine'
                    'audit_trail' => [
                        'enabled' => true
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
        ]);

.. tip::

    If you are creating your first workflows, consider using the ``workflow:dump``
    command to :doc:`debug the workflow contents </workflow/dumping-workflows>`.

The configured property will be used via it's implemented getter/setter methods by the marking store::

    class BlogPost
    {
        // the configured property must be declared
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
    }

.. note::

    The marking store type could be "multiple_state" or "single_state". A single
    state marking store does not support a model being on multiple places at the
    same time. This means a "workflow" must use a "multiple_state" marking store
    and a "state_machine" must use a "single_state" marking store. Symfony
    configures the marking store according to the "type" by default, so it's
    preferable to not configure it.

    A single state marking store uses a ``string`` to store the data. A multiple
    state marking store uses an ``array`` to store the data.

.. tip::

    The ``marking_store.type`` (the default value depends on the ``type`` value)
    and ``property`` (default value ``['marking']``) attributes of the
    ``marking_store`` option are optional. If omitted, their default values will
    be used. It's highly recommenced to use the default value.

.. tip::

    Setting the ``audit_trail.enabled`` option to ``true`` makes the application
    generate detailed log messages for the workflow activity.

With this workflow named ``blog_publishing``, you can get help to decide
what actions are allowed on a blog post::

    use App\Entity\BlogPost;
    use Symfony\Component\Workflow\Exception\LogicException;

    $post = new BlogPost();

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

Accessing the Workflow in a Class
---------------------------------

To access workflow inside a class, use dependency injection and inject the
registry in the constructor::

    use Symfony\Component\Workflow\Registry;

    class MyClass
    {

        private $workflowRegistry;

        public function __construct(Registry $workflowRegistry)
        {
            $this->workflowRegistry = $workflowRegistry;
        }

        public function toReview(BlogPost $post)
        {
            $workflow = $this->workflowRegistry->get($post);

            // Update the currentState on the post
            try {
                $workflow->apply($post, 'to_review');
            } catch (LogicException $exception) {
                // ...
            }
            // ...
        }
    }

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
    The subject is about to enter a new place. This event is triggered just
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

    You can avoid triggering those events by using the context::

        $workflow->apply($subject, $transitionName, [Workflow::DISABLE_ANNOUNCE_EVENT => true]);

    .. versionadded:: 5.1

        The ``Workflow::DISABLE_ANNOUNCE_EVENT`` constant was introduced in Symfony 5.1.

.. note::

    The leaving and entering events are triggered even for transitions that stay
    in same place.

Here is an example of how to enable logging for every time a "blog_publishing"
workflow leaves a place::

    use Psr\Log\LoggerInterface;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\Event;

    class WorkflowLogger implements EventSubscriberInterface
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

.. _workflow-usage-guard-events:

Guard Events
~~~~~~~~~~~~

There are a special kind of events called "Guard events". Their event listeners
are invoked every time a call to ``Workflow::can``, ``Workflow::apply`` or
``Workflow::getEnabledTransitions`` is executed. With the guard events you may
add custom logic to decide which transitions should be blocked or not. Here is a
list of the guard event names.

* ``workflow.guard``
* ``workflow.[workflow name].guard``
* ``workflow.[workflow name].guard.[transition name]``

This example stops any blog post being transitioned to "reviewed" if it is
missing a title::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\GuardEvent;

    class BlogPostReviewListener implements EventSubscriberInterface
    {
        public function guardReview(GuardEvent $event)
        {
            /** @var App\Entity\BlogPost $post */
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
This class has these additonal methods:

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

The execution of the workflow can be controlled by executing custom logic to
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
        use App\Entity\BlogPost;

        $container->loadFromExtension('framework', [
            'workflows' => [
                'blog_publishing' => [
                    // ... previous configuration

                    'transitions' => [
                        'to_review' => [
                            // the transition is allowed only if the current user has the ROLE_REVIEWER role.
                            'guard' => 'is_granted("ROLE_REVIEWER")',
                            'from' => 'draft',
                            'to' => 'reviewed',
                        ],
                        'publish' => [
                            // or "is_anonymous", "is_remember_me", "is_fully_authenticated", "is_granted"
                            'guard' => 'is_authenticated',
                            'from' => 'reviewed',
                            'to' => 'published',
                        ],
                        'reject' => [
                            // or any valid expression language with "subject" referring to the post
                            'guard' => 'is_granted("ROLE_ADMIN") and subject.isStatusReviewed()',
                            'from' => 'reviewed',
                            'to' => 'rejected',
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

    namespace App\Listener\Workflow\Task;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Workflow\Event\GuardEvent;
    use Symfony\Component\Workflow\TransitionBlocker;

    class BlogPostPublishListener implements EventSubscriberInterface
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
            $event->addTransitionBlocker(new TransitionBlocker($explanation , 0));
        }

        public static function getSubscribedEvents()
        {
            return [
                'workflow.blog_publishing.guard.publish' => ['guardPublish'],
            ];
        }
    }

Usage in Twig
-------------

Symfony defines several Twig functions to manage workflows and reduce the need
of domain logic in your templates:

``workflow_can()``
    Returns ``true`` if the given object can make the given transition.

``workflow_transitions()``
    Returns an array with all the transitions enabled for the given object.

``workflow_marked_places()``
    Returns an array with the place names of the given marking.

``workflow_has_marked_place()``
    Returns ``true`` if the marking of the given object has the given state.

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

Storing Metadata
----------------

In case you need it, you can store arbitrary metadata in workflows, their
places, and their transitions using the ``metadata`` option. This metadata can
be as simple as the title of the workflow or as complex as your own application
requires:

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
        $container->loadFromExtension('framework', [
            // ...
            'workflows' => [
                'blog_publishing' => [
                    'metadata' => [
                        'title' => 'Blog Publishing Workflow',
                    ],
                    // ...
                    'places' => [
                        'draft' => [
                            'metadata' => [
                                'max_num_of_words' => 500,
                            ],
                        ],
                        // ...
                    ],
                    'transitions' => [
                        'to_review' => [
                            'from' => 'draft',
                            'to' => 'review',
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
        ]);

Then you can access this metadata in your controller as follows::

    use App\Entity\BlogPost;
    use Symfony\Component\Workflow\Registry;

    public function myController(Registry $registry, BlogPost $post)
    {
        $workflow = $registry->get($post);

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
    }

There is a ``getMetadata()`` method that works with all kinds of metadata::

    // pass no arguments to getMetadata() to get "workflow metadata"
    $title = $workflow->getMetadataStore()->getMetadata()['title'];

    // pass a string (the place name) to getMetadata() to get "place metadata"
    $maxNumOfWords = $workflow->getMetadataStore()->getMetadata('draft')['max_num_of_words'];

    // pass a Transition object to getMetadata() to get "transition metadata"
    $priority = $workflow->getMetadataStore()->getMetadata($aTransition)['priority'];

In a :ref:`flash message <flash-messages>` in your controller::

    // $transition = ...; (an instance of Transition)

    // $workflow is a Workflow instance retrieved from the Registry (see above)
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
                    <code>{{ workflow_metadata(blog_post, 'priority', transition) ?: '0' }}</code>
                </li>
            {% endfor %}
        </ul>
    </p>

Learn more
----------

.. toctree::
   :maxdepth: 1

   /workflow/workflow-and-state-machine
   /workflow/dumping-workflows
