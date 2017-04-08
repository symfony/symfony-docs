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

With help from the ``EventDispatcher`` and the ``AuditTrailListener`` you
could easily enable logging::

    use Symfony\Component\Workflow\EventListener\AuditTrailListener;

    $logger = new AnyPsr3Logger();
    $subscriber = new AuditTrailListener($logger);
    $dispatcher->addSubscriber($subscriber);

Usage in Twig
-------------

Using your workflow in your Twig templates reduces the need of domain logic
in the view layer. Consider this example of the control panel of the blog.
The links below will only be displayed when the action is allowed:

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
