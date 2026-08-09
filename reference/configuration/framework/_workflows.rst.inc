workflows
~~~~~~~~~

**type**: ``array``

A list of workflows to be created by the framework extension:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                my_workflow:
                    # ...

    .. code-block:: xml

        <!-- config/packages/workflow.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:workflows>
                    <framework:workflow
                        name="my_workflow"/>
                </framework:workflows>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/workflow.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->workflows()
                ->workflows('my_workflow')
                    // ...
            ;
        };

.. seealso::

    See also the article about :doc:`using workflows in Symfony applications </workflow>`.

.. _reference-workflows-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the support for workflows or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-workflows-name:

workflows
.........

**type**: ``prototype``

A list of workflows to create, keyed by the workflow name.

audit_trail
"""""""""""

**type**: ``boolean``

If set to ``true``, the :class:`Symfony\\Component\\Workflow\\EventListener\\AuditTrailListener`
will be enabled.

definition_validators
"""""""""""""""""""""

**type**: ``array``

A list of fully-qualified class names of definition validators. Each class must
implement :class:`Symfony\\Component\\Workflow\\Validator\\DefinitionValidatorInterface`
and its constructor must have no required parameters.

events_to_dispatch
""""""""""""""""""

**type**: ``array`` **default**: ``null``

Selects which transition events should be dispatched for this workflow.
If ``null``, all events are dispatched. Otherwise, it must be an array of
workflow event names (e.g. ``['workflow.enter', 'workflow.transition']``).

initial_marking
"""""""""""""""

**type**: ``string`` | ``array``

One of the ``places`` or ``empty``. If not null and the supported object is not
already initialized via the workflow, this place will be set.

marking_store
"""""""""""""

**type**: ``array``

Each marking store can define any of these options:

* ``property`` (**type**: ``string`` **default**: ``marking``)
* ``service`` (**type**: ``string``)
* ``type`` (**type**: ``string`` **allow value**: ``'method'``)

metadata
""""""""

**type**: ``array``

Metadata available for the workflow configuration.
Note that ``places`` and ``transitions`` can also have their own
``metadata`` entry.

places
""""""

**type**: ``array``

All available places (**type**: ``string``) for the workflow configuration.

supports
""""""""

**type**: ``string`` | ``array``

The FQCN (fully-qualified class name) of the object supported by the workflow
configuration or an array of FQCN if multiple objects are supported.

support_strategy
""""""""""""""""

**type**: ``string``

transitions
"""""""""""

**type**: ``array``

Each marking store can define any of these options:

* ``from`` (**type**: ``string`` or ``array``) value from the ``places``,
  multiple values are allowed for both ``workflow`` and ``state_machine``;
* ``guard`` (**type**: ``string``) an :doc:`ExpressionLanguage </components/expression_language>`
  compatible expression to block the transition;
* ``name`` (**type**: ``string``) the name of the transition;
* ``to`` (**type**: ``string`` or ``array``) value from the ``places``,
  multiple values are allowed only for ``workflow``.

.. _reference-workflows-type:

type
""""

**type**: ``string`` **possible values**: ``'workflow'`` or ``'state_machine'``

Defines the kind of workflow that is going to be created, which can be either
a normal workflow or a state machine. Read :doc:`this article </workflow/workflow-and-state-machine>`
to know their differences.
