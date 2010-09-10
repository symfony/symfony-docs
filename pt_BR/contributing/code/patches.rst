Submitting a Patch
==================

Patches are the best way to provide a bug fix or to propose enhancements to
Symfony.

Initial Setup
-------------

Before working on Symfony2, setup a friendly environment with the following
software:

* Git;

* PHP version 5.3.2 or above;

* PHPUnit 3.5.0 or above.

Set up your user information with your real name and a working email address:

.. code-block:: bash

    $ git config --global user.name "Your Name"
    $ git config --global user.email you@example.com

.. tip::
   If you are new to Git, we highly recommend you to read the excellent and free
   `ProGit`_ book.

Get the Symfony2 code source:

* Create a `Github`_ account and sign in;

* Fork the `Symfony2 repository`_ (click on the "Fork" button);

* After the "hardcore forking action" has completed, clone your fork locally
  (this will create a `symfony` directory):

.. code-block:: bash

      $ git clone git@github.com:USERNAME/symfony.git

* Add the upstream repository as remote:

.. code-block:: bash

      $ cd symfony
      $ git remote add upstream git://github.com/fabpot/symfony.git

Now that Symfony2 is installed, check that all unit tests pass for your
environment as explained in the dedicated :doc:`document <tests>`.

Working on a Patch
------------------

Each time you want to work on a patch for a bug or on an enhancement, create a
topic branch:

.. code-block:: bash

    $ git checkout -b BRANCH_NAME

.. tip::
   Use a descriptive name for your branch (`ticket_XXX` where `XXX` is the ticket
   number is a good convention for bug fixes).

The above command automatically switches the code to the newly created branch
(check the branch you are working on with `git branch`.)

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
   A good commit message is composed of a summary (the first line), optionally
   followed by a blank line and a more detailed description. The summary should
   start with the Component you are working on in square brackets
   (`[DependencyInjection]`, `[FoundationBundle]`, ...). Use a verb (`fixed ...`,
   `added ...`, ...) to start the summary and don't add a period at the end.

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

When doing the `rebase` command, you might have to fix merge conflicts. `git
st` gives you the *unmerged* files. Resolve all conflicts, then continue the
rebase:

.. code-block:: bash

    $ git add ... # add resolved files
    $ git rebase --continue

Check that all tests still pass and push your branch remotely:

.. code-block:: bash

    $ git push origin BRANCH_NAME

You can now advertise your patch on the `dev mailing-list`_. The email must
follow the following conventions:

* Subject must start with `[PATCH]`, followed by a short summary of the
  patch (with a reference to the ticket if it's a bug fix - `#XXX`);

* The body must contain the information about your branch
  (`git://github.com/USERNAME/symfony.git BRANCH_NAME`);

* The body must then describe what the patch does (reference a ticket, or
  copy and paste the commit message).

Based on the feedback, you might need to rework your patch. Before
re-submitting the patch, rebase with master, don't merge; and force the push
to the origin:

.. code-block:: bash

    $ git push -f origin BRANCH_NAME

.. _ProGit: http://progit.org/
.. _Github: https://github.com/signup/free
.. _Symfony2 repository: http://www.github.com/fabpot/symfony
.. _dev mailing-list: http://groups.google.com/group/symfony-devs
