How to Dump Workflows
=====================

To help you debug your workflows, you can generate a visual representation of
them as SVG or PNG images. First, install any of these free and open source
applications needed to generate the images:

* `Graphviz`_, provides the ``dot`` command;
* `Mermaid CLI`_, provides the ``mmdc`` command;
* `PlantUML`_, provides the ``plantuml.jar`` file (which requires Java).

If you are defining the workflow inside a Symfony application, run this command
to dump it as an image:

.. code-block:: terminal

    # using Graphviz's 'dot' and SVG images
    $ php bin/console workflow:dump workflow-name | dot -Tsvg -o graph.svg

    # using Graphviz's 'dot' and PNG images
    $ php bin/console workflow:dump workflow-name | dot -Tpng -o graph.png

    # using PlantUML's 'plantuml.jar'
    $ php bin/console workflow:dump workflow_name --dump-format=puml | java -jar plantuml.jar -p  > graph.png

    # highlight 'place1' and 'place2' in the dumped workflow
    $ php bin/console workflow:dump workflow-name place1 place2 | dot -Tsvg -o graph.svg

    # using Mermaid.js CLI
    $ php bin/console workflow:dump workflow_name --dump-format=mermaid | mmdc -o graph.svg

The DOT image will look like this:

.. image:: /_images/components/workflow/blogpost.png
    :alt: A state diagram of the Symfony workflow created by DOT.

The Mermaid image will look like this:

.. image:: /_images/components/workflow/blogpost_mermaid.png
    :alt: A state diagram of the Symfony workflow created by Mermaid.

The PlantUML image will look like this:

.. image:: /_images/components/workflow/blogpost_puml.png
    :alt: A state diagram of the Symfony workflow created by PlantUML.

If you are creating workflows outside of a Symfony application, use the
``GraphvizDumper`` or ``StateMachineGraphvizDumper`` class to create the DOT
files and ``PlantUmlDumper`` to create the PlantUML files::

    // Add this code to a PHP script; for example: dump-graph.php
    $dumper = new GraphvizDumper();
    echo $dumper->dump($definition);

    # if you prefer PlantUML, use this code:
    # $dumper = new PlantUmlDumper();
    # echo $dumper->dump($definition);

.. code-block:: terminal

    # replace 'dump-graph.php' by the name of your PHP script
    $ php dump-graph.php | dot -Tsvg -o graph.svg
    $ php dump-graph.php | java -jar plantuml.jar -p  > graph.png

Styling
-------

You can use ``--with-metadata`` option in the ``workflow:dump`` command to include places, transitions and
workflow's metadata.

The DOT image will look like this:

.. image:: /_images/components/workflow/blogpost_metadata.png

.. note::

    The ``--with-metadata`` option only works for the DOT dumper for now.

.. note::

    The ``label`` metadata is not included in the dumped metadata, because it is used as a place's title.

You can also pass the ``--with-listeners`` option to ``workflow:dump`` to
display the workflow listeners associated with places and transitions:

.. code-block:: terminal

    $ php bin/console workflow:dump workflow-name --with-listeners | dot -Tsvg -o graph.svg

.. versionadded:: 8.1

    The ``--with-listeners`` option was introduced in Symfony 8.1.

.. note::

    This option is currently only supported by the DOT dumper.

You can use ``metadata`` with the following keys to style the workflow:

* for places:

  * ``bg_color``: a color;
  * ``description``: a string that describes the state.

* for transitions:

  * ``label``: a string that replaces the name of the transition;
  * ``color``: a color;
  * ``arrow_color``: a color.

Strings can include ``\n`` characters to display the contents in multiple lines.
Colors can be defined as:

* a color name from `PlantUML's color list`_;
* an hexadecimal color (both ``#AABBCC`` and ``#ABC`` formats are supported).

.. note::

    The Mermaid dumper does not support coloring the arrow heads
    with ``arrow_color`` as there is no support in Mermaid for doing so.

Below is the configuration for the pull request state machine with styling added.

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
                    supports:
                        - App\Entity\PullRequest
                    initial_marking: start
                    places:
                        start: ~
                        coding: ~
                        test: ~
                        review:
                            metadata:
                                description: Human review
                        merged: ~
                        closed:
                            metadata:
                                bg_color: DeepSkyBlue
                    transitions:
                        submit:
                            from: start
                            to: test
                        update:
                            from: [coding, test, review]
                            to: test
                            metadata:
                                arrow_color: Turquoise
                        wait_for_review:
                            from: test
                            to: review
                            metadata:
                                color: Orange
                        request_change:
                            from: review
                            to: coding
                        accept:
                            from: review
                            to: merged
                            metadata:
                                label: Accept PR
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
                        'supports' => ['App\Entity\PullRequest'],
                        'initial_marking' => 'start',
                        'places' => [
                            'start',
                            'coding',
                            'test',
                            'review' => [
                                'metadata' => [
                                    'description' => 'Human review',
                                ],
                            ],
                            'merged',
                            'closed' => [
                                'metadata' => [
                                    'bg_color' => 'DeepSkyBlue',
                                ],
                            ],
                        ],
                        'transitions' => [
                            'submit' => [
                                'from' => 'start',
                                'to' => 'test',
                            ],
                            'update' => [
                                'from' => ['coding', 'test', 'review'],
                                'to' => 'test',
                                'metadata' => [
                                    'arrow_color' => 'Turquoise',
                                ],
                            ],
                            'wait_for_review' => [
                                'from' => 'test',
                                'to' => 'review',
                                'metadata' => [
                                    'color' => 'Orange',
                                ],
                            ],
                            'request_change' => [
                                'from' => 'review',
                                'to' => 'coding',
                            ],
                            'accept' => [
                                'from' => 'review',
                                'to' => 'merged',
                                'metadata' => [
                                    'label' => 'Accept PR',
                                ],
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

The PlantUML image will look like this:

.. image:: /_images/components/workflow/pull_request_puml_styled.png
    :alt: A state diagram created by PlantUML with custom transition colors and descriptions.

.. _`Graphviz`: https://www.graphviz.org
.. _`Mermaid CLI`: https://github.com/mermaid-js/mermaid-cli
.. _`PlantUML`: https://plantuml.com/
.. _`PlantUML's color list`: https://plantuml.com/color
