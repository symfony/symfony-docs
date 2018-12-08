Workflow
========

A workflow is a model of a process in your application. It may be the process
of how a blog post goes from draft, review and publish. Another example is when
a user submits a series of different forms to complete a task. Such processes are
best kept away from your models and should be defined in configuration.

A **definition** of a workflow consist of places and actions to get from one
place to another. The actions are called **transitions**. A workflow does also
need to know each object's position in the workflow. That **marking store** writes
to a property of the object to remember the current place.

.. note::

    The terminology above is commonly used when discussing workflows and
    `Petri nets`_

The Workflow component helps you handle different kind of workflows in your application.

.. tip::

    Component does also support state machines. A state machine is a subset
    of a workflow and its purpose is to hold a state of your model. Read more about the
    differences and specific features of state machine in :doc:`/workflow/state-machines`.

Examples
--------

The simplest workflow looks like this. It contains two places and one transition.

.. image:: /_images/components/workflow/simple.png

Workflows could be more complicated when they describe a real business case. The
workflow below describes the process to fill in a job application.

.. image:: /_images/components/workflow/job_application.png

When you fill in a job application in this example there are 4 to 7 steps depending
on the what job you are applying for. Some jobs require personality tests, logic tests
and/or formal requirements to be answered by the user. Some jobs don't. The
``GuardEvent`` is used to decide what next steps are allowed for a specific application.

By defining a workflow like this, there is an overview how the process looks like. The process
logic is not mixed with the controllers, models or view. The order of the steps can be changed
by changing the configuration only.


Installation
------------

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install the workflow feature before using it:

.. code-block:: terminal

    $ composer require symfony/workflow

Defining a Workflow
-------------------

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
                        type: 'single_state' # or 'multiple_state'
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
        <?xml version="1.0" encoding="utf-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >

            <framework:config>
                <framework:workflow name="blog_publishing" type="workflow">
                    <framework:audit-trail enabled="true" />

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

        $container->loadFromExtension('framework', array(
            // ...
            'workflows' => array(
                'blog_publishing' => array(
                    'type' => 'workflow', // or 'state_machine'
                    'audit_trail' => array(
                        'enabled' => true
                    ),
                    'marking_store' => array(
                        'type' => 'single_state', // or 'multiple_state'
                        'arguments' => array('currentPlace')
                    ),
                    'supports' => array('App\Entity\BlogPost'),
                    'places' => array(
                        'draft',
                        'review',
                        'rejected',
                        'published',
                    ),
                    'transitions' => array(
                        'to_review' => array(
                            'from' => 'draft',
                            'to' => 'review',
                         ),
                         'publish' => array(
                             'from' => 'review',
                             'to' => 'published',
                         ),
                         'reject' => array(
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
        public $currentPlace; // This property is used by the marking store
        public $title;
        public $content;
    }

.. note::

    The marking store type could be "single_state" or "multiple_state".
    A multiple state marking store allow your model to be on multiple places
    at the same time.

.. tip::

    The ``type`` (default value ``single_state``) and ``arguments`` (default
    value ``marking``) attributes of the ``marking_store`` option are optional.
    If omitted, their default values will be used.

.. tip::

    Setting the ``audit_trail.enabled`` option to ``true`` makes the application
    generate detailed log messages for the workflow activity.

.. tip::

    You can easily visualize your workflow by using ``workflow:dump`` command.
    Read more on :doc:`/workflow/dumping-workflows`


Workflow Events
---------------

To make your workflows more flexible, Workflow component allows you to listen on
several events raised during transitions. You can create event listeners to block them
(i.e. depending on the data in the blog post) and do additional actions when a workflow
operation happened (e.g. sending announcements). Read more on different kind of events
on :doc:`/workflow/events`


Using a Workflow
----------------

Once the ``blog_publishing`` workflow has been defined, you can now use it to
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

            // Update the currentPlace on the post
            try {
                $workflow->apply($post, 'to_review');
            } catch (TransitionException $exception) {
                // ... if the transition is not allowed
            }

            // See all the available transitions for the post in the current state
            $transitions = $workflow->getEnabledTransitions($post);
        }
    }

.. versionadded:: 4.1
    The :class:`Symfony\\Component\\Workflow\\Exception\\TransitionException`
    class was introduced in Symfony 4.1.

.. versionadded:: 4.1
    The :method:`Symfony\\Component\\Workflow\\Registry::all` method was
    introduced in Symfony 4.1.


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
    {% if workflow_has_marked_place(post, 'review') %}
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

   workflow/events
   workflow/state-machines
   workflow/dumping-workflows

.. _Petri nets: https://en.wikipedia.org/wiki/Petri_net
