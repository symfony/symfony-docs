Contributing to the Documentation
=================================

One of the essential principles of the Symfony project is that **documentation
is as important as code**. That's why a great amount of resources are dedicated
to documenting new features and to keeping the rest of the documentation
up-to-date.

More than 1,000 developers all around the world have contributed to Symfony's
documentation and we are glad that you are considering joining this big family.
This guide will explain everything you need to contribute to the Symfony
documentation.

Before Your First Contribution
------------------------------

**Before contributing**, you should consider the following:

* Symfony documentation is written using `reStructuredText`_ markup language.
  If you are not familiar with this format, read
  :doc:`this article </contributing/documentation/format>` for a quick overview
  of its basic features.
* Symfony documentation is hosted on `GitHub`_. You'll need a free GitHub user
  account to contribute to the documentation.
* Symfony documentation is published under a
  :doc:`Creative Commons BY-SA 3.0 License </contributing/documentation/license>`
  and all your contributions will implicitly adhere to that license.

.. _minor-changes-e-g-typos:

Fast Online Contributions
-------------------------

If you're making a relatively small change - like fixing a typo or rewording
something - the easiest way to contribute is directly on GitHub! You can do this
while you're reading the Symfony documentation.

**Step 1.** Click on the **edit this page** button on the upper right corner
and you'll be redirected to GitHub:

.. image:: /images/contributing/docs-github-edit-page.png

**Step 2.** Edit the contents, describe your changes and click on the
**Propose file change** button.

**Step 3.** GitHub will now create a branch and a commit for your changes
(forking the repository first if this is your first contribution) and it will
also display a preview of your changes:

.. image:: /images/contributing/docs-github-create-pr.png

If everything is correct, click on the **Create pull request** button.

**Step 4.** GitHub will display a new page where you can do some last-minute
changes to your pull request before creating it. For simple contributions, you
can safely ignore these options and just click on the **Create pull request**
button again.

**Congratulations!** You just created a pull request to the official Symfony
documentation! The community will now review your pull request and (possibly)
suggest tweaks.

If your contribution is large or if you prefer to work on your own computer,
keep reading this guide to learn an alternative way to send pull requests to the
Symfony Documentation.

Your First Documentation Contribution
-------------------------------------

In this section, you'll learn how to contribute to the Symfony documentation for
the first time. The next section will explain the shorter process you'll follow
in the future for every contribution after your first one.

Let's imagine that you want to improve the installation chapter of the Symfony
book. In order to make your changes, follow these steps:

**Step 1.** Go to the official Symfony documentation repository located at
`github.com/symfony/symfony-docs`_ and click on the **Fork** button to `fork the
repository`_ to your personal account. This is only needed the first time you
contribute to Symfony.

**Step 2.** **Clone** the forked repository to your local machine (this example
uses the ``projects/symfony-docs/`` directory to store the documentation; change
this value accordingly):

.. code-block:: bash

    $ cd projects/
    $ git clone git://github.com/<YOUR GITHUB USERNAME>/symfony-docs.git

**Step 3.** Add the original Symfony docs repository as a "Git remote" executing
this command:

.. code-block:: bash

    $ cd symfony-docs/
    $ git remote add upstream https://github.com/symfony/symfony-docs.git

If things went right, you'll see the following when listing the "remotes" of
your project:

.. code-block:: bash

    $ git remote -v
    origin  git@github.com:<YOUR GITHUB USERNAME>/symfony-docs.git (fetch)
    origin  git@github.com:<YOUR GITHUB USERNAME>/symfony-docs.git (push)
    upstream  https://github.com/symfony/symfony-docs.git (fetch)
    upstream  https://github.com/symfony/symfony-docs.git (push)

The purpose of this step is to allow you work simultaneously on the official
Symfony repository and on your own fork. You'll see this in action in a moment.

**Step 4.** Create a dedicated **new branch** for your changes. Use a short and
memorable name for the new branch (if you are fixing a reported issue, use
``fix_XXX`` as the branch name, where ``XXX`` is the number of the issue):

.. code-block:: bash

    $ git checkout -b improve_install_chapter upstream/2.3

In this example, the name of the branch is ``improve_install_chapter`` and the
``upstream/2.3`` value tells Git to create this branch based on the ``2.3``
branch of the ``upstream`` remote, which is the original Symfony Docs repository.

Fixes should always be based on the **oldest maintained branch** which contains
the error. Nowadays this is the ``2.3`` branch. If you are instead documenting a
new feature, switch to the first Symfony version that included it, e.g.
``upstream/3.1``. Not sure? That's ok! Just use the ``upstream/master`` branch.

**Step 5.** Now make your changes in the documentation. Add, tweak, reword and
even remove any content and do your best to comply with the
:doc:`/contributing/documentation/standards`. Then commit your changes!

.. code-block:: bash

    # if the modified content existed before
    $ git add book/installation.rst
    $ git commit book/installation.rst

**Step 6.** **Push** the changes to your forked repository:

.. code-block:: bash

    $ git push origin improve_install_chapter

The ``origin`` value is the name of the Git remote that corresponds to your
forked repository and ``improve_install_chapter`` is the name of the branch you
created previously.

**Step 7.** Everything is now ready to initiate a **pull request**. Go to your
forked repository at ``https//github.com/<YOUR GITHUB USERNAME>/symfony-docs``
and click on the **Pull Requests** link located in the sidebar.

Then, click on the big **New pull request** button. As GitHub cannot guess the
exact changes that you want to propose, select the appropriate branches where
changes should be applied:

.. image:: /images/contributing/docs-pull-request-change-base.png
   :align: center

In this example, the **base fork** should be ``symfony/symfony-docs`` and
the **base** branch should be the ``2.3``, which is the branch that you selected
to base your changes on. The **head fork** should be your forked copy
of ``symfony-docs`` and the **compare** branch should be ``improve_install_chapter``,
which is the name of the branch you created and where you made your changes.

.. _pull-request-format:

**Step 8.** The last step is to prepare the **description** of the pull request.
A short phrase or paragraph describing the proposed changes is enough to ensure
that your contribution can be reviewed.

**Step 9.** Now that you've successfully submitted your first contribution to
the Symfony documentation, **go and celebrate!**  The documentation managers
will carefully review your work in short time and they will let you know about
any required change.

In case you are asked to add or modify something, don't create a new pull
request. Instead, make sure that you are on the correct branch, make your
changes and push the new changes:

.. code-block:: bash

    $ cd projects/symfony-docs/
    $ git checkout improve_install_chapter

    # ... do your changes

    $ git push

**Step 10.** After your pull request is eventually accepted and merged in the
Symfony documentation, you will be included in the `Symfony Documentation
Contributors`_ list. Moreover, if you happen to have a `SensioLabsConnect`_
profile, you will get a cool `Symfony Documentation Badge`_.

Your Next Documentation Contributions
-------------------------------------

Check you out! You've made your first contribution to the Symfony documentation!
Somebody throw a party! Your first contribution took a little extra time because
you needed to learn a few standards and setup your computer. But from now on,
your contributions will be much easier to complete.

Here is a **checklist** of steps that will guide you through your next
contribution to the Symfony docs:

.. code-block:: bash

    # create a new branch based on the oldest maintained version
    $ cd projects/symfony-docs/
    $ git checkout -b my_changes upstream/2.3

    # ... do your changes

    # add and commit your changes
    $ git add xxx.rst     # (optional) only if this is a new content
    $ git commit xxx.rst
    $ git push origin my_changes

    # go to GitHub and create the Pull Request

    # (optional) make the changes requested by reviewers and commit them
    $ git commit xxx.rst
    $ git push

After completing your next contributions, also watch your ranking improve on
the list of `Symfony Documentation Contributors`_. You guessed right: after all
this hard work, it's **time to celebrate again!**

Review your changes
-------------------

Every GitHub Pull Request is automatically built and deployed by `Platform.sh`_
on a single environment that you can access on your browser to review your
changes.

.. image:: /images/contributing/docs-pull-request-platformsh.png
   :align: center
   :alt:   Platform.sh Pull Request Deployment

To access the `Platform.sh`_ environment URL, go to your Pull Request page on
GitHub, click on the **Show all checks** link and finally, click on the ``Details``
link displayed for Platform.sh service.

.. note::

    Only Pull Requests to maintained branches are automatically built by
    Platform.sh. Check the `roadmap`_ for maintained branches.

Frequently Asked Questions
--------------------------

Why Do my Changes Take so Long to Be Reviewed and/or Merged?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Please be patient. It can take up to several days before your pull request can
be fully reviewed. After merging the changes, it could take again several hours
before your changes appear on the symfony.com website.

Why Should I Use the Oldest Maintained Branch Instead of the Master Branch?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Consistent with Symfony's source code, the documentation repository is split
into multiple branches, corresponding to the different versions of Symfony itself.
The ``master`` branch holds the documentation for the development branch of
the code.

Unless you're documenting a feature that was introduced after Symfony 2.3,
your changes should always be based on the ``2.3`` branch. Documentation managers
will use the necessary Git-magic to also apply your changes to all the active
branches of the documentation.

What If I Want to Submit my Work without Fully Finishing It?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can do it. But please use one of these two prefixes to let reviewers know
about the state of your work:

* ``[WIP]`` (Work in Progress) is used when you are not yet finished with your
  pull request, but you would like it to be reviewed. The pull request won't
  be merged until you say it is ready.

* ``[WCM]`` (Waiting Code Merge) is used when you're documenting a new feature
  or change that hasn't been accepted yet into the core code. The pull request
  will not be merged until it is merged in the core code (or closed if the
  change is rejected).

Would You Accept a Huge Pull Request with Lots of Changes?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, make sure that the changes are somewhat related. Otherwise, please create
separate pull requests. Anyway, before submitting a huge change, it's probably a
good idea to open an issue in the Symfony Documentation repository to ask the
managers if they agree with your proposed changes. Otherwise, they could refuse
your proposal after you put all that hard work into making the changes. We
definitely don't want you to waste your time!

.. _`github.com/symfony/symfony-docs`: https://github.com/symfony/symfony-docs
.. _`reStructuredText`: http://docutils.sourceforge.net/rst.html
.. _`GitHub`: https://github.com/
.. _`fork the repository`: https://help.github.com/articles/fork-a-repo
.. _`Symfony Documentation Contributors`: https://symfony.com/contributors/doc
.. _`SensioLabsConnect`: https://connect.sensiolabs.com/
.. _`Symfony Documentation Badge`: https://connect.sensiolabs.com/badge/36/symfony-documentation-contributor
.. _`sync your fork`: https://help.github.com/articles/syncing-a-fork
.. _`Platform.sh`: https://platform.sh
.. _`roadmap`: https://symfony.com/roadmap
