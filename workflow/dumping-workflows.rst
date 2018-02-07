.. index::
    single: Workflow; Dumping Workflows

How to Dump Workflows
=====================

To help you debug your workflows, you can dump a representation of your workflow
or state machine with the use of a ``DumperInterface``. Symfony provides 2
different dumpers both based on Dot.

Use the ``GraphvizDumper`` or ``StateMachineGraphvizDumper`` to create DOT
files, or use ``PlantUmlDumper`` for PlantUML files. Both types can be converted
to PNG images.

Images of the workflow defined above:

.. code-block:: php

    // dump-graph-dot.php
    $dumper = new GraphvizDumper();
    echo $dumper->dump($definition);

.. code-block:: php

    // dump-graph-puml.php
    $dumper = new PlantUmlDumper();
    echo $dumper->dump($definition);

.. code-block:: terminal

    $ php dump-graph-dot.php | dot -Tpng -o dot_graph.png
    $ php dump-graph-puml.php | java -jar plantuml.jar -p  > puml_graph.png

The DOT result will look like this:

.. image:: /_images/components/workflow/blogpost.png

The PUML result:

.. image:: /_images/components/workflow/blogpost_puml.png

Inside a Symfony application, you can dump the files with those commands using
``workflow:dump`` command:

.. code-block:: terminal

    $ php bin/console workflow:dump name | dot -Tpng -o graph.png
    $ php bin/console workflow:dump name --dump-format=puml | java -jar plantuml.jar -p  > workflow.png

.. note::

    The ``dot`` command is part of Graphviz. You can download it and read
    more about it on `Graphviz.org`_.

    The ``plantuml.jar`` command is part of PlantUML. You can download it and
    read more about it on `PlantUML.com`_.


.. _Graphviz.org: http://www.graphviz.org
.. _PlantUML.com: http://plantuml.com/
