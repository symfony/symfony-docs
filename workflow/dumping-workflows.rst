.. index::
    single: Workflow; Dumping Workflows

How to Dump Workflows
=====================

To help you debug your workflows, you can generate a visual representation of
them as SVG or PNG images. First, install any of these free and open source
applications needed to generate the images:

* `Graphviz`_, provides the ``dot`` command;
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

The DOT image will look like this:

.. image:: /_images/components/workflow/blogpost.png

The PlantUML image will look like this:

.. image:: /_images/components/workflow/blogpost_puml.png

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

.. _`Graphviz`: http://www.graphviz.org
.. _`PlantUML`: http://plantuml.com/
