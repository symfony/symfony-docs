.. index::
    single: Workflow; Usage

How to Create and Use Workflows
===============================

Installation
------------

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install the workflow feature before using it:

.. code-block:: terminal

    $ composer require symfony/workflow

Creating a Workflow
-------------------

A workflow is a process or a lifecycle that your objects go through. Each
step or stage in the process is called a *place*. You do also define *transitions*
to that describes the action to get from one place to another.

.. image:: /_images/components/workflow/states_transitions.png

A set of places and transitions creates a **definition**. A workflow needs
a ``Definition`` and a way to write the states to the objects (i.e. an
instance of a :class:`Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface`.)

Consider the following example for a blog post that can have these places:
``draft``, ``review``, ``rejected``, ``published``. You can define the workflow
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
                        type: 'multiple_state' # or 'single_state'
                        arguments:
                            - 'currentPlace'
                    supports:
                        - App\Entity\BlogPost
                    initial_place: draft
                    places:
                        - draft
                        - review
                        - rejected
                        - published
                    transitions:
                        to_review:
                            from: draft
                            to:   review
                        publish:
                            from: review
                            to:   published
                        reject:
                            from: review
                            to:   rejected

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
                <framework:workflow name="blog_publishing" type="workflow">
                    <framework:audit-trail enabled="true"/>

                    <framework:marking-store type="single_state">
                      <framework:argument>currentPlace</framework:argument>
                    </framework:marking-store>

                    <framework:support>App\Entity\BlogPost</framework:support>

                    <framework:place>draft</framework:place>
                    <framework:place>review</framework:place>
                    <framework:place>rejected</framework:place>
                    <framework:place>published</framework:place>

                    <framework:transition name="to_review">
                        <framework:from>draft</framework:from>

                        <framework:to>review</framework:to>
                    </framework:transition>

                    <framework:transition name="publish">
                        <framework:from>review</framework:from>

                        <framework:to>published</framework:to>
                    </framework:transition>

                    <framework:transition name="reject">
                        <framework:from>review</framework:from>

                        <framework:to>rejected</framework:to>
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
                    'type' => 'workflow', // or 'state_machine'
                    'audit_trail' => [
                        'enabled' => true
                    ],
                    'marking_store' => [
                        'type' => 'multiple_state', // or 'single_state'
                        'arguments' => ['currentPlace'],
                    ],
                    'supports' => ['App\Entity\BlogPost'],
                    'places' => [
                        'draft',
                        'review',
                        'rejected',
                        'published',
                    ],
                    'transitions' => [
                        'to_review' => [
                            'from' => 'draft',
                            'to' => 'review',
                        ],
                        'publish' => [
                            'from' => 'review',
                            'to' => 'published',
                        ],
                        'reject' => [
                            'from' => 'review',
                            'to' => 'rejected',
                        ],
                    ],
                ],
            ],
        ]);

As configured, the following property is used by the marking store::

    class BlogPost
    {
        // This property is used by the marking store
        public $currentPlace;
        public $title;
        public $content;
    }

.. note::

    The marking store type could be "multiple_state" or "single_state".
    A single state marking store does not support a model being on multiple places
    at the same time.

.. tip::

    The ``type`` (default value ``single_state``) and ``arguments`` (default
    value ``marking``) attributes of the ``marking_store`` option are optional.
    If omitted, their default values will be used.

.. tip::

    Setting the ``audit_trail.enabled`` option to ``true`` makes the application
    generate detailed log messages for the workflow activity.

Using a Workflow
----------------

Once the ``blog_publishing`` workflow has been created, you can now use it to
decide what actions are allowed on a blog post. For example, inside a controller
of an application using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you can get the workflow by injecting the Workflow registry service::

    // ...
    use Symfony\Component\Workflow\Registry;
    use App\Entity\BlogPost;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Workflow\Exception\TransitionException;

    class BlogController extends AbstractController
    {
        public function edit(Registry $workflows)
        {
            $post = new BlogPost();
            $workflow = $workflows->get($post);

            // if there are multiple workflows for the same class,
            // pass the workflow name as the second argument
            // $workflow = $workflows->get($post, 'blog_publishing');

            // you can also get all workflows associated with an object, which is useful
            // for example to show the status of all those workflows in a backend
            $postWorkflows = $workflows->all($post);

            $workflow->can($post, 'publish'); // False
            $workflow->can($post, 'to_review'); // True

            // Update the currentState on the post
            try {
                $workflow->apply($post, 'to_review');
            } catch (TransitionException $exception) {
                // ... if the transition is not allowed
            }

            // See all the available transitions for the post in the current state
            $transitions = $workflow->getEnabledTransitions($post);
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
    Validate whether the transition is allowed at all (:ref:`see below <workflow-usage-guard-events>`).

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
    The subject has entered in the places and the marking is updated (making it a good
    place to flush data in Doctrine).

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

Here is an example of how to enable logging for every time the ``blog_publishing``
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
                'Blog post (id: "%s") performed transaction "%s" from "%s" to "%s"',
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
add custom logic to decide what transitions that are valid or not. Here is a list
of the guard event names.

* ``workflow.guard``
* ``workflow.[workflow name].guard``
* ``workflow.[workflow name].guard.[transition name]``

See example to make sure no blog post without title is moved to "review"::

    use Symfony\Component\Workflow\Event\GuardEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class BlogPostReviewListener implements EventSubscriberInterface
    {
        public function guardReview(GuardEvent $event)
        {
            /** @var App\Entity\BlogPost $post */
            $post = $event->getSubject();
            $title = $post->title;

            if (empty($title)) {
                // Posts with no title should not be allowed
                $event->setBlocked(true);
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                'workflow.blogpost.guard.to_review' => ['guardReview'],
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

For Guard Events, there is an extended class :class:`Symfony\\Component\\Workflow\\Event\\GuardEvent`.
This class has two more methods:

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::isBlocked`
    Returns if transition is blocked.

:method:`Symfony\\Component\\Workflow\\Event\\GuardEvent::setBlocked`
    Sets the blocked value.

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
    {% if workflow_has_marked_place(post, 'review') %}
        <p>This post is ready for review.</p>
    {% endif %}

    {# Check if some place has been marked on the object #}
    {% if 'waiting_some_approval' in workflow_marked_places(post) %}
        <span class="label">PENDING</span>
    {% endif %}

Transition Blockers
-------------------

.. versionadded:: 4.1

    Transition Blockers were introduced in Symfony 4.1.

Transition Blockers provide a way to return a human-readable message for why a
transition was blocked::

    use Symfony\Component\Workflow\Event\GuardEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class BlogPostPublishListener implements EventSubscriberInterface
    {
        public function guardPublish(GuardEvent $event)
        {
            /** @var \App\Entity\BlogPost $post */
            $post = $event->getSubject();

            // If it's after 9pm, prevent publication
            if (date('H') > 21) {
                $event->addTransitionBlocker(
                    new TransitionBlocker(
                        "You can not publish this blog post because it's too late. Try again tomorrow morning."
                    )
                );
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                'workflow.blogpost.guard.publish' => ['guardPublish'],
            ];
        }
    }

You can access the message from a Twig template as follows:

.. code-block:: html+twig

    <h2>Publication was blocked because:</h2>
    <ul>
        {% for transition in workflow_all_transitions(article) %}
            {% if not workflow_can(article, transition.name) %}
                <li>
                    <strong>{{ transition.name }}</strong>:
                    <ul>
                    {% for blocker in workflow_build_transition_blocker_list(article, transition.name) %}
                        <li>
                            {{ blocker.message }}
                            {% if blocker.parameters.expression is defined %}
                                <code>{{ blocker.parameters.expression }}</code>
                            {% endif %}
                        </li>
                    {% endfor %}
                    </ul>
                </li>
            {% endif %}
        {% endfor %}
    </ul>

Don't need a human-readable message? You can still use::

    $event->setBlocked('true');
