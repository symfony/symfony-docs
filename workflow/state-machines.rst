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

.. configuration-block::

    .. code-block:: yaml

        framework:
            workflows:
                blog_publishing:
                    type:
                        type: 'state_machine'
                    supports:
                        - AppBundle\Entity\BlogPost
                    places:
                        - draft
                        - review
                        - rejected
                        - published
                    transitions:
                        to_review:
                            from: [draft, rejected]
                            to:   review
                        publish:
                            from: review
                            to:   published
                        reject:
                            from: review
                            to:   rejected


With the configuration above we allow an object in place ``draft`` **or**
``rejected`` to be moved to ``review``. If the marking store had been of
type ``scalar`` the object had to be in **both** places.

.. code-block:: php

    $workflow = $this->container->get('state_machine.blog_publishing');
    $post = new \BlogPost();

    $post->state = 'draft';
    $workflow->can($post, 'to_review'); // True

    $post->state = 'rejected';
    $workflow->can($post, 'to_review'); // True





.. _Petri net: https://en.wikipedia.org/wiki/Petri_net
