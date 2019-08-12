Proposing a Change
==================

A pull request, "PR" for short, is the best way to provide a bug fix or to
propose enhancements to Symfony.

Step 1: Check existing Issues and Pull Requests
-----------------------------------------------

Before working on a change, check to see if someone else also raised the topic
or maybe even started working on a PR by `searching on GitHub`_.

If you are unsure or if you have any questions during this entire process,
please ask your questions on the #contribs channel on `Symfony Slack`_.

.. _step-1-setup-your-environment:

Step 2: Setup your Environment
------------------------------

Install the Software Stack
~~~~~~~~~~~~~~~~~~~~~~~~~~

Before working on Symfony, setup a friendly environment with the following
software:

* Git;
* PHP version 5.5.9 or above.

Configure Git
~~~~~~~~~~~~~

Set up your user information with your real name and a working email address:

.. code-block:: terminal

    $ git config --global user.name "Your Name"
    $ git config --global user.email you@example.com

.. tip::

    If you are new to Git, you are highly recommended to read the excellent and
    free `ProGit`_ book.

.. tip::

    If your IDE creates configuration files inside the project's directory,
    you can use global ``.gitignore`` file (for all projects) or
    ``.git/info/exclude`` file (per project) to ignore them. See
    `GitHub's documentation`_.

.. tip::

    Windows users: when installing Git, the installer will ask what to do with
    line endings, and suggests replacing all LF with CRLF. This is the wrong
    setting if you wish to contribute to Symfony! Selecting the as-is method is
    your best choice, as Git will convert your line feeds to the ones in the
    repository. If you have already installed Git, you can check the value of
    this setting by typing:

    .. code-block:: terminal

        $ git config core.autocrlf

    This will return either "false", "input" or "true"; "true" and "false" being
    the wrong values. Change it to "input" by typing:

    .. code-block:: terminal

        $ git config --global core.autocrlf input

    Replace --global by --local if you want to set it only for the active
    repository

Get the Symfony Source Code
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Get the Symfony source code:

* Create a `GitHub`_ account and sign in;

* Fork the `Symfony repository`_ (click on the "Fork" button);

* After the "forking action" has completed, clone your fork locally
  (this will create a ``symfony`` directory):

.. code-block:: terminal

      $ git clone git@github.com:USERNAME/symfony.git

* Add the upstream repository as a remote:

.. code-block:: terminal

      $ cd symfony
      $ git remote add upstream git://github.com/symfony/symfony.git

Check that the current Tests Pass
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that Symfony is installed, check that all unit tests pass for your
environment as explained in the dedicated :doc:`document <tests>`.

.. tip::

    If tests are failing, check on `Travis-CI`_ if the same test is
    failing there as well. In that case you do not need to be concerned
    about the test failing locally.

.. _step-2-work-on-your-patch:

Step 3: Work on your Pull Request
---------------------------------

The License
~~~~~~~~~~~

Before you start, you should be aware that all the code you are going to submit
must be released under the *MIT license*.

Choose the right Branch
~~~~~~~~~~~~~~~~~~~~~~~

Before working on a PR, you must determine on which branch you need to
work:

* ``3.4``, if you are fixing a bug for an existing feature or want to make a
  change that falls into the :doc:`list of acceptable changes in patch versions
  </contributing/code/maintenance>` (you may have to choose a higher branch if
  the feature you are fixing was introduced in a later version);

* ``master``, if you are adding a new feature.

  The only exception is when a new :doc:`major Symfony version </contributing/community/releases>`
  (4.0, 5.0, etc.) comes out every two years. Because of the
  :ref:`special development process <major-version-development>` of those versions,
  you need to use the previous minor version for the features (e.g. use ``3.4``
  instead of ``4.0``, use ``4.4`` instead of ``5.0``, etc.)

.. note::

    All bug fixes merged into maintenance branches are also merged into more
    recent branches on a regular basis. For instance, if you submit a PR
    for the ``3.4`` branch, the PR will also be applied by the core team on
    the ``master`` branch.

Create a Topic Branch
~~~~~~~~~~~~~~~~~~~~~

Each time you want to work on a PR for a bug or on an enhancement, create a
topic branch:

.. code-block:: terminal

    $ git checkout -b BRANCH_NAME master

Or, if you want to provide a bug fix for the ``3.4`` branch, first track the remote
``3.4`` branch locally:

.. code-block:: terminal

    $ git checkout -t origin/3.4

Then create a new branch off the ``3.4`` branch to work on the bug fix:

.. code-block:: terminal

    $ git checkout -b BRANCH_NAME 3.4

.. tip::

    Use a descriptive name for your branch (``ticket_XXX`` where ``XXX`` is the
    ticket number is a good convention for bug fixes).

The above checkout commands automatically switch the code to the newly created
branch (check the branch you are working on with ``git branch``).

Use your Branch in an Existing Project
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to test your code in an existing project that uses ``symfony/symfony``
or Symfony components, you can use the ``link`` utility provided in the Git repository
you cloned previously.
This tool scans the ``vendor/`` directory of your project, finds Symfony packages it
uses, and replaces them by symbolic links to the ones in the Git repository.

.. code-block:: terminal

    $ php link /path/to/your/project

Before running the ``link`` command, be sure that the dependencies of the project you
want to debug are installed by running ``composer install`` inside it.

.. _work-on-your-patch:

Work on your Pull Request
~~~~~~~~~~~~~~~~~~~~~~~~~

Work on the code as much as you want and commit as much as you want; but keep
in mind the following:

* Read about the Symfony :doc:`conventions <conventions>` and follow the
  coding :doc:`standards <standards>` (use ``git diff --check`` to check for
  trailing spaces -- also read the tip below);

* Add unit tests to prove that the bug is fixed or that the new feature
  actually works;

* Try hard to not break backward compatibility (if you must do so, try to
  provide a compatibility layer to support the old way) -- PRs that break
  backward compatibility have less chance to be merged;

* Do atomic and logically separate commits (use the power of ``git rebase`` to
  have a clean and logical history);

* Never fix coding standards in some existing code as it makes the code review
  more difficult;

* Write good commit messages (see the tip below).

.. tip::

    When submitting pull requests, `fabbot`_ checks your code
    for common typos and verifies that you are using the PHP coding standards
    as defined in `PSR-1`_ and `PSR-2`_.

    A status is posted below the pull request description with a summary
    of any problems it detects or any Travis CI build failures.

.. tip::

    A good commit message is composed of a summary (the first line),
    optionally followed by a blank line and a more detailed description. The
    summary should start with the Component you are working on in square
    brackets (``[DependencyInjection]``, ``[FrameworkBundle]``, ...). Use a
    verb (``fixed ...``, ``added ...``, ...) to start the summary and don't
    add a period at the end.

.. _prepare-your-patch-for-submission:

Prepare your Pull Request for Submission
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When your PR is not about a bug fix (when you add a new feature or change
an existing one for instance), it must also include the following:

* An explanation of the changes in the relevant ``CHANGELOG`` file(s) (the
  ``[BC BREAK]`` or the ``[DEPRECATION]`` prefix must be used when relevant);

* An explanation on how to upgrade an existing application in the relevant
  ``UPGRADE`` file(s) if the changes break backward compatibility or if you
  deprecate something that will ultimately break backward compatibility.

.. _step-4-submit-your-patch:

Step 4: Submit your Pull Request
--------------------------------

Whenever you feel that your PR is ready for submission, follow the
following steps.

.. _rebase-your-patch:

Rebase your Pull Request
~~~~~~~~~~~~~~~~~~~~~~~~

Before submitting your PR, update your branch (needed if it takes you a
while to finish your changes):

.. code-block:: terminal

    $ git checkout master
    $ git fetch upstream
    $ git merge upstream/master
    $ git checkout BRANCH_NAME
    $ git rebase master

.. tip::

    Replace ``master`` with the branch you selected previously (e.g. ``3.4``)
    if you are working on a bug fix.

When doing the ``rebase`` command, you might have to fix merge conflicts.
``git status`` will show you the *unmerged* files. Resolve all the conflicts,
then continue the rebase:

.. code-block:: terminal

    $ git add ... # add resolved files
    $ git rebase --continue

Check that all tests still pass and push your branch remotely:

.. code-block:: terminal

    $ git push --force origin BRANCH_NAME

.. _contributing-code-pull-request:

Make a Pull Request
~~~~~~~~~~~~~~~~~~~

You can now make a pull request on the ``symfony/symfony`` GitHub repository.

.. tip::

    Take care to point your pull request towards ``symfony:3.4`` if you want
    the core team to pull a bug fix based on the ``3.4`` branch.

To ease the core team work, always include the modified components in your
pull request message, like in:

.. code-block:: text

    [Yaml] fixed something
    [Form] [Validator] [FrameworkBundle] added something

The default pull request description contains a table which you must fill in
with the appropriate answers. This ensures that contributions may be reviewed
without needless feedback loops and that your contributions can be included into
Symfony as quickly as possible.

Some answers to the questions trigger some more requirements:

* If you answer yes to "Bug fix?", check if the bug is already listed in the
  Symfony issues and reference it/them in "Fixed tickets";

* If you answer yes to "New feature?", you must submit a pull request to the
  documentation and reference it under the "Doc PR" section;

* If you answer yes to "BC breaks?", the PR must contain updates to the
  relevant ``CHANGELOG`` and ``UPGRADE`` files;

* If you answer yes to "Deprecations?", the PR must contain updates to the
  relevant ``CHANGELOG`` and ``UPGRADE`` files;

* If you answer no to "Tests pass", you must add an item to a todo-list with
  the actions that must be done to fix the tests;

* If the "license" is not MIT, just don't submit the pull request as it won't
  be accepted anyway.

If some of the previous requirements are not met, create a todo-list and add
relevant items:

.. code-block:: text

    - [ ] fix the tests as they have not been updated yet
    - [ ] submit changes to the documentation
    - [ ] document the BC breaks

If the code is not finished yet because you don't have time to finish it or
because you want early feedback on your work, add an item to todo-list:

.. code-block:: text

    - [ ] finish the code
    - [ ] gather feedback for my changes

As long as you have items in the todo-list, please prefix the pull request
title with "[WIP]". If you do not yet want to trigger the automated tests,
you can also set the PR to `draft status`_.

In the pull request description, give as much detail as possible about your
changes (don't hesitate to give code examples to illustrate your points). If
your pull request is about adding a new feature or modifying an existing one,
explain the rationale for the changes. The pull request description helps the
code review and it serves as a reference when the code is merged (the pull
request description and all its associated comments are part of the merge
commit message).

In addition to this "code" pull request, you must also send a pull request to
the `documentation repository`_ to update the documentation when appropriate.

Step 5: Receiving Feedback
--------------------------

We ask all contributors to follow some
:doc:`best practices </contributing/community/reviews>`
to ensure a constructive feedback process.

If you think someone fails to keep this advice in mind and you want another
perspective, please join the #contribs channel on `Symfony Slack`_. If you
receive feedback you find abusive please contact the
:doc:`CARE team </contributing/code_of_conduct/care_team>`.

The :doc:`core team </contributing/code/core_team>` is responsible for deciding
which PR gets merged, so their feedback is the most relevant. So do not feel
pressured to refactor your code immediately when someone provides feedback.

.. _rework-your-patch:

Rework your Pull Request
~~~~~~~~~~~~~~~~~~~~~~~~

Based on the feedback on the pull request, you might need to rework your
PR. Before re-submitting the PR, rebase with ``upstream/master`` or
``upstream/3.4``, don't merge; and force the push to the origin:

.. code-block:: terminal

    $ git rebase -f upstream/master
    $ git push --force origin BRANCH_NAME

.. note::

    When doing a ``push --force``, always specify the branch name explicitly
    to avoid messing other branches in the repo (``--force`` tells Git that
    you really want to mess with things so do it carefully).

Moderators earlier asked you to "squash" your commits. This means you will
convert many commits to one commit. This is no longer necessary today, because
Symfony project uses a proprietary tool which automatically squashes all commits
before merging.

.. _ProGit: https://git-scm.com/book
.. _GitHub: https://github.com/join
.. _`GitHub's Documentation`: https://help.github.com/articles/ignoring-files
.. _Symfony repository: https://github.com/symfony/symfony
.. _dev mailing-list: https://groups.google.com/group/symfony-devs
.. _travis-ci.org: https://travis-ci.org/
.. _`travis-ci.org status icon`: https://about.travis-ci.com/docs/user/status-images/
.. _`travis-ci.org Getting Started Guide`: https://about.travis-ci.com/docs/user/getting-started/
.. _`documentation repository`: https://github.com/symfony/symfony-docs
.. _`fabbot`: https://fabbot.io
.. _`PSR-1`: https://www.php-fig.org/psr/psr-1/
.. _`PSR-2`: https://www.php-fig.org/psr/psr-2/
.. _`searching on GitHub`: https://github.com/symfony/symfony/issues?q=+is%3Aopen+
.. _`Symfony Slack`: https://symfony.com/slack-invite
.. _`Travis-CI`: https://travis-ci.org/symfony/symfony
.. _`draft status`: https://help.github.com/en/articles/about-pull-requests#draft-pull-requests
.. _`Symfony Roadmap`: https://symfony.com/roadmap
