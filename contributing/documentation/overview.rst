Contributing to the Documentation
=================================

Documentation is as important as code. It follows the exact same principles:
DRY, tests, ease of maintenance, extensibility, optimization, and refactoring
just to name a few. And of course, documentation has bugs, typos, hard to read
tutorials, and more.

Contributing
------------

Before contributing, you need to become familiar with the :doc:`markup
language <format>` used by the documentation.

The Symfony2 documentation is hosted on GitHub:

.. code-block:: text

    https://github.com/symfony/symfony-docs

If you want to submit a patch, `fork`_ the official repository on GitHub and
then clone your fork:

.. code-block:: bash

    $ git clone git://github.com/YOURUSERNAME/symfony-docs.git

Unless you're documenting a feature that's new to Symfony 2.1, you changes
should be based on the 2.0 branch instead of the master branch. To do this
checkout the 2.0 branch before the next step:

.. code-block:: bash

    $ git checkout 2.0


Next, create a dedicated branch for your changes (for organization):

.. code-block:: bash

    $ git checkout -b improving_foo_and_bar

You can now make your changes directly to this branch and commit them. When
you're done, push this branch to *your* GitHub fork and initiate a pull request.
The pull request will be between your ``improving_foo_and_bar`` branch and
the ``symfony-docs`` ``master`` branch.

.. image:: /images/docs-pull-request.png
   :align: center

If you have made your changes based
on the 2.0 branch then you need to follow the change commit link and change
the base branch to be @2.0:

.. image:: /images/docs-pull-request-change-base.png
   :align: center

GitHub covers the topic of `pull requests`_ in detail.

.. note::

    The Symfony2 documentation is licensed under a Creative Commons
    Attribution-Share Alike 3.0 Unported :doc:`License <license>`.

Reporting an Issue
------------------

The most easy contribution you can make is reporting issues: a typo, a grammar
mistake, a bug in code example, a missing explanation, and so on.

Steps:

* Submit a bug in the bug tracker;

* *(optional)* Submit a patch.

Translating
-----------

Read the dedicated :doc:`document <translations>`.

.. _`fork`: http://help.github.com/fork-a-repo/
.. _`pull requests`: http://help.github.com/pull-requests/
