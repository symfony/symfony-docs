.. index::
    single: Workflow; Workflows as State Machines

Workflows as State Machines
===========================

The workflow component is modelled after a *Workflow net* which is a subclass
of a `Petri net`_. By adding further restrictions you can get a state machine.
The most important one being that a state machine cannot be in more than
one place simultaneously. It is also worth noting that a workflow does not
commonly have cyclic path in the definition graph but it is common for a state
machine.

Example of state machine
------------------------

Consider the states a GitHub pull request may have. We have an initial "start"
state, a state for running tests on "travis", then we have the "review" state
where we can require changes, reject or accept the pull request. At anytime we
could also "update" the pull request which will result in another "travis" run.

.. image:: /_images/components/workflow/pull_request.png

Below is the configuration for the pull request state machine.

.. configuration-block::

    .. code-block:: yaml

        framework:
            workflows:
                pull_request:
                   type: 'state_machine'
                   marking_store:
                       type: scalar
                   supports:
                        - AppBundle\Entity\PullRequest
                   places:
                        - start
                        - coding
                        - travis
                        - review
                        - merged
                        - closed
                   transitions:
                        submit:
                            from: start
                            to: travis
                        update:
                            from: [coding, travis, review]
                            to: travis
                        wait_for_reivew:
                            from: travis
                            to: review
                        change_needed:
                            from: review
                            to: coding
                        accepted:
                            from: review
                            to: merged
                        rejected:
                            from: review
                            to: closed
                        reopened:
                            from: closed
                            to: review


You can now use this state machine by getting the ``state_machine.pull_request`` service::

    $stateMachine = $this->container->get('state_machine.pull_request');


.. _Petri net: https://en.wikipedia.org/wiki/Petri_net
