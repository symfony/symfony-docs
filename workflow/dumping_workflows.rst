.. index::
    single: Workflow; Dumping Workflows

How to Dump Workflows
=====================

To help you debug your workflows, you can generate a visual representation of
them as SVG or PNG images. First, download and install the `Graphviz project`_,
an open source graph visualization software which provides the ``dot`` command
needed to generate the images.

If you are defining the workflow inside a Symfony application, run this command
to dump it as an image:

.. code-block:: terminal

    $ php bin/console workflow:dump workflow-name | dot -Tsvg -o graph.svg

    # run this command if you prefer PNG images:
    $ php bin/console workflow:dump workflow-name | dot -Tpng -o graph.png

    # highlight 'place1' and 'place2' in the dumped workflow
    $ php bin/console workflow:dump workflow-name place1 place2 | dot -Tsvg -o graph.svg

The result will look like this:

.. image:: /_images/components/workflow/blogpost.png

If you are creating workflows outside of a Symfony application, use the
``GraphvizDumper`` class to dump the workflow representation::

    // Add this code to a PHP script; for example: dump-graph.php
    $dumper = new GraphvizDumper();
    echo $dumper->dump($definition);

.. code-block:: terminal

    # replace 'dump-graph.php' by the name of your PHP script
    $ php dump-graph.php | dot -Tsvg -o graph.svg

.. _`Graphviz project`: http://www.graphviz.org
