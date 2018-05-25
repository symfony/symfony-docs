.. index::
    single: Workflow; Dumping Workflows

How to Dump Workflows
=====================

To help you debug your workflows, you can dump a representation of your workflow
with the use of a ``DumperInterface``. Use the ``GraphvizDumper`` to create a
SVG image of the workflow defined above::

    // dump-graph.php
    $dumper = new GraphvizDumper();
    echo $dumper->dump($definition);

.. code-block:: terminal

    $ php dump-graph.php | dot -Tsvg -o graph.svg

The result will look like this:

.. image:: /_images/components/workflow/blogpost.png

Inside a Symfony application, you can dump the dot file with the
``workflow:dump`` command:

.. code-block:: terminal

    $ php bin/console workflow:dump name | dot -Tsvg -o graph.svg

.. note::

    The ``dot`` command is part of Graphviz. You can download it and read
    more about it on `Graphviz.org`_.

.. _Graphviz.org: http://www.graphviz.org
