.. index::
    single: Workflow; Dumping Workflows

How to Dump Workflows
=====================

To help you debug your workflows, you can dump a representation of your workflow
or state machine with the use of a ``DumperInterface``. Symfony provides two
different dumpers, both based on Dot (see below).

Use the ``GraphvizDumper`` or ``StateMachineGraphvizDumper`` to create DOT
files, or use ``PlantUmlDumper`` for PlantUML files. Both types can be converted
to PNG or SVG images.

Images of the workflow defined above::

    // dump-graph-dot.php
    $dumper = new GraphvizDumper();
    echo $dumper->dump($definition);

    // dump-graph-puml.php
    $dumper = new PlantUmlDumper();
    echo $dumper->dump($definition);

.. code-block:: terminal

    # dump DOT file in PNG image:
    $ php dump-graph-dot.php | dot -Tpng -o dot_graph.png

    # dump DOT file in SVG image:
    # $ php dump-graph-dot.php | dot -Tsvg -o dot_graph.svg

    # dump PlantUML in PNG image:
    $ php dump-graph-puml.php | java -jar plantuml.jar -p  > puml_graph.png

The DOT result will look like this:

.. image:: /_images/components/workflow/blogpost.png

The PlantUML result:

.. image:: /_images/components/workflow/blogpost_puml.png

Inside a Symfony application, you can dump the files with those commands using
``workflow:dump`` command:

.. code-block:: terminal

    $ php bin/console workflow:dump workflow_name | dot -Tpng -o workflow_name.png
    $ php bin/console workflow:dump workflow_name | dot -Tsvg -o workflow_name.svg
    $ php bin/console workflow:dump workflow_name --dump-format=puml | java -jar plantuml.jar -p  > workflow_name.png

.. note::

    The ``dot`` command is part of Graphviz. You can download it and read
    more about it on `Graphviz.org`_.

    The ``plantuml.jar`` command is part of PlantUML. You can download it and
    read more about it on `PlantUML.com`_.


.. _Graphviz.org: http://www.graphviz.org
.. _PlantUML.com: http://plantuml.com/
