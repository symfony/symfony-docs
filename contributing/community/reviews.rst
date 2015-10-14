Community Reviews
=================

Symfony is an open-source project driven by a large community. If you don't feel
ready to contribute code or patches, reviewing issues and pull requests (PRs)
can be a great start to get involved and give back. In fact, people who "triage"
issues are the backbone to Symfony's success!

Why Reviewing Is Important
--------------------------

Community reviews are essential for the development of the Symfony framework,
since there are many more pull requests and bug reports than there are members
in the Symfony core team to review, fix and merge them.

On the `Symfony issue tracker`_, you can find many items in a `Needs Review`_
status:

* **Bug Reports**: Bug reports need to be checked for completeness.
  Is any important information missing? Can the bug be *easily* reproduced?

* **Pull Requests**: Pull requests contain code that fixes a bug or implements
  new functionality. Reviews of pull requests ensure that they are implemented
  properly, are covered by test cases, don't introduce new bugs and maintain
  backwards compatibility.

Note that **anyone who has some basic familiarity with Symfony and PHP can
review bug reports and pull requests**. You don't need to be an expert to help.

Be Constructive
---------------

Before you begin, remember that you are looking at the result of someone else's
hard work. A good review comment thanks the contributor for their work,
identifies what was done well, identifies what should be improved and suggests a
next step.

Create a GitHub Account
-----------------------

Symfony uses GitHub_ to manage bug reports and pull requests. If you want to
do reviews, you need to `create a GitHub account`_ and log in.

The Bug Report Review Process
-----------------------------

A good way to get started with reviewing is to pick a bug report from the
`bug reports in need of review`_.

The steps for the review are:

#. **Is the Report Complete?**

   Good bug reports contain a link to a fork of the `Symfony Standard Edition`_
   (the "reproduction project") that reproduces the bug. If it doesn't, the
   report should at least contain enough information and code samples to
   reproduce the bug.

#. **Reproduce the Bug**

   Download the reproduction project and test whether the bug can be reproduced
   on your system. If the reporter did not provide a reproduction project,
   create one by forking_ the `Symfony Standard Edition`_.

#. **Update the Issue Status**

   At last, add a comment to the bug report. **Thank the reporter for reporting
   the bug**. Include the line ``Status: <status>`` in your comment to trigger
   our `Carson Bot`_ which updates the status label of the issue. You can set
   the status to one of the following:

   **Needs Work** If the bug *does not* contain enough information to be
   reproduced, explain what information is missing and move the report to this
   status.

   **Works for me** If the bug *does* contain enough information to be
   reproduced but works on your system, or if the reported bug is a feature and
   not a bug, provide a short explanation and move the report to this status.

   **Reviewed** If you can reproduce the bug, move the report to this status.
   If you created a reproduction project, include the link to the project in
   your comment.

.. topic:: Example

    Here is a sample comment for a bug report that could be reproduced:

    .. code-block:: text

        Thank you @weaverryan for creating this bug report! This indeed looks
        like a bug. I reproduced the bug in the "kernel-bug" branch of
        https://github.com/webmozart/symfony-standard.

        Status: Reviewed

The Pull Request Review Process
-------------------------------

The process for reviewing pull requests (PRs) is similar to the one for bug
reports. Reviews of pull requests usually take a little longer since you need
to understand the functionality that has been fixed or added and find out
whether the implementation is complete.

It is okay to do partial reviews! If you do a partial review, comment how far
you got and leave the PR in "Needs Review" state.

Pick a pull request from the `PRs in need of review`_ and follow these steps:

#. **Is the PR Complete**?

   Every pull request must contain a header that gives some basic information
   about the PR. You can find the template for that header in the
   :ref:`Contribution Guidelines <contributing-code-pull-request>`.

#. **Is the Base Branch Correct?**

   GitHub displays the branch that a PR is based on below the title of the
   pull request. Is that branch correct?

   * Bugs should be fixed in the oldest, maintained version that contains the
     bug. Check :doc:`Symfony's Release Schedule <releases>` to find the oldest
     currently supported version.

   * New features should always be added to the current development version.
     Check the `Symfony Roadmap`_ to find the current development version.

#. **Reproduce the Problem**

   Read the issue that the pull request is supposed to fix. Reproduce the
   problem on a clean `Symfony Standard Edition`_ project and try to understand
   why it exists. If the linked issue already contains such a project, install
   it and run it on your system.

#. **Review the Code**

   Read the code of the pull request and check it against some common criteria:

   * Does the code address the issue the PR is intended to fix/implement?
   * Does the PR stay within scope to address *only* that issue?
   * Does the PR contain automated tests? Do those tests cover all relevant
     edge cases?
   * Does the PR contain sufficient comments to easily understand its code?
   * Does the code break backwards compatibility? If yes, does the PR header say
     so?
   * Does the PR contain deprecations? If yes, does the PR header say so? Does
     the code contain ``trigger_error()`` statements for all deprecated
     features?
   * Are all deprecations and backwards compatibility breaks documented in the
     latest UPGRADE-X.X.md file? Do those explanations contain "Before"/"After"
     examples with clear upgrade instructions?

   .. note::

       Eventually, some of these aspects will be checked automatically.

#. **Test the Code**

   Take your project from step 3 and test whether the PR works properly.
   Replace the Symfony project in the ``vendor`` directory by the code in the
   PR by running the following Git commands. Insert the PR ID (that's the number
   after the ``#`` in the PR title) for the ``<ID>`` placeholders:

   .. code-block:: text

       $ cd vendor/symfony/symfony
       $ git fetch origin pull/<ID>/head:pr<ID>
       $ git checkout pr<ID>

   For example:

   .. code-block:: text

       $ git fetch origin pull/15723/head:pr15723
       $ git checkout pr15723

   Now you can :doc:`test the project </contributing/code/tests>` against
   the code in the PR.

#. **Update the PR Status**

   At last, add a comment to the PR. **Thank the contributor for working on the
   PR**. Include the line ``Status: <status>`` in your comment to trigger our
   `Carson Bot`_ which updates the status label of the issue. You can set the
   status to one of the following:

   **Needs Work** If the PR is not yet ready to be merged, explain the issues
   that you found and move it to this status.

   **Reviewed** If the PR satisfies all the checks above, move it to this
   status. A core contributor will soon look at the PR and decide whether it can
   be merged or needs further work.

.. topic:: Example

    Here is a sample comment for a PR that is not yet ready for merge:

    .. code-block:: text

        Thank you @weaverryan for working on this! It seems that your test
        cases don't cover the cases when the counter is zero or smaller.
        Could you please add some tests for that?

        Status: Needs Work

.. _GitHub: https://github.com
.. _Symfony issue tracker: https://github.com/symfony/symfony/issues
.. _Symfony Standard Edition: https://github.com/symfony/symfony-standard
.. _create a GitHub account: https://help.github.com/articles/signing-up-for-a-new-github-account/
.. _forking: https://help.github.com/articles/fork-a-repo/
.. _bug reports in need of review: https://github.com/symfony/symfony/issues?utf8=%E2%9C%93&q=is%3Aopen+is%3Aissue+label%3A%22Bug%22+label%3A%22Status%3A+Needs+Review%22+
.. _PRs in need of review: https://github.com/symfony/symfony/issues?utf8=%E2%9C%93&q=is%3Aopen+is%3Apr+label%3A%22Status%3A+Needs+Review%22+
.. _Contribution Guidelines: https://github.com/symfony/symfony/blob/master/CONTRIBUTING.md
.. _Symfony's Release Schedule: http://symfony.com/doc/current/contributing/community/releases.html#schedule
.. _Symfony Roadmap: https://symfony.com/roadmap
.. _Carson Bot: https://github.com/carsonbot/carsonbot
.. _`Needs Review`: https://github.com/symfony/symfony/labels/Status%3A%20Needs%20Review
