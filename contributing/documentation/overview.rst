Contributing to the Documentation
=================================

One of the essential principles of the Symfony project is that **documentation is
as important as code**. That's why we dedicate a great amount of resources to
document new features and to maintain updated the rest of the documentation.

More than 800 developers all around the world have contributed to Symfony
documentation, and we are glad that you are considering joining this big family.
This guide will explain everything you need to contribute to Symfony documentation.

Before Your First Contribution
------------------------------

**Before contributing**, you should consider the following:

* Symfony documentation is written using `reStructuredText`_ markup language.
  If you are not familiar with this format, read :doc:`this article </contributing/documentation/format>`
  for a quick overview of its basic features.
* Symfony documentation is hosted on `GitHub`_. You'll need a GitHub user account
  to contribute documentation.
* Symfony documentation is published under a
  :doc:`Creative Commons BY-SA 3.0 License </contributing/documentation/license>`
  and all your contributions will implicitly adhere to that license.

Your First Documentation Contribution
-------------------------------------

In this section you'll learn how to contribute to Symfony documentation for the
first time. Next section will explain the abbreviated process to follow for the
next contributions.

Let's imagine that you want to improve the installation chapter of the Symfony
book. In order to do those changes, follow these steps:

**Step 1.** Go to the official Symfony documentation repository located at
`github.com/symfony/symfony-docs <https://github.com/symfony/symfony-docs>`_
and `fork the repository`_ to your personal account. This is only needed the
first time you contribute to Symfony.

**Step 2.** **Clone** the forked repository to your local machine (in this
example we'll use the ``projects/symfony-docs/`` directory to store the
documentation; change this value accordingly):

.. code-block:: bash

    $ cd projects/
    $ git clone git://github.com/<YOUR GITHUB USERNAME>/symfony-docs.git

**Step 3.** Switch to the **oldest maintained branch** before making any change.
Nowadays this is the ``2.3`` branch:

.. code-block:: bash

    $ cd symfony-docs/
    $ git checkout 2.3

If you were instead documenting a new feature, switch to the first Symfony
version which included it: ``2.5``, ``2.6``, etc.

**Step 4.** Create a dedicated **new branch** for your changes. This greatly
simplifies the work of reviewing and merging your changes. Use a short and
memorable name for the new branch:

.. code-block:: bash

    $ git checkout -b improve_install_chapter

**Step 5.** Now make your changes in the documentation. Add, tweak, reword and
even remove any content, but make sure that you comply with the doc:`standards`.

**Step 6.** **Push** the changes to your forked repository:

.. code-block:: bash

    $ git commit book/installation.rst
    $ git push

**Step 7.** Everything is now ready to initiate a **pull request**. Go to your
forked repository at ``https//github.com/<YOUR GITHUB USERNAME>/symfony-docs``
and click on the ``Pull Requests`` link located at the sidebar.

Then, click on the big ``New pull request`` button. As GitHub cannot guess the
exact changes that you want to propose, select the appropriate branches where
changes should be applied:

.. image:: /images/docs-pull-request-change-base.png
   :align: center

In this example, the **base repository** should be ``symfony/symfony-docs`` and
the **base branch** should be the ``2.3``, which is the branch that you selected
to base your changes on. The **compare repository** should be your forked copy
of ``symfony-docs`` and the **compare branch** should be ``improve_install_chapter``,
which is the name that you selected to work on your changes.

**Step 8.** The last step is to prepare the **description** of the pull request.
To ensure that your work is reviewed quickly, you have to add the following table
at the beginning of your pull request description:

.. code-block:: text

    | Q             | A
    | ------------- | ---
    | Doc fix?      | [yes|no]
    | New docs?     | [yes|no] (PR # on symfony/symfony if applicable)
    | Applies to    | [Symfony version numbers this applies to]
    | Fixed tickets | [comma separated list of tickets fixed by the PR]

In this example, this table would look as follows:

.. code-block:: text

    | Q             | A
    | ------------- | ---
    | Doc fix?      | yes
    | New docs?     | no
    | Applies to    | 2.3+
    | Fixed tickets | #10575

**Step 9.** Now that you've successfully submitted your first contribution to the
Symfony documentation, **go and celebrate!**  The documentation managers will
carefully review your work in short time and they will let you know about any
required change.

In case you need to add or modify anything, there is no need to create a new
pull request. Just make sure that you are in the correct branch, make your
changes and push them:

.. code-block:: bash

    $ cd projects/symfony-docs/
    $ git checkout improve_install_chapter

    # do your changes ...

    $ git push

**Step 10.** After your pull request is eventually accepted and merged in the Symfony
documentation, you will be included in the `Symfony Documentation Contributors`_
list. Moreover, if you happen to have a `SensioLabsConnect`_ profile, we will
award you a cool `Symfony Documentation Badge`_.

Your Second Documentation Contribution
--------------------------------------

The first contribution took some time because you had to fork the repository,
learn how to write documentation, comply with the pull requests standards, etc.
The second contribution will be much easier, except for one detail: given the
furious update activity of the Symfony documentation repository, odds are that
your fork is now outdated comparing it with the official repository.

Solving this problem requires to `sync your fork`_ with the original repository.
To do so, execute first this command to tell git which is the original repository
from which you created the fork:

.. code-block:: bash

    $ cd projects/symfony-docs/
    $ git remote add upstream https://github.com/symfony/symfony-docs.git

Now you can **sync your fork** executing the following command:

.. code-block:: bash

    $ cd projects/symfony-docs/
    $ git fetch upstream
    $ git checkout master
    $ git merge upstream/master

Now you can proceed following the same steps explained in the previous section:

.. code-block:: bash

    # create a new branch to store your changes based on the 2.3 branch
    $ cd projects/symfony-docs/
    $ git checkout 2.3
    $ git checkout -b my_changes

    # do your changes ...

    # submit the changes to your forked repository
    $ git add xxx.rst     # (optional) only if this is a new content
    $ git commit xxx.rst
    $ git push

    # go to GitHub and create the Pull Request
    #
    # Include this table in the description:
    # | Q             | A
    # | ------------- | ---
    # | Doc fix?      | [yes|no]
    # | New docs?     | [yes|no] (PR # on symfony/symfony if applicable)
    # | Applies to    | [Symfony version numbers this applies to]
    # | Fixed tickets | [comma separated list of tickets fixed by the PR]

Your second contribution is now completed, so **go and celebrate again!**
You will also see how your ranking improves in the list of
`Symfony Documentation Contributors`_.

Your Next Documentation Contributions
-------------------------------------

Now that you've made two contributions to the Symfony documentation, you are
probably comfortable with all the Git-magic involved in the process. That's
why your next contributions would be much faster and easier. Here you can find
the complete steps to contribute to Symfony documentation, which you can use as
a **checklist**:

.. code-block:: bash

    # sync your fork with the official Symfony repository
    $ cd projects/symfony-docs/
    $ git fetch upstream
    $ git checkout master
    $ git merge upstream/master

    # create a new branch from the oldest maintained version
    $ git checkout 2.3
    $ git checkout -b my_changes

    # do your changes ...

    # add and commit your changes
    $ git add xxx.rst     # (optional) only if this is a new content
    $ git commit xxx.rst
    $ git push

    # go to GitHub and create the Pull Request
    #
    # Include this table in the description:
    # | Q             | A
    # | ------------- | ---
    # | Doc fix?      | [yes|no]
    # | New docs?     | [yes|no] (PR # on symfony/symfony if applicable)
    # | Applies to    | [Symfony version numbers this applies to]
    # | Fixed tickets | [comma separated list of tickets fixed by the PR]

    # (optional) make the changes requested by reviewers and commit them
    $ git commit xxx.rst
    $ git push

You guessed right: after all this hard work, it's **time to celebrate again!**

Frequently Asked Questions
--------------------------

Why Do my Changes so Long to Be Reviewed and/or Merged?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Please be patient. It can take from 15 minutes to several days before your pull
request can be fully reviewed. And after merging the changes, it could take again
from some minutes to several hours before your changes appear on the symfony.com
website.

What If I Want to Translate Some Documentation into my Language?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Read the dedicated :doc:`document </contributing/documentation/translations>`.

Why Should I Use the Oldest Maintained Branch Instead of the Master Branch?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Consistent with Symfony's source code, the documentation repository is split
into multiple branches, corresponding to the different versions of Symfony itself.
The master branch holds the documentation for the development branch of the code.

Unless you're documenting a feature that was introduced after Symfony 2.3,
your changes should always be based on the 2.3 branch. Documentation managers
will use the necessary Git-magic to also apply your changes to all the active
branches of the documentation.

What If I Want to Submit my Work without Fully Finishing It?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can do it. But please use one of these two prefixes to let reviewers know
which is the state of your work:

* ``[WIP]`` (Work in Progress) is used when you are not yet finished with your
  pull request, but you would like it to be reviewed. The pull request won't
  be merged until you say it is ready.

* ``[WCM]`` (Waiting Code Merge) is used when you're documenting a new feature
  or change that hasn't been accepted yet into the core code. The pull request
  will not be merged until it is merged in the core code (or closed if the
  change is rejected).

Would You Admit a Huge Pull Request with Lots of Changes?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sure! But before doing a huge change, it's probably a good idea to open an issue
in the Symfony Documentation repository to ask the managers if they agree with
your proposed changes. Otherwise, they could refuse your proposal after having
made all the work and you would have wasted a lot of time.

.. _`reStructuredText`: http://docutils.sourceforge.net/rst.html
.. _`GitHub`: https://github.com/
.. _`fork the repository`: https://help.github.com/articles/fork-a-repo
.. _`Symfony Documentation Contributors`: http://symfony.com/contributors/doc
.. _`SensioLabsConnect`: https://connect.sensiolabs.com/
.. _`Symfony Documentation Badge`: https://connect.sensiolabs.com/badge/36/symfony-documentation-contributor
.. _`sync your fork`: https://help.github.com/articles/syncing-a-fork
