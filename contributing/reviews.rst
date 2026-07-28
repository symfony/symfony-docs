Reviewing Issues and Pull Requests
==================================

Symfony is an open source project driven by a large community. If you don't feel
ready to contribute code or documentation yet, **reviewing issues and pull
requests** is a great way to get involved. It works the same whether the change
touches code or documentation, and **anyone with some basic familiarity with
Symfony can do it**, you don't need to be an expert. People who "triage" issues
are the backbone of Symfony's success!

.. tip::

    **Quick reference**

    Pick an item labeled `Needs Review`_, check that it's complete, reproduce it
    (or read the change), then leave a friendly comment with a
    ``Status: <status>`` line to update its label. Always keep your comments
    respectful (see `Writing Respectful Comments`_ below).

Why Reviewing Is Important
--------------------------

Community reviews are essential for the development of Symfony: there are many
more pull requests and bug reports than there are core team members to review,
fix and merge them. On the `Symfony issue tracker`_ you can find many items in a
`Needs Review`_ status:

* **Bug reports** need to be checked for completeness: is any important
  information missing? Can the bug be reproduced?
* **Pull requests** contain a code or documentation change. Reviews ensure the
  change is correct, complete, covered by tests (for code) and doesn't introduce
  new problems.

Before You Start
----------------

Symfony uses `GitHub`_ to manage issues and pull requests, so you need to
`create a GitHub account`_ and log in.

Remember that you are looking at the result of someone else's hard work. A good
review thanks the contributor, points out what was done well, explains what
should be improved and suggests a next step. Before commenting, read the
`Writing Respectful Comments`_ guidelines below.

Reviewing a Bug Report
----------------------

A good way to get started is to pick a bug report from the
`bug reports in need of review`_ and follow these steps:

#. **Is the report complete?**

   Good bug reports link to a "reproduction project" (created with the
   `Symfony skeleton`_) that reproduces the bug. If there's none, the report
   should at least contain enough information and code samples to reproduce it.

#. **Reproduce the bug**

   Download the reproduction project and test whether the bug happens on your
   system. If the reporter didn't provide one, try to create it yourself.

#. **Update the issue status**

   Add a comment to the report. **Thank the reporter**, and include a
   ``Status: <status>`` line to trigger the `Carson Bot`_, which updates the
   issue label. Possible statuses:

   **Needs Work** if the report lacks enough information to reproduce the bug
   (explain what's missing);

   **Works for me** if it has enough information but works on your system, or if
   it's a feature request and not a bug (give a short explanation);

   **Reviewed** if you can reproduce the bug (include the link to your
   reproduction project, if you created one).

.. topic:: Example

    A sample comment for a bug report that could be reproduced:

    .. code-block:: text

        Thank you @weaverryan for creating this bug report! This indeed looks
        like a bug. I reproduced it in the "kernel-bug" branch of
        https://github.com/webmozart/some-project.

        Status: Reviewed

Reviewing a Pull Request
------------------------

The process is similar to reviewing a bug report, but usually takes a little
longer since you need to understand the change. **It's okay to do partial
reviews!** If you do, comment how far you got and leave the PR in the "Needs
Review" state.

Pick a pull request from the `PRs in need of review`_ and follow these steps:

#. **Is the PR complete?**

   Every pull request must contain a header with basic information about the
   change. You can find the template in the
   :ref:`Contribution Guidelines <contributing-code-pull-request>`.

#. **Is the base branch correct?**

   GitHub displays the branch a PR is based on, below its title:

   * Bug fixes should target the oldest maintained version that contains the bug
     (check the :doc:`release schedule </contributing/code/releases>`);
   * New features should target the current development version (check the
     `Symfony Roadmap`_).

#. **Review the change**

   For a **documentation** pull request, check that the prose follows the
   :doc:`documentation standards </contributing/documentation/standards>`, that
   code examples are correct, and build the docs locally to catch syntax errors.

   For a **code** pull request, read the code and check it against these
   criteria:

   * Does the code address the issue the PR is intended to fix/implement, and
     stay within scope?
   * Does it contain automated tests covering the relevant edge cases?
   * Does it contain enough comments to understand the code?
   * Does it break backward compatibility or add deprecations? If so, does the
     PR header say so, does the code use ``trigger_deprecation()`` and is
     everything documented in the latest ``UPGRADE-X.X.md`` file (with
     "Before"/"After" upgrade instructions)?

   .. note::

       Eventually, some of these aspects will be checked automatically.

#. **Test the change** (code PRs)

   Reproduce the original problem on a project created with the
   `Symfony skeleton`_, then check whether the PR fixes it. Check out the PR in
   a local clone of Symfony (the quickest way is the `GitHub CLI`_; replace
   ``<ID>`` with the PR number) and run the ``link`` script to make your project
   use that code instead of the one in its ``vendor/`` directory (see
   :ref:`testing your changes in a real project
   <test-your-changes-in-a-real-project>`):

   .. code-block:: terminal

       $ git clone https://github.com/symfony/symfony.git
       $ cd symfony
       $ gh pr checkout <ID>
       $ php link /path/to/your/reproduction-project

   Now run your reproduction project again to verify that the problem is fixed.

#. **Update the PR status**

   Add a comment to the PR. **Thank the contributor**, and include a
   ``Status: <status>`` line to trigger the `Carson Bot`_:

   **Needs Work** if the PR is not ready to be merged (explain the issues you
   found);

   **Reviewed** if the PR satisfies all the checks above. A core team member will
   then decide whether it can be merged or needs further work.

.. topic:: Example

    A sample comment for a PR that is not yet ready for merge:

    .. code-block:: text

        Thank you @weaverryan for working on this! It seems that your test
        cases don't cover the cases when the counter is zero or smaller.
        Could you please add some tests for that?

        Status: Needs Work

Writing Respectful Comments
---------------------------

Before you leave a comment, take a step back and think: is what you are about to
say actually what you intend? Communicating over the Internet with nothing but
text is hard, especially in a world-wide community of people with different
ideas, opinions and backgrounds. Not everyone speaks English natively, and some
may have had a bad experience contributing to other projects.

.. tip::

    This guide is not about asking you to "conform" or give up your ideas and
    opinions; it's about communicating better and keeping the Symfony community a
    welcoming place. **You are free to disagree with someone's opinions, but
    don't be disrespectful.**

Many programming decisions are opinions. Discuss trade-offs, say which you
prefer, and reach a resolution quickly. It's not about being right or wrong, but
using what works.

Tone of Voice
~~~~~~~~~~~~~

We don't expect you to be formal or to write error-free English. Don't swear
and be respectful to others. Don't reply in anger or with an aggressive
tone: take a deep breath, count to 10, and try to *clearly* explain the problem
you found.

Inclusive Language
~~~~~~~~~~~~~~~~~~

To be inclusive to a wide group of people, use pronouns that don't suggest a
particular gender: unless someone has stated their pronouns, use "they"/"them"
instead of "he"/"she", etc. Avoid wording that may be excluding, needlessly
gendered, racially motivated or that singles out a group. For example, prefer
"folks", "team" or "everyone" over "guys", "ladies", etc.

Giving Positive Feedback
~~~~~~~~~~~~~~~~~~~~~~~~

While reviewing you may run into suggestions that don't reflect your ideas, or
that are simply wrong. Before you comment, consider the time the author spent and
how your response would make them feel. Did you correctly understand their
intention, or are you making assumptions? Be explicit, and avoid terms that refer
to personal traits ("dumb", "stupid"). Assume everyone is intelligent and
well-meaning.

.. tip::

    Good questions avoid judgment and assumptions. Ask for clarification, suggest
    an alternative, or explain *why* you disagree:

    * ``This looks wrong. Are you sure it's correct?``
    * ``What do you think of "RequestFactory" instead of "RequestCreator"?``

Don't use hyperbole ("always", "never", "worst", "horrible"). Explain *why*:

* **Don't:** *"I don't like how you wrote this code"*; no clear explanation.
* **Better:** *"I find this hard to read because of the many nested if
  statements. Could you make it more readable, e.g. by encapsulating some details
  or adding comments to explain the logic?"*

**Examples of valid reasons to reject a change:**

* "We tried that in the past (link to the relevant PR) but had to revert it for
  XXX reason.";
* "That change would introduce too many merge conflicts when merging up Symfony
  branches.";
* "I profiled this change and it hurts performance significantly" (if you don't
  profile, it's an opinion);
* "This would require a lot of code and changes for a feature that doesn't look
  important, which could hurt maintenance in the future."

Asking for Changes
~~~~~~~~~~~~~~~~~~

Understand that the author already spent time on the change, and that asking for
(small) changes may be misread as a personal attack, *especially for a first
time contributor*. Be thankful for their work, stay positive and help them make
the contribution a great one. Use words like "Please", "Thank you" and "Could
you" instead of making demands:

* "Thank you for your work so far. I left some suggestions to make the code more
  readable.";
* "Your change has some coding-style problems, could you fix these before we
  merge? Thank you!"

Saying "Thank you" may not seem like much, but it makes others feel welcome.

Preventing Escalations
~~~~~~~~~~~~~~~~~~~~~~

Sometimes people get defensive when they receive feedback. In that case, try to
approach the discussion differently to avoid escalating further. If you'd like
someone to mediate, join the ``#contribs`` channel on the `Symfony Slack`_.

Using Humor
~~~~~~~~~~~

Keep it real and friendly. **Don't use sarcasm on a serious topic**, and don't
marginalize someone's problem (``Well I guess that's not supposed to happen? 😆``).
Even if an explanation seems "inviting to joke about it", the problem is real to
that person; making jokes only makes them *feel stupid*. Instead, try to discover
the actual problem.

Final Words
~~~~~~~~~~~

Don't feel bad if you "failed" to follow these tips. As long as your intentions
were good and you didn't offend anyone, you can explain that you misunderstood or
didn't mean to. But don't apologize "just because": an apology that isn't really
meant costs you credibility. *Do unto others as you would have them do unto you.*

.. _GitHub: https://github.com
.. _`GitHub CLI`: https://cli.github.com/
.. _`Symfony issue tracker`: https://github.com/symfony/symfony/issues
.. _`Symfony skeleton`: https://github.com/symfony/skeleton
.. _`create a GitHub account`: https://help.github.com/github/getting-started-with-github/signing-up-for-a-new-github-account
.. _`bug reports in need of review`: https://github.com/symfony/symfony/issues?utf8=%E2%9C%93&q=is%3Aopen+is%3Aissue+label%3A%22Bug%22+label%3A%22Status%3A+Needs+Review%22+
.. _`PRs in need of review`: https://github.com/symfony/symfony/pulls?q=is%3Aopen+is%3Apr+label%3A%22Status%3A+Needs+Review%22
.. _`Symfony Roadmap`: https://symfony.com/releases
.. _`Carson Bot`: https://github.com/carsonbot/carsonbot
.. _`Needs Review`: https://github.com/symfony/symfony/labels/Status%3A%20Needs%20Review
.. _`Symfony Slack`: https://symfony.com/slack
