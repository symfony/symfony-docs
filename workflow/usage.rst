.. index::
    single: Workflow; Usage

How to Use the Workflow
=======================

A workflow is a process or a lifecycle that your objects go through. Each
step or stage in the process is called a *place*. You do also define *transitions*
to that describes the action to get from one place to another.

.. image:: /_images/components/workflow/states_transitions.png

A set of places and transitions creates a **definition**. A workflow needs
a ``Definition`` and a way to write the states to the objects (i.e. an
instance of a :class:`Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface`.)

Consider the following example for a blog post. A post can have places:
'draft', 'review', 'rejected', 'published'. You can define the workflow
like this:

.. configuration-block::

    .. code-block:: yaml

        framework:
            workflows:
                blog_publishing:
                    type: 'workflow' # or 'state_machine'
                    marking_store:
                        type: 'multiple_state' # or 'single_state'
                        arguments:
                            - 'currentPlace'
                    supports:
                        - AppBundle\Entity\BlogPost
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

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="utf-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >

            <framework:config>
                <framework:workflow name="blog_publishing" type="workflow">
                    <framework:marking-store type="single_state">
                      <framework:arguments>currentPlace</framework:arguments>
                    </framework:marking-store>

                    <framework:support>AppBundle\Entity\BlogPost</framework:support>

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

        // app/config/config.php

                $container->loadFromExtension('framework', array(
                    // ...
                    'workflows' => array(
                        'blog_publishing' => array(
                          'type' => 'workflow', // or 'state_machine'
                          'marking_store' => array(
                            'type' => 'multiple_state', // or 'single_state'
                            'arguments' => array('currentPlace')
                          ),
                          'supports' => array('AppBundle\Entity\BlogPost'),
                          'places' => array(
                            'draft',
                            'review',
                            'rejected',
                            'published',
                          ),
                          'transitions' => array(
                            'to_review'=> array(
                              'from' => 'draft',
                              'to' => 'review',
                            ),
                            'publish'=> array(
                              'from' => 'review',
                              'to' => 'published',
                            ),
                            'reject'=> array(
                              'from' => 'review',
                              'to' => 'rejected',
                            ),
                          ),
                        ),
                    ),
                ));

.. code-block:: php

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

    The ``type`` (default value ``single_state``) and ``arguments`` (default value ``marking``)
    attributes of the ``marking_store`` option are optional. If omitted, their default values
    will be used.

With this workflow named ``blog_publishing``, you can get help to decide
what actions are allowed on a blog post::

    $post = new \AppBundle\Entity\BlogPost();

    $workflow = $this->container->get('workflow.blog_publishing');
    $workflow->can($post, 'publish'); // False
    $workflow->can($post, 'to_review'); // True

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
block transitions (i.e. depending on the data in the blog post). The following
events are dispatched:

* ``workflow.leave``
* ``workflow.[workflow name].leave``
* ``workflow.[workflow name].leave.[place name]``

* ``workflow.transition``
* ``workflow.[workflow name].transition``
* ``workflow.[workflow name].transition.[transition name]``

* ``workflow.enter``
* ``workflow.[workflow name].enter``
* ``workflow.[workflow name].enter.[place name]``

* ``workflow.entered``
* ``workflow.[workflow name].entered``
* ``workflow.[workflow name].entered.[place name]``

* ``workflow.announce``
* ``workflow.[workflow name].announce``
* ``workflow.[workflow name].announce.[transition name]``

Here is an example how to enable logging for every time a the "blog_publishing" workflow leaves a place::

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
            return array(
                'workflow.blog_publishing.leave' => 'onLeave',
            );
        }
    }

Guard events
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
            /** @var \AppBundle\Entity\BlogPost $post */
            $post = $event->getSubject();
            $title = $post->title;

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


Event Methods
~~~~~~~~~~~~~

Each workflow event is an instance of :class:`Symfony\\Component\\Workflow\\Event\\Event`.
This means that each event has access to the following information:

:method:`Symfony\\Component\\Workflow\\Event\\Event::getMarking`
    Returns the :class:`Symfony\\Component\\Workflow\\Marking` of the workflow.

:method:`Symfony\\Component\\Worflow\\Event\\Event::getSubject`
    Returns the object that dispatches the event.

:method:`Symfony\\Component\\Workflow\\Event\\Event::getTransition`
    Returns the :class:`Symfony\\Component\\Workflow\\Transition` that dispatches the event.

:method:`Symfony\\Component\\Workflow\\Event\\Event::getWorkflowName`
    Returns a string with the name of the workflow that triggered the event.

    .. versionadded:: 3.3
        The ``getWorkflowName()`` method was introduced in Symfony 3.3.

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

.. versionadded:: 3.3
    The ``workflow_marked_places()`` and ``workflow_has_marked_place()``
    functions were introduced in Symfony 3.3.

The following example shows these functions in action:

.. code-block:: twig

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
    {% if workflow_has_marked_place(post, 'to_review') %}
        <p>This post is ready for review.</p>
    {% endif %}

    {# Check if some place has been marked on the object #}
    {% if 'waiting_some_approval' in workflow_marked_places(post) %}
        <span class="label">PENDING</span>
    {% endif %}
