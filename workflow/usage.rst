.. index::
    single: Workflow; Usage

How to Use the Workflow
=======================

Using the workflow component will help you to keep your domain logic as
configuration. Having domain logic in one place gives you a better overview
and it is easier to maintain whenever the domain requirement changes since
you do not have to edit all your controllers, twig templates and services.

A workflow is a process or a lifecycle that your objects go through. Each
step or stage in the process is called a *place*. You do also define *transitions*
to that describes the action to get from one place to another.

.. image:: /_images/components/workflow/states_transitions.png

A set of places and transitions creates a **definition**. A workflow needs
a ``Definition`` and a way to write the states to the objects, (i.e. an
instance of a :class:`Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface`.

Consider the following example for a blog post. A post can have places:
'draft', 'review', 'rejected', 'published'. You can define the workflow
like this:

.. code-block: yaml

    framework:
        workflows:
            blog_publishing:
                marking_store:
                    type: scalar # or 'property_accessor'
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

.. code-block: php

    class BlogPost
    {
        // This property is used by the marking store
        public $currentPlace;
        public $title;
        public $content
    }

With this workflow named ``blog_publishing`` you can get help to decide
what actions that are allowed on a blog post.

.. code-block:: php

    $post = new BlogPost();

    $workflow = $this->get('workflow.blog_publishing');
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

    class BlogPostReviewListener implements EventSubscriberInterface
    {
        public function guardReview(GuardEvent $event)
        {
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

With help from the ``EventDispatcher`` and the ``AuditTrailListener`` you
could easily enable logging::

    $logger = new PSR3Logger();
    $subscriber = new AuditTrailListener($logger);
    $dispatcher->addSubscriber($subscriber);

