Submitting a Patch
==================

Patches are the best way to provide a bug fix or to propose enhancements to
Symfony2.

Check List
----------

The purpose of the check list is to ensure that contributions may be reviewed
without needless feedback loops to ensure that your contributions can be included
into Symfony2 as quickly as possible.

The pull request title should be prefixed with the component name or bundle
it relates to.

.. code-block:: text

    [Component] Short title description here.

An example title might look like this:

.. code-block:: text

    [Form] Add selectbox field type.

.. tip::

    Please use the title with "[WIP]" if the submission is not yet completed
    or the tests are incomplete or not yet passing.

All pull requests should include the following template in the request
description:

.. code-block:: text

    Bug fix: [yes|no]
    Feature addition: [yes|no]
    Backwards compatibility break: [yes|no]
    Symfony2 tests pass: [yes|no]
    Fixes the following tickets: [comma separated list of tickets fixed by the PR]
    Todo: [list of todos pending]
    
An example submission could now look as follows:

.. code-block:: text

    Bug fix: no
    Feature addition: yes
    Backwards compatibility break: no
    Symfony2 tests pass: yes
    Fixes the following tickets: -
    Todo: -

Thank you for including the filled out template in your submission!

.. tip::

    All feature addition's should be sent to the "master" branch, while all
    bug fixes should be sent to the oldest still active branch. Furthermore
    submissions should, as a rule of thumb, not break backwards compatibility.

.. tip::

    To automatically get your feature branch tested, you can add your fork to
    `travis-ci.org`_. Just login using your github.com account and then simply
    flip a single switch to enable automated testing. In your pull request,
    instead of specifying "*Symfony2 tests pass: [yes|no]*", you can link to
    the `travis-ci.org status icon`_. For more details, see the
    `travis-ci.org Getting Started Guide`_.

Initial Setup
-------------

Before working on Symfony2, setup a friendly environment with the following
software:

* Git;

* PHP version 5.3.2 or above;

* PHPUnit 3.6.4 or above.

Set up your user information with your real name and a working email address:

.. code-block:: bash

    $ git config --global user.name "Your Name"
    $ git config --global user.email you@example.com

.. tip::

    If you are new to Git, we highly recommend you to read the excellent and
    free `ProGit`_ book.

.. tip::

    Windows users: when installing Git, the installer will ask what to do with
    line endings and suggests to replace all Lf by CRLF. This is the wrong
    setting if you wish to contribute to Symfony! Selecting the as-is method is
    your best choice, as git will convert your line feeds to the ones in the
    repository. If you have already installed Git, you can check the value of
    this setting by typing:

    .. code-block:: bash

        $ git config core.autocrlf

    This will return either "false", "input" or "true", "true" and "false" being
    the wrong values. Set it to another value by typing:

    .. code-block:: bash

        $ git config --global core.autocrlf input

    Replace --global by --local if you want to set it only for the active
    repository

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

Or, if you want to provide a bugfix for the 2.0 branch, you need to first track
the remote `2.0` branch locally:

.. code-block:: bash

    $ git checkout -t origin/2.0

Then you can create a new branch off the 2.0 branch to work on the bugfix:

.. code-block:: bash

    $ git checkout -b BRANCH_NAME 2.0

.. tip::

    Use a descriptive name for your branch (`ticket_XXX` where `XXX` is the
    ticket number is a good convention for bug fixes).

The above checkout commands automatically switch the code to the newly created
branch (check the branch you are working on with `git branch`).

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

.. tip::

    Replace `master` with `2.0` if you are working on a bugfix

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

.. tip::

    Take care to point your pull request towards ``symfony:2.0`` if you want
    the core team to pull a bugfix based on the 2.0 branch.

If you are going to send an email to the mailing-list, don't forget to
reference you branch URL (``https://github.com/USERNAME/symfony.git
BRANCH_NAME``) or the pull request URL.

Based on the feedback from the mailing-list or via the pull request on GitHub,
you might need to rework your patch. Before re-submitting the patch, rebase
with upstream/master or upstream/2.0, don't merge; and force the push to the
origin:

.. code-block:: bash

    $ git rebase -f upstream/master
    $ git push -f origin BRANCH_NAME

.. note::

    when doing a push -f (or --force), always specify the branch name explicitly
    to avoid messing other branches in the repo (--force tells git that you
    really want to mess with things so do it carefully).

Often, moderators will ask you to "squash" your commits. This means you will
convert many commits to one commit. To do this, use the rebase command:

.. code-block:: bash

    $ git rebase -i head~3
    $ git push -f origin BRANCH_NAME

The number 3 here must equal the amount of commits in your branch. After you
type this command, an editor will popup showing a list of commits:

.. code-block:: text

    pick 1a31be6 first commit
    pick 7fc64b4 second commit
    pick 7d33018 third commit

To squash all commits into the first one, remove the word "pick" before the
second and the last commits, and replace it by the word "squash" or just "s".
When you save, git will start rebasing, and if succesful, will ask you to edit
the commit message, which by default is a listing of the commit messages of all
the commits. When you finish, execute the push command.

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
.. _travis-ci.org:       http://travis-ci.org
.. _`travis-ci.org status icon`: http://about.travis-ci.org/docs/user/status-images/
.. _`travis-ci.org Getting Started Guide`: http://about.travis-ci.org/docs/user/getting-started/