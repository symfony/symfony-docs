.. index::
    single: Workflow; Workflows as State Machines

Workflows as State Machines
===========================

The workflow component is modelled after a *Workflow net* which is a subclass
of a `Petri net`_. By adding further restrictions you can get a state machine.
The most important one being that a state machine cannot be in more than
one place simultaneously. It is also worth noting that a workflow does not
commonly have cyclic path in the definition graph, but it is common for a state
machine.

Example of State Machine
------------------------

A pull request starts in an intial "start" state, a state for e.g. running
tests on Travis. When this is finished, the pull request is in the "review"
state, where contributors can require changes, reject or accept the
pull request. At any time, you can also "update" the pull request, which
will result in another Travis run.

.. image:: /_images/components/workflow/pull_request.png

Below is the configuration for the pull request state machine.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
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
                <framework:workflow name="pull_request" type="state_machine">
                    <framework:marking-store type="scalar"/>

                    <framework:support>AppBundle\Entity\PullRequest</framework:support>

                    <framework:place>start</framework:place>
                    <framework:place>coding</framework:place>
                    <framework:place>travis</framework:place>
                    <framework:place>review</framework:place>
                    <framework:place>merged</framework:place>
                    <framework:place>closed</framework:place>

                    <framework:transition name="submit">
                        <framework:from>start</framework:from>

                        <framework:to>travis</framework:to>
                    </framework:transition>

                    <framework:transition name="update">
                        <framework:from>coding</framework:from>
                        <framework:from>travis</framework:from>
                        <framework:from>review</framework:from>

                        <framework:to>travis</framework:to>
                    </framework:transition>

                    <framework:transition name="wait_for_review">
                        <framework:from>travis</framework:from>

                        <framework:to>review</framework:to>
                    </framework:transition>

                    <framework:transition name="change_needed">
                        <framework:from>review</framework:from>

                        <framework:to>coding</framework:to>
                    </framework:transition>

                    <framework:transition name="accepted">
                        <framework:from>review</framework:from>

                        <framework:to>merged</framework:to>
                    </framework:transition>

                    <framework:transition name="rejected">
                        <framework:from>review</framework:from>

                        <framework:to>closed</framework:to>
                    </framework:transition>

                    <framework:transition name="reopened">
                        <framework:from>closed</framework:from>

                        <framework:to>review</framework:to>
                    </framework:transition>

                </framework:workflow>

            </framework:config>
        </container>

    .. code-block:: php

        use Symfony\Component\Workflow\Definition;
        use Symfony\Component\Workflow\Transition;
        use Symfony\Component\Workflow\StateMachine;
        use Symfony\Component\Workflow\MarkingStore\ScalarMarkingStore;

        $states = ['start', 'coding', 'travis', 'review', 'merged', 'closed'];
        $transitions[] = new Transition('submit', 'start', 'travis');
        $transitions[] = new Transition('update', 'coding', 'travis');
        $transitions[] = new Transition('update', 'travis', 'travis');
        $transitions[] = new Transition('update', 'review', 'travis');
        $transitions[] = new Transition('wait_for_reivew', 'travis', 'review');
        $transitions[] = new Transition('change_needed', 'review', 'coding');
        $transitions[] = new Transition('accepted', 'review', 'merged');
        $transitions[] = new Transition('rejected', 'review', 'closed');
        $transitions[] = new Transition('reopened', 'closed', 'review');

        $definition = new Definition($states, $transitions);
        $definition->setInitialPlace('start');

        $marking = new ScalarMarkingStore('marking');
        $stateMachine = new StateMachine($definition, $marking);


You can now use this state machine by getting the ``state_machine.pull_request`` service::

    $stateMachine = $this->container->get('state_machine.pull_request');


.. _Petri net: https://en.wikipedia.org/wiki/Petri_net
