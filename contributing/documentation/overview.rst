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

Consistent with Symfony's source code, the documentation repository is split into
multiple branches, corresponding to the different versions of Symfony itself.
The ``master`` branch holds the documentation for the development branch of the code.

Unless you're documenting a feature that was introduced *after* Symfony 2.1
(e.g. in Symfony 2.2), your changes should always be based on the 2.1 branch.
To do this checkout the 2.1 branch before the next step:

.. code-block:: bash

    $ git checkout 2.1

.. tip::

    Your base branch (e.g. 2.1) will become the "Applies to" in the :ref:`doc-contributing-pr-format`
    that you'll use later.

Next, create a dedicated branch for your changes (for organization):

.. code-block:: bash

    $ git checkout -b improving_foo_and_bar

You can now make your changes directly to this branch and commit them. When
you're done, push this branch to *your* GitHub fork and initiate a pull request.

Creating a Pull Request
~~~~~~~~~~~~~~~~~~~~~~~

Following the example, the pull request will default to be between your
``improving_foo_and_bar`` branch and the ``symfony-docs`` ``master`` branch.

.. image:: /images/docs-pull-request.png
   :align: center

If you have made your changes based on the 2.1 branch then you need to change
the base branch to be 2.1 on the preview page:

.. image:: /images/docs-pull-request-change-base.png
   :align: center

.. note::

  All changes made to a branch (e.g. 2.1) will be merged up to each "newer"
  branch (e.g. 2.2, master, etc) for the next release on a weekly basis.

GitHub covers the topic of `pull requests`_ in detail.

.. note::

    The Symfony2 documentation is licensed under a Creative Commons
    Attribution-Share Alike 3.0 Unported :doc:`License <license>`.

You can also prefix the title of your pull request in a few cases:

* ``[WIP]`` (Work in Progress) is used when you are not yet finished with your
  pull request, but you would like it to be reviewed. The pull request won't
  be merged until you say it is ready.

* ``[WCM]`` (Waiting Code Merge) is used when you're documenting a new feature
  or change that hasn't been accepted yet into the core code. The pull request
  will not be merged until it is merged in the core code (or closed if the
  change is rejected).

.. _doc-contributing-pr-format:

Pull Request Format
~~~~~~~~~~~~~~~~~~~

Unless you're fixing some minor typos, the pull request description **must**
include the following checklist to ensure that contributions may be reviewed
without needless feedback loops and that your contributions can be included
into the documentation as quickly as possible:

.. code-block:: text

    | Q             | A
    | ------------- | ---
    | Doc fix?      | [yes|no]
    | New docs?     | [yes|no] (PR # on symfony/symfony if applicable)
    | Applies to    | [Symfony version numbers this applies to]
    | Fixed tickets | [comma separated list of tickets fixed by the PR]

An example submission could now look as follows:

.. code-block:: text

    | Q             | A
    | ------------- | ---
    | Doc fix?      | yes
    | New docs?     | yes (symfony/symfony#2500)
    | Applies to    | all (or 2.1+)
    | Fixed tickets | #1075

.. tip::

    Please be patient. It can take from 15 minutes to several days for your changes
    to appear on the symfony.com website after the documentation team merges your
    pull request. You can check if your changes have introduced some markup issues
    by going to the `Documentation Build Errors`_ page (it is updated each French
    night at 3AM when the server rebuilds the documentation).

Documenting new Features or Behavior Changes
--------------------------------------------

If you're documenting a brand new feature or a change that's been made in
Symfony2, you should precede your description of the change with a ``.. versionadded:: 2.X``
tag and a short description:

.. code-block:: text

    .. versionadded:: 2.2
        The ``askHiddenResponse`` method was added in Symfony 2.2.

    You can also ask a question and hide the response. This is particularly...

If you're documenting a behavior change, it may be helpful to *briefly* describe
how the behavior has changed.

.. code-block:: text

    .. versionadded:: 2.2
        The ``include()`` function is a new Twig feature that's available in
        Symfony 2.2. Prior, the ``{% include %}`` tag was used.

Whenever a new minor version of Symfony2 is released (e.g. 2.3, 2.4, etc),
a new branch of the documentation is created from the ``master`` branch.
At this point, all the ``versionadded`` tags for Symfony2 versions that have
reached end-of-life will be removed. For example, if Symfony 2.5 were released
today, and 2.2 had recently reached its end-of-life, the 2.2 ``versionadded``
tags would be removed from the new 2.5 branch.

Standards
---------

All documentation in the Symfony Documentation should follow
:doc:`the documentation standards <standards>`.

Reporting an Issue
------------------

The most easy contribution you can make is reporting issues: a typo, a grammar
mistake, a bug in a code example, a missing explanation, and so on.

Steps:

* Submit a bug in the bug tracker;

* *(optional)* Submit a patch.

Translating
-----------

Read the dedicated :doc:`document <translations>`.

.. _`fork`: https://help.github.com/articles/fork-a-repo
.. _`pull requests`: https://help.github.com/articles/using-pull-requests
.. _`Documentation Build Errors`: http://symfony.com/doc/build_errors
