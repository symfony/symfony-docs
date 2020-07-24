Workflow
========

Using the Workflow component inside a Symfony application requires to know first
some basic theory and concepts about workflows and state machines.
:doc:`Read this article </workflow/workflow-and-state-machine>` for a quick overview.

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

        # app/config/config.yml
        framework:
            workflows:
                blog_publishing:
                    type: 'workflow' # or 'state_machine'
                    audit_trail:
                        enabled: true
                    marking_store:
                        type: 'multiple_state' # or 'single_state'
                        arguments:
                            - 'currentPlace'
                    supports:
                        - AppBundle\Entity\BlogPost
                    initial_place: draft
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

        <!-- app/config/config.xml -->
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

                    <!-- or type="multiple_state" -->
                    <framework:marking-store type="single_state">
                        <framework:argument>currentPlace</framework:argument>
                    </framework:marking-store>

                    <framework:support>AppBundle\Entity\BlogPost</framework:support>

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

        // app/config/config.php
        use AppBundle\Entity\BlogPost;

        $container->loadFromExtension('framework', [
            'workflows' => [
                'blog_publishing' => [
                    'type' => 'workflow', // or 'state_machine'
                    'audit_trail' => [
                        'enabled' => true
                    ],
                    'marking_store' => [
                        'type' => 'multiple_state', // or 'single_state'
                        'arguments' => ['currentPlace']
                    ],
                    'supports' => [BlogPost::class],
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

As configured, the following property is used by the marking store::

    class BlogPost
    {
        // This property is used by the marking store
        public $currentPlace;
        public $title;
        public $content;
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
    and ``arguments`` (default value ``['marking']``) attributes of the
    ``marking_store`` option are optional. If omitted, their default values will
    be used. It's highly recommended to use the default value.

.. tip::

    Setting the ``audit_trail.enabled`` option to ``true`` makes the application
    generate detailed log messages for the workflow activity.

    .. versionadded:: 3.3

        The ``audit_trail`` option was introduced in Symfony 3.3.

With this workflow named ``blog_publishing``, you can get help to decide
what actions are allowed on a blog post::

    $post = new AppBundle\Entity\BlogPost();

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
are invoked every time a call to ``Workflow::can()``, ``Workflow::apply()`` or
``Workflow::getEnabledTransitions()`` is executed. With the guard events you may
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
            /** @var AppBundle\Entity\BlogPost $post */
            $post = $event->getSubject();
            $title = $post->title;

            if (empty($title)) {
                // Block the transition "to_review" if the post has no title
                $event->setBlocked(true);
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                'workflow.blog_publishing.guard.to_review' => ['guardReview'],
            ];
        }
    }

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

    .. versionadded:: 3.3

        The ``getWorkflowName()`` method was introduced in Symfony 3.3.

For Guard Events, there is an extended :class:`Symfony\\Component\\Workflow\\Event\\GuardEvent` class.
This class has two more methods:

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::isBlocked`
    Returns if transition is blocked.

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::setBlocked`
    Sets the blocked value.

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
                    # ... previous configuration

                    transitions:
                        to_review:
                            # the transition is allowed only if the current user has the ROLE_REVIEWER role.
                            guard: "is_granted('ROLE_REVIEWER')"
                            from: draft
                            to:   reviewed
                        publish:
                            # or "is_anonymous", "is_remember_me", "is_fully_authenticated", "is_granted"
                            guard: "is_authenticated"
                            from: reviewed
                            to:   published
                        reject:
                            # or any valid expression language with "subject" referring to the post
                            guard: "has_role('ROLE_ADMIN') and subject.isStatusReviewed()"
                            from: reviewed
                            to:   rejected

    .. code-block:: xml

        <!-- app/config/config.xml -->
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
                        <framework:guard>has_role("ROLE_ADMIN") and subject.isStatusReviewed()</framework:guard>
                        <framework:from>reviewed</framework:from>
                        <framework:to>rejected</framework:to>
                    </framework:transition>

                </framework:workflow>

            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        use AppBundle\Entity\BlogPost;

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
                            'guard' => 'has_role("ROLE_ADMIN") and subject.isStatusReviewed()',
                            'from' => 'reviewed',
                            'to' => 'rejected',
                        ],
                    ],
                ],
            ],
        ]);

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

.. versionadded:: 3.3

    The ``workflow_marked_places()`` and ``workflow_has_marked_place()``
    functions were introduced in Symfony 3.3.

The following example shows these functions in action:

.. code-block:: html+twig

    <h3>Actions</h3>
    {% if workflow_can(post, 'publish') %}
        <a href="...">Publish article</a>
    {% endif %}
    {% if workflow_can(post, 'to_review') %}
        <a href="...">Submit to review</a>
    {% endif %}
    {% if workflow_can(post, 'reject') %}
        <a href="...">Reject article</a>
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
    {% if 'waiting_some_approval' in workflow_marked_places(post) %}
        <span class="label">PENDING</span>
    {% endif %}

Learn more
----------

.. toctree::
   :maxdepth: 1

   /workflow/workflow-and-state-machine
   /workflow/dumping-workflows
