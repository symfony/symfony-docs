.. index::
    single: Workflow; Usage

Using Workflow Events
===============================

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
            return array(
                'workflow.blog_publishing.leave' => 'onLeave',
            );
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
            /** @var \App\Entity\BlogPost $post */
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
This means that each event
Defining a Workflowhas access to the following information:

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
