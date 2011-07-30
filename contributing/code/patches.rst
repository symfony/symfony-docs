Submitting a Patch
==================

Patches are the best way to provide a bug fix or to propose enhancements to
Symfony2.

Initial Setup
-------------

Before working on Symfony2, setup a friendly environment with the following
software:

* Git;

* PHP version 5.3.2 or above;

* PHPUnit 3.5.11 or above.

Set up your user information with your real name and a working email address:

.. code-block:: bash

    $ git config --global user.name "Your Name"
    $ git config --global user.email you@example.com

.. tip::

    If you are new to Git, we highly recommend you to read the excellent and
    free `ProGit`_ book.

Get the Symfony2 source code:

* Create a `GitHub`_ account and sign in;

* Fork the `Symfony2 repository`_ (click on the "Fork" button);

* After the "hardcore forking action" has completed, clone your fork locally
  (this will create a `symfony` directory):

.. code-block:: bash

      $ git clone git@github.com:USERNAME/symfony.git

* Add the upstream repository as ``remote``:

.. code-block:: bash

      $ cd symfony
      $ git remote add upstream git://github.com/symfony/symfony.git

Now that Symfony2 is installed, check that all unit tests pass for your
environment as explained in the dedicated :doc:`document <tests>`.

Working on a Patch
------------------

Each time you want to work on a patch for a bug or on an enhancement, you need
to create a topic branch.

The branch should be based on the `master` branch if you want to add a new
feature. But if you want to fix a bug, use the oldest but still maintained
version of Symfony where the bug happens (like `2.0`).

Create the topic branch with the following command:

.. code-block:: bash

    $ git checkout -b BRANCH_NAME master

.. tip::

    Use a descriptive name for your branch (`ticket_XXX` where `XXX` is the
    ticket number is a good convention for bug fixes).

The above command automatically switches the code to the newly created branch
(check the branch you are working on with `git branch`).

Work on the code as much as you want and commit as much as you want; but keep
in mind the following:

* Follow the coding :doc:`standards <standards>` (use `git diff --check` to
  check for trailing spaces);

* Add unit tests to prove that the bug is fixed or that the new feature
  actually works;

* Do atomic and logically separate commits (use the power of `git rebase` to
  have a clean and logical history);

* Write good commit messages.

.. tip::

    A good commit message is composed of a summary (the first line),
    optionally followed by a blank line and a more detailed description. The
    summary should start with the Component you are working on in square
    brackets (``[DependencyInjection]``, ``[FrameworkBundle]``, ...). Use a
    verb (``fixed ...``, ``added ...``, ...) to start the summary and don't
    add a period at the end.

Submitting a Patch
------------------

Before submitting your patch, update your branch (needed if it takes you a
while to finish your changes):

.. code-block:: bash

    $ git checkout master
    $ git fetch upstream
    $ git merge upstream/master
    $ git checkout BRANCH_NAME
    $ git rebase master

When doing the ``rebase`` command, you might have to fix merge conflicts.
``git status`` will show you the *unmerged* files. Resolve all the conflicts,
then continue the rebase:

.. code-block:: bash

    $ git add ... # add resolved files
    $ git rebase --continue

Check that all tests still pass and push your branch remotely:

.. code-block:: bash

    $ git push origin BRANCH_NAME

You can now discuss your patch on the `dev mailing-list`_ or make a pull
request (they must be done on the ``symfony/symfony`` repository). To ease the
core team work, always include the modified components in your pull request
message, like in:

.. code-block:: text

    [Yaml] foo bar
    [Form] [Validator] [FrameworkBundle] foo bar

If you are going to send an email to the mailing-list, don't forget to
reference you branch URL (``https://github.com/USERNAME/symfony.git
BRANCH_NAME``) or the pull request URL.

Based on the feedback from the mailing-list or via the pull request on GitHub,
you might need to rework your patch. Before re-submitting the patch, rebase
with master, don't merge; and force the push to the origin:

.. code-block:: bash

    $ git rebase -f upstream/master
    $ git push -f origin BRANCH_NAME

.. note::

    All patches you are going to submit must be released under the MIT
    license, unless explicitly specified in the code.

All bug fixes merged into maintenance branches are also merged into more
recent branches on a regular basis. For instance, if you submit a patch for
the `2.0` branch, the patch will also be applied by the core team on the
`master` branch.

.. _ProGit:              http://progit.org/
.. _GitHub:              https://github.com/signup/free
.. _Symfony2 repository: https://github.com/symfony/symfony
.. _dev mailing-list:    http://groups.google.com/group/symfony-devs
