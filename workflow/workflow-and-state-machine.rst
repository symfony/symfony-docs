Workflows and State Machines
============================

Workflows
---------

A workflow is a model of a process in your application. It may be the process of
how a blog post goes from draft to review and publish. Another example is when a
user submits a series of different forms to complete a task. Such processes are
best kept away from your models and should be defined in configuration.

A **definition** of a workflow consists of places and actions to get from one
place to another. The actions are called **transitions**. A workflow also needs to
know each object's position in the workflow. The **marking store** writes
the current place to a property on the object.

.. note::

    The terminology above is commonly used when discussing workflows and
    `Petri nets`_.

Examples
~~~~~~~~

The simplest workflow looks like this. It contains two places and one transition.

.. image:: /_images/components/workflow/simple.png
    :alt: A simple state diagram showing a single transition between two places.

Workflows could be more complicated when they describe a real business case. The
workflow below describes the process to fill in a job application.

.. image:: /_images/components/workflow/job_application.png
    :alt: A complex state diagram showing many places with multiple possible transitions between them.

When you fill in a job application in this example there are 4 to 7 steps
depending on the job you are applying for. Some jobs require personality
tests, logic tests and/or formal requirements to be answered by the user. Some
jobs don't. The ``GuardEvent`` is used to decide what next steps are allowed for
a specific application.

By defining a workflow like this, there is an overview of how the process looks. The process logic is not mixed with the controllers, models or view. The
order of the steps can be changed by changing the configuration only.

State Machines
--------------

A state machine is a subset of a workflow and its purpose is to hold a state of
your model. The most important differences between them are:

* Workflows can be in more than one place at the same time, whereas state
  machines can't;
* In order to apply a transition, workflows require that the object is in all
  the previous places of the transition, whereas state machines only require
  that the object is at least in one of those places.

Example
~~~~~~~

A pull request starts in an initial "start" state, then a state "test", e.g. for running
tests on continuous integration stack. When this is finished, the pull request is in the "review"
state, where contributors can require changes, reject or accept the
pull request. At any time, you can also "update" the pull request, which
will result in another continuous integration run.

.. image:: /_images/components/workflow/pull_request.png
    :alt: A state diagram for the pull request process described previously.

Below is the configuration for the pull request state machine.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                pull_request:
                    type: 'state_machine'
                    marking_store:
                         type: 'method'
                         property: 'currentPlace'
                    # The "supports" option is useful only if you are using Twig functions ('workflow_*')
                    supports:
                        - App\Entity\PullRequest
                    initial_marking: start
                    places:
                        - start
                        - coding
                        - test
                        - review
                        - merged
                        - closed
                    transitions:
                        submit:
                            from: start
                            to: test
                        update:
                            from: [coding, test, review]
                            to: test
                        wait_for_review:
                            from: test
                            to: review
                        request_change:
                            from: review
                            to: coding
                        accept:
                            from: review
                            to: merged
                        reject:
                            from: review
                            to: closed
                        reopen:
                            from: closed
                            to: review

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'workflows' => [
                    'pull_request' => [
                        'type' => 'state_machine',
                        'marking_store' => [
                            'type' => 'method',
                            'property' => 'currentPlace',
                        ],
                        // The "supports" option is useful only if you are using Twig functions ('workflow_*')
                        'supports' => ['App\Entity\PullRequest'],
                        'initial_marking' => 'start',
                        'places' => [
                            'start',
                            'coding',
                            'test',
                            'review',
                            'merged',
                            'closed',
                        ],
                        'transitions' => [
                            'submit' => [
                                'from' => 'start',
                                'to' => 'test',
                            ],
                            'update' => [
                                'from' => ['coding', 'test', 'review'],
                                'to' => 'test',
                            ],
                            'wait_for_review' => [
                                'from' => 'test',
                                'to' => 'review',
                            ],
                            'request_change' => [
                                'from' => 'review',
                                'to' => 'coding',
                            ],
                            'accept' => [
                                'from' => 'review',
                                'to' => 'merged',
                            ],
                            'reject' => [
                                'from' => 'review',
                                'to' => 'closed',
                            ],
                            'reopen' => [
                                'from' => 'closed',
                                'to' => 'review',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

.. tip::

    You can omit the ``places`` option if your transitions define all the places
    that are used in the workflow. Symfony will automatically extract the places
    from the transitions.

Symfony automatically creates a service for each workflow (:class:`Symfony\\Component\\Workflow\\Workflow`)
or state machine (:class:`Symfony\\Component\\Workflow\\StateMachine`) you
have defined in your configuration. You can use the workflow inside a class by using
:doc:`service autowiring </service_container/autowiring>` and using
``camelCased workflow name + Workflow`` as parameter name. If it is a state
machine type, use ``camelCased workflow name + StateMachine``::

    // ...
    use App\Entity\PullRequest;
    use Symfony\Component\Workflow\WorkflowInterface;

    class SomeService
    {
        public function __construct(
            // Symfony will inject the 'pull_request' state machine configured before
            private WorkflowInterface $pullRequestStateMachine,
        ) {
        }

        public function someMethod(PullRequest $pullRequest): void
        {
            $this->pullRequestStateMachine->apply($pullRequest, 'wait_for_review', [
                'log_comment' => 'My logging comment for the wait for review transition.',
            ]);
            // ...
        }

        // ...
    }

Automatic and Manual Validation
-------------------------------

During cache warmup, Symfony validates the workflows and state machines that are
defined in configuration files. If your workflows or state machines are defined
programmatically instead of in a configuration file, you can validate them with
the :class:`Symfony\\Component\\Workflow\\Validator\\WorkflowValidator` and
:class:`Symfony\\Component\\Workflow\\Validator\\StateMachineValidator`::

    // ...
    use Symfony\Component\Workflow\Definition;
    use Symfony\Component\Workflow\StateMachine;
    use Symfony\Component\Workflow\Validator\StateMachineValidator;

    $states = ['created', 'activated', 'deleted'];
    $stateTransitions = [
        new Transition('activate', 'created', 'activated'),
        // This duplicate event "from" the "created" state is invalid
        new Transition('activate', 'created', 'deleted'),
        new Transition('delete', 'activated', 'deleted'),
    ];

    // No validation is done upon initialization
    $definition = new Definition($states, $stateTransitions);

    $validator = new StateMachineValidator();
    // Throws InvalidDefinitionException in case of an invalid definition
    $validator->validate($definition, 'My First StateMachine');

.. _`Petri nets`: https://en.wikipedia.org/wiki/Petri_net
