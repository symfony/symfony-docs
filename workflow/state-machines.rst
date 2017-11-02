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

Example of a State Machine
--------------------------

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
                    supports:
                        - App\Entity\PullRequest
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
                        wait_for_review:
                            from: travis
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
                    <framework:marking-store type="single_state"/>

                    <framework:support>App\Entity\PullRequest</framework:support>

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

                    <framework:transition name="request_change">
                        <framework:from>review</framework:from>

                        <framework:to>coding</framework:to>
                    </framework:transition>

                    <framework:transition name="accept">
                        <framework:from>review</framework:from>

                        <framework:to>merged</framework:to>
                    </framework:transition>

                    <framework:transition name="reject">
                        <framework:from>review</framework:from>

                        <framework:to>closed</framework:to>
                    </framework:transition>

                    <framework:transition name="reopen">
                        <framework:from>closed</framework:from>

                        <framework:to>review</framework:to>
                    </framework:transition>

                </framework:workflow>

            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php

        $container->loadFromExtension('framework', array(
            // ...
            'workflows' => array(
                'pull_request' => array(
                  'type' => 'state_machine',
                  'supports' => array('App\Entity\PullRequest'),
                  'places' => array(
                    'start',
                    'coding',
                    'travis',
                    'review',
                    'merged',
                    'closed',
                  ),
                  'transitions' => array(
                    'submit'=> array(
                      'from' => 'start',
                      'to' => 'travis',
                    ),
                    'update'=> array(
                      'from' => array('coding','travis','review'),
                      'to' => 'travis',
                    ),
                    'wait_for_review'=> array(
                      'from' => 'travis',
                      'to' => 'review',
                    ),
                    'request_change'=> array(
                      'from' => 'review',
                      'to' => 'coding',
                    ),
                    'accept'=> array(
                      'from' => 'review',
                      'to' => 'merged',
                    ),
                    'reject'=> array(
                      'from' => 'review',
                      'to' => 'closed',
                    ),
                    'reopen'=> array(
                      'from' => 'start',
                      'to' => 'review',
                    ),
                  ),
                ),
            ),
        ));

You can now use this state machine by getting the ``state_machine.pull_request`` service::

    $stateMachine = $this->container->get('state_machine.pull_request');

.. _Petri net: https://en.wikipedia.org/wiki/Petri_net
