Submitting a Patch
==================

Patches are the best way to provide a bug fix or to propose enhancements to
Symfony2.

Step 1: Setup your Environment
------------------------------

Install the Software Stack
~~~~~~~~~~~~~~~~~~~~~~~~~~

Before working on Symfony2, setup a friendly environment with the following
software:

* Git;
* PHP version 5.3.3 or above;
* PHPUnit 3.6.4 or above.

Configure Git
~~~~~~~~~~~~~

Set up your user information with your real name and a working email address:

.. code-block:: bash

    $ git config --global user.name "Your Name"
    $ git config --global user.email you@example.com

.. tip::

    If you are new to Git, you are highly recommended to read the excellent and
    free `ProGit`_ book.

.. tip::

    If your IDE creates configuration files inside the project's directory,
    you can use global ``.gitignore`` file (for all projects) or
    ``.git/info/exclude`` file (per project) to ignore them. See
    `Github's documentation`_.

.. tip::

    Windows users: when installing Git, the installer will ask what to do with
    line endings, and suggests replacing all LF with CRLF. This is the wrong
    setting if you wish to contribute to Symfony! Selecting the as-is method is
    your best choice, as git will convert your line feeds to the ones in the
    repository. If you have already installed Git, you can check the value of
    this setting by typing:

    .. code-block:: bash

        $ git config core.autocrlf

    This will return either "false", "input" or "true"; "true" and "false" being
    the wrong values. Change it to "input" by typing:

    .. code-block:: bash

        $ git config --global core.autocrlf input

    Replace --global by --local if you want to set it only for the active
    repository

Get the Symfony Source Code
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Get the Symfony2 source code:

* Create a `GitHub`_ account and sign in;

* Fork the `Symfony2 repository`_ (click on the "Fork" button);

* After the "forking action" has completed, clone your fork locally
  (this will create a `symfony` directory):

.. code-block:: bash

      $ git clone git@github.com:USERNAME/symfony.git

* Add the upstream repository as a remote:

.. code-block:: bash

      $ cd symfony
      $ git remote add upstream git://github.com/symfony/symfony.git

Check that the current Tests pass
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that Symfony2 is installed, check that all unit tests pass for your
environment as explained in the dedicated :doc:`document <tests>`.

Step 2: Work on your Patch
--------------------------

The License
~~~~~~~~~~~

Before you start, you must know that all the patches you are going to submit
must be released under the *MIT license*, unless explicitly specified in your
commits.

Choose the right Branch
~~~~~~~~~~~~~~~~~~~~~~~

Before working on a patch, you must determine on which branch you need to
work. The branch should be based on the `master` branch if you want to add a
new feature. But if you want to fix a bug, use the oldest but still maintained
version of Symfony where the bug happens (like `2.1`).

.. note::

    All bug fixes merged into maintenance branches are also merged into more
    recent branches on a regular basis. For instance, if you submit a patch
    for the `2.1` branch, the patch will also be applied by the core team on
    the `master` branch.

Create a Topic Branch
~~~~~~~~~~~~~~~~~~~~~

Each time you want to work on a patch for a bug or on an enhancement, create a
topic branch:

.. code-block:: bash

    $ git checkout -b BRANCH_NAME master

Or, if you want to provide a bugfix for the 2.1 branch, first track the remote
`2.1` branch locally:

.. code-block:: bash

    $ git checkout -t origin/2.1

Then create a new branch off the 2.1 branch to work on the bugfix:

.. code-block:: bash

    $ git checkout -b BRANCH_NAME 2.1

.. tip::

    Use a descriptive name for your branch (`ticket_XXX` where `XXX` is the
    ticket number is a good convention for bug fixes).

The above checkout commands automatically switch the code to the newly created
branch (check the branch you are working on with `git branch`).

Work on your Patch
~~~~~~~~~~~~~~~~~~

Work on the code as much as you want and commit as much as you want; but keep
in mind the following:

* Read about the Symfony :doc:`conventions <conventions>` and follow the
  coding :doc:`standards <standards>` (use `git diff --check` to check for
  trailing spaces -- also read the tip below);

* Add unit tests to prove that the bug is fixed or that the new feature
  actually works;

* Try hard to not break backward compatibility (if you must do so, try to
  provide a compatibility layer to support the old way) -- patches that break
  backward compatibility have less chance to be merged;

* Do atomic and logically separate commits (use the power of `git rebase` to
  have a clean and logical history);

* Squash irrelevant commits that are just about fixing coding standards or
  fixing typos in your own code;

* Never fix coding standards in some existing code as it makes the code review
  more difficult;

* Write good commit messages (see the tip below).

.. tip::

    You can check the coding standards of your patch by running the following
    `script <http://cs.sensiolabs.org/get/php-cs-fixer.phar>`_
    (`source <https://github.com/fabpot/PHP-CS-Fixer>`_):

    .. code-block:: bash

        $ cd /path/to/symfony/src
        $ php symfony-cs-fixer.phar fix . Symfony20Finder

.. tip::

    A good commit message is composed of a summary (the first line),
    optionally followed by a blank line and a more detailed description. The
    summary should start with the Component you are working on in square
    brackets (``[DependencyInjection]``, ``[FrameworkBundle]``, ...). Use a
    verb (``fixed ...``, ``added ...``, ...) to start the summary and don't
    add a period at the end.

Prepare your Patch for Submission
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When your patch is not about a bug fix (when you add a new feature or change
an existing one for instance), it must also include the following:

* An explanation of the changes in the relevant CHANGELOG file(s) (the ``[BC
  BREAK]`` or the ``[DEPRECATION]`` prefix must be used when relevant);

* An explanation on how to upgrade an existing application in the relevant
  UPGRADE file(s) if the changes break backward compatibility or if you
  deprecate something that will ultimately break backward compatibility.

Step 3: Submit your Patch
-------------------------

Whenever you feel that your patch is ready for submission, follow the
following steps.

Rebase your Patch
~~~~~~~~~~~~~~~~~

Before submitting your patch, update your branch (needed if it takes you a
while to finish your changes):

.. code-block:: bash

    $ git checkout master
    $ git fetch upstream
    $ git merge upstream/master
    $ git checkout BRANCH_NAME
    $ git rebase master

.. tip::

    Replace `master` with `2.1` if you are working on a bugfix

When doing the ``rebase`` command, you might have to fix merge conflicts.
``git status`` will show you the *unmerged* files. Resolve all the conflicts,
then continue the rebase:

.. code-block:: bash

    $ git add ... # add resolved files
    $ git rebase --continue

Check that all tests still pass and push your branch remotely:

.. code-block:: bash

    $ git push origin BRANCH_NAME

Make a Pull Request
~~~~~~~~~~~~~~~~~~~

You can now make a pull request on the ``symfony/symfony`` Github repository.

.. tip::

    Take care to point your pull request towards ``symfony:2.1`` if you want
    the core team to pull a bugfix based on the 2.1 branch.

To ease the core team work, always include the modified components in your
pull request message, like in:

.. code-block:: text

    [Yaml] fixed something
    [Form] [Validator] [FrameworkBundle] added something

The pull request description must include the following checklist at the top
to ensure that contributions may be reviewed without needless feedback
loops and that your contributions can be included into Symfony2 as quickly as
possible:

.. code-block:: text

    | Q             | A
    | ------------- | ---
    | Bug fix?      | [yes|no]
    | New feature?  | [yes|no]
    | BC breaks?    | [yes|no]
    | Deprecations? | [yes|no]
    | Tests pass?   | [yes|no]
    | Fixed tickets | [comma separated list of tickets fixed by the PR]
    | License       | MIT
    | Doc PR        | [The reference to the documentation PR if any]

An example submission could now look as follows:

.. code-block:: text

    | Q             | A
    | ------------- | ---
    | Bug fix?      | no
    | New feature?  | no
    | BC breaks?    | no
    | Deprecations? | no
    | Tests pass?   | yes
    | Fixed tickets | #12, #43
    | License       | MIT
    | Doc PR        | symfony/symfony-docs#123

The whole table must be included (do **not** remove lines that you think are
not relevant). For simple typos, minor changes in the PHPDocs, or changes in
translation files, use the shorter version of the check-list:

.. code-block:: text

    | Q             | A
    | ------------- | ---
    | Fixed tickets | [comma separated list of tickets fixed by the PR]
    | License       | MIT

Some answers to the questions trigger some more requirements:

 * If you answer yes to "Bug fix?", check if the bug is already listed in the
   Symfony issues and reference it/them in "Fixed tickets";

 * If you answer yes to "New feature?", you must submit a pull request to the
   documentation and reference it under the "Doc PR" section;

 * If you answer yes to "BC breaks?", the patch must contain updates to the
   relevant CHANGELOG and UPGRADE files;

 * If you answer yes to "Deprecations?", the patch must contain updates to the
   relevant CHANGELOG and UPGRADE files;

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
title with "[WIP]".

In the pull request description, give as much details as possible about your
changes (don't hesitate to give code examples to illustrate your points). If
your pull request is about adding a new feature or modifying an existing one,
explain the rationale for the changes. The pull request description helps the
code review and it serves as a reference when the code is merged (the pull
request description and all its associated comments are part of the merge
commit message).

In addition to this "code" pull request, you must also send a pull request to
the `documentation repository`_ to update the documentation when appropriate.

Rework your Patch
~~~~~~~~~~~~~~~~~

Based on the feedback on the pull request, you might need to rework your
patch. Before re-submitting the patch, rebase with ``upstream/master`` or
``upstream/2.1``, don't merge; and force the push to the origin:

.. code-block:: bash

    $ git rebase -f upstream/master
    $ git push -f origin BRANCH_NAME

.. note::

    when doing a ``push --force``, always specify the branch name explicitly
    to avoid messing other branches in the repo (``--force`` tells git that
    you really want to mess with things so do it carefully).

Often, moderators will ask you to "squash" your commits. This means you will
convert many commits to one commit. To do this, use the rebase command:

.. code-block:: bash

    $ git rebase -i HEAD~3
    $ git push -f origin BRANCH_NAME

The number 3 here must equal the amount of commits in your branch. After you
type this command, an editor will popup showing a list of commits:

.. code-block:: text

    pick 1a31be6 first commit
    pick 7fc64b4 second commit
    pick 7d33018 third commit

To squash all commits into the first one, remove the word "pick" before the
second and the last commits, and replace it by the word "squash" or just "s".
When you save, git will start rebasing, and if successful, will ask you to
edit the commit message, which by default is a listing of the commit messages
of all the commits. When you finish, execute the push command.

.. _ProGit:                                http://git-scm.com/book
.. _GitHub:                                https://github.com/signup/free
.. _`Github's Documentation`:              https://help.github.com/articles/ignoring-files
.. _Symfony2 repository:                   https://github.com/symfony/symfony
.. _dev mailing-list:                      http://groups.google.com/group/symfony-devs
.. _travis-ci.org:                         https://travis-ci.org/
.. _`travis-ci.org status icon`:           http://about.travis-ci.org/docs/user/status-images/
.. _`travis-ci.org Getting Started Guide`: http://about.travis-ci.org/docs/user/getting-started/
.. _`documentation repository`:            https://github.com/symfony/symfony-docs
