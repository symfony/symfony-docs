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
three branches: ``2.0`` for the current Symfony 2.0.x release, ``2.1`` for the
current Symfony 2.1.x release and ``master`` as the development branch for
upcoming releases.

Unless you're documenting a feature that's new to Symfony 2.1, your changes
should always be based on the 2.0 branch instead of the master branch. To do
this checkout the 2.0 branch before the next step:

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

If you have made your changes based on the 2.0 branch then you need to change
the base branch to be 2.0 on the preview page:

.. image:: /images/docs-pull-request-change-base.png
   :align: center

.. note::

  All changes made to the 2.0 branch will be merged into 2.1 which in turn will be
  merged into the master branch for the next release on a weekly basis.

GitHub covers the topic of `pull requests`_ in detail.

.. note::

    The Symfony2 documentation is licensed under a Creative Commons
    Attribution-Share Alike 3.0 Unported :doc:`License <license>`.

.. tip::

    Please be patient. It can take from 15 minutes to several days for your changes
    to appear on the symfony.com website after the documentation team merges your
    pull request. You can check if your changes have introduced some markup issues
    by going to the `Documentation Build Errors`_ page (it is updated each French
    night at 3AM when the server rebuilds the documentation).

Standards
---------

In order to help the reader as much as possible and to create code examples that
look and feel familiar, you should follow these rules:

* The code follows the :doc:`Symfony Coding Standards</contributing/code/standards>`
  as well as the `Twig Coding Standards`_;
* Each line should break approximately after the first word that crosses the
  72nd character (so most lines end up being 72-78 lines);
* To avoid horizontal scrolling on code blocks, we prefer to break a line
  correctly if it crosses the 85th character;
* When you fold one or more lines of code, place ``...`` in a comment at the point
  of the fold. These comments are: ``// ...`` (php), ``# ...`` (yaml/bash), ``{# ... #}``
  (twig), ``<!-- ... -->`` (xml/html), ``; ...`` (ini), ``...`` (text);
* When you fold a part of a line, e.g. a variable value, put ``...`` (without comment)
  at the place of the fold;
* Description of the folded code: (optional)
  If you fold several lines: the description of the fold can be placed after the ``...``
  If you fold only part of a line: the description can be placed before the line;
* If useful, a ``codeblock`` should begin with a comment containing the filename
  of the file in the code block. Don't place a blank line after this comment,
  unless the next line is also a comment;
* You should put a ``$`` in front of every bash line;
* The ``::`` shorthand is preferred over ``.. code-block:: php`` to begin a PHP
  code block;
* You should use a form of *you* instead of *we*.

An example::

    // src/Foo/Bar.php

    // ...
    class Bar
    {
        // ...

        public function foo($bar)
        {
            // set foo with a value of bar
            $foo = ...;

            // ... check if $bar has the correct value

            return $foo->baz($bar, ...);
        }
    }

.. note::

    * In Yaml you should put a space after ``{`` and before ``}`` (e.g. ``{ _controller: ... }``),
      but this should not be done in Twig (e.g. ``{'hello' : 'value'}``).
    * An array item is a part of a line, not a complete line. So you should
      not use ``// ...`` but ``...,`` (the comma because of the Coding Standards)::

        array(
            'some value',
            ...,
        )

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

.. _`fork`: https://help.github.com/articles/fork-a-repo
.. _`pull requests`: https://help.github.com/articles/using-pull-requests
.. _`Documentation Build Errors`: http://symfony.com/doc/build_errors
.. _`Twig Coding Standards`: http://twig.sensiolabs.org/doc/coding_standards.html
