Symfony Docs Maintainer Guide
=============================

The `symfony/symfony-docs`_ repository stores the Symfony project documentation
and is managed by the `Symfony Docs team`_. This article explains in detail some
of those management tasks, so it's only useful for maintainers and not regular
readers or Symfony developers.

Reviewing Pull Requests
-----------------------

All the recommendations of the `Symfony's respectful review comments`_ apply,
but there are extra things to keep in mind for maintainers:

* Always be nice in all interactions with all contributors.
* Be extra-patient with new contributors (GitHub shows a special badge for them).
* Don't assume that contributors know what you think is obvious (e.g. lots of
  them don't know what to "squash commits" means).
* Don't use acronyms like IMO, IIRC, etc. or complex English words (most
  contributors are not native in English and it's intimidating for them).
* Never engage in a heated discussion. Lock it right away using GitHub.
* Never discuss non-tech issues. Some PRs are related to our Diversity initiative
  and some people always try to drag you into politics. Never engage in that and
  lock the issue/PR as off-topic on GitHub.

Fixing Minor Issues Yourself
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It's common for new contributors to make lots of minor mistakes in the syntax
of the RST format used in the docs. It's also common for non English speakers to
make minor typos.

Even if your intention is good, if you add lots of comments when reviewing a
first contribution, that person will probably not contribute again. It's better
to fix the minor errors and typos yourself while merging. If that person
contributes again, it's OK to mention some of the minor issues to educate them.

.. code-block:: terminal

    $ gh merge 11059

      Working on symfony/symfony-docs (branch 6.2)
      Merging Pull Request 11059: dmaicher/patch-3

      ...

      # This is important!! Say NO to push the changes now
      Push the changes now? (Y/n) n
      Now, push with: git push gh "6.2" refs/notes/github-comments

      # Now, open your editor and make the needed changes ...

      $ git commit -a
      # Use "Minor reword", "Minor tweak", etc. as the commit message

      # now run the 'push' command shown above by 'gh' (it's different each time)
      $ git push gh "6.2" refs/notes/github-comments

Merging Pull Requests
---------------------

Technical Requirements
~~~~~~~~~~~~~~~~~~~~~~

* `Git`_ installed and properly configured.
* ``gh`` tool fully installed according to its installation instructions
  (GitHub token configured, Git remote configured, etc.)
  This is a proprietary CLI tool which only Symfony team members have access to.
* Some previous Git experience, specially merging pull requests.

First Setup
~~~~~~~~~~~

First, fork the <https://github.com/symfony/symfony-docs> using the GitHub web
interface. Then:

.. code-block:: terminal

    # Clone your fork
    $ git clone https://github.com/<YOUR-NAME>/symfony-docs.git

    $ cd symfony-docs/

    # Add the original repo as 'upstream' remote
    $ git remote add upstream https://github.com/symfony/symfony-docs

    # Add the original repo as 'gh' remote (needed for the 'gh' tool)
    $ git remote add gh https://github.com/symfony/symfony-docs

    # Configure 'gh' in Git as the remote used by the 'gh' tool
    $ git config gh.remote gh

Merging Process
~~~~~~~~~~~~~~~

At first, it's common to make mistakes and merge things badly. Don't worry. This
has happened to all of us and we've always been able to recover from any mistake.

Step 1: Select the right branch to merge
........................................

PRs must be merged in the oldest maintained branch where they are applicable:

* Here you can find the currently maintained branches: https://symfony.com/roadmap.
* Typos and old undocumented features are merged into the oldest maintained branch.
* New features are merged into the branch where they were introduced. This
  usually means ``master``. And don't forget to check that new feature includes
  the ``versionadded`` directive.

It's very common for contributors (specially newcomers) to select the wrong
branch for their PRs, so we must always check if the change should go to the
proposed branch or not.

If the branch is wrong, there's no need to ask the contributor to rebase. The
``gh`` tool can do that for us.

Step 2: Merge the pull request
..............................

Never use GitHub's web interface (or desktop clients) to merge PRs or to solve
merge conflicts. Always use the ``gh`` tool for anything related to merges.

We require two approval votes from team members before merging a PR, except if
it's a typo, a small change or clearly an error.

If a PR contains lots of commits, there's no need to ask the contributor to
squash them. The ``gh`` tool does that automatically. The only exceptions are
when commits are made by more than one person and when there's a merge commit.
``gh`` can't squash commits in those cases, so it's better to ask to the
original contributor.

.. code-block:: terminal

    $ cd symfony-docs/

    # make sure that your local branch is updated
    $ git checkout 4.4
    $ git fetch upstream
    $ git merge upstream/4.4

    # merge any PR passing its GitHub number as argument
    $ gh merge 11159

    # the gh tool will ask you some questions...

    # push your changes (you can merge several PRs and push once at the end)
    $ git push origin
    $ git push upstream

It's common to have to change the branch where a PR is merged. Instead of asking
the contributors to rebase their PRs, the "gh" tool can change the branch with
the ``-s`` option:

.. code-block:: terminal

    # e.g. this PR was sent against 'master', but it's merged in '4.4'
    $ gh merge 11160 -s 4.4

Sometimes, when changing the branch, you may face rebase issues, but they are
usually simple to fix:

.. code-block:: terminal

    $ gh merge 11160 -s 4.4

      ...

      Unable to rebase the patch for <comment>pull/11183</comment>
      The command "'git' 'rebase' '--onto' '4.4' '5.0' 'pull/11160'" failed.
      Exit Code: 128(Invalid exit argument)

      [...]
      Auto-merging reference/forms/types/entity.rst
      CONFLICT (content): Merge conflict in reference/forms/types/entity.rst
      Patch failed at 0001 Update entity.rst
      The copy of the patch that failed is found in: .git/rebase-apply/patch

    # Now, fix all the conflicts using your editor

    # Add the modified files and continue the rebase
    $ git add reference/forms/types/entity.rst ...
    $ git rebase --continue

    # Lastly, re-run the exact same original command that resulted in a conflict
    # There's no need to change the branch or do anything else.
    $ gh merge 11160 -s 4.4

      The previous run had some conflicts. Do you want to resume the merge? (Y/n)

Later in this article you can find a troubleshooting section for the errors that
you will usually face while merging.

Step 3: Merge it into the other branches
........................................

If a PR has not been merged in ``master``, you must merge it up into all the
maintained branches until ``master``. Imagine that you are merging a PR against
``4.4`` and the maintained branches are ``4.4``, ``5.0`` and ``master``:

.. code-block:: terminal

    $ git fetch upstream

    $ git checkout 4.4
    $ git merge upstream/4.4

    $ gh merge 11159
    $ git push origin
    $ git push upstream

    $ git checkout 5.0
    $ git merge upstream/5.0
    $ git merge --log 4.4
    # here you can face several errors explained later
    $ git push origin
    $ git push upstream

    $ git checkout master
    $ git merge upstream/master
    $ git merge --log 5.0
    $ git push origin
    $ git push upstream

.. tip::

    If you followed the full ``gh`` installation instructions you can remove the
    ``--log`` option in the above commands.

.. tip::

    When the support of a Symfony branch ends, it's recommended to delete your
    local branch to avoid merging in it unawarely:

    .. code-block:: terminal

        # if Symfony 3.3 goes out of maintenance today, delete your local branch
        $ git branch -D 3.3

Troubleshooting
~~~~~~~~~~~~~~~

Wrong merge of your local branch
................................

When updating your local branches before merging:

.. code-block:: terminal

    $ git fetch upstream
    $ git checkout 4.4
    $ git merge upstream/4.4

It's possible that you merge a wrong upstream branch unawarely. It's usually
easy to spot because you'll see lots of conflicts:

.. code-block:: terminal

    # DON'T DO THIS! It's a wrong branch merge
    $ git checkout 4.4
    $ git merge upstream/5.0

As long as you don't push this wrong merge, there's no problem. Delete your
local branch and check it out again:

.. code-block:: terminal

    $ git checkout master
    $ git branch -D 4.4
    $ git checkout 4.4 upstream/4.4

If you did push the wrong branch merge, ask for help in the documentation
mergers chat and we'll help solve the problem.

Solving merge conflicts
.......................

When merging things to upper branches, most of the times you'll see conflicts:

.. code-block:: terminal

    $ git checkout 5.0
    $ git merge upstream/5.0
    $ git merge --log 4.4

      Auto-merging security/entity_provider.rst
      Auto-merging logging/monolog_console.rst
      Auto-merging form/dynamic_form_modification.rst
      Auto-merging components/phpunit_bridge.rst
      CONFLICT (content): Merge conflict in components/phpunit_bridge.rst
      Automatic merge failed; fix conflicts and then commit the result.

Solve the conflicts with your editor (look for occurrences of ``<<<<``, which is
the marker used by Git for conflicts) and then do this:

.. code-block:: terminal

    # add all the conflicting files that you fixed
    $ git add components/phpunit_bridge.rst
    $ git commit -a
    $ git push origin
    $ git push upstream

.. tip::

    When there are lots of conflicts, look for ``<<<<<`` with your editor in all
    docs before committing the changes. It's common to forget about some of them.
    If you prefer, you can run this too: ``git grep --cached "<<<<<"``.

Merging deleted files
.....................

A common cause of conflict when merging PRs into upper branches are files which
were modified by the PR but no longer exist in newer branches:

.. code-block:: terminal

    $ git checkout 5.0
    $ git merge upstream/5.0
    $ git merge --log 4.4

      Auto-merging translation/debug.rst
      CONFLICT (modify/delete): service_container/scopes.rst deleted in HEAD and
      modified in 4.4. Version 4.4 of service_container/scopes.rst left in tree.
      Auto-merging service_container.rst

If the contents of the deleted file were moved to a different file in newer
branches, redo the changes in the new file. Then, delete the file that Git left
in the tree as follows:

.. code-block:: terminal

    # delete all the conflicting files that no longer exist in this branch
    $ git rm service_container/scopes.rst
    $ git commit -a
    $ git push origin
    $ git push upstream

Merging in the wrong branch
...........................

A Pull Request was made against ``5.x`` but it should be merged in ``5.1`` and you
forgot to merge as ``gh merge NNNNN -s 5.1`` to change the merge branch. Solution:

.. code-block:: terminal

    $ git checkout 5.1
    $ git cherry-pick <SHA OF YOUR MERGE COMMIT> -m 1
    $ git checkout 5.x
    $ git revert <SHA OF YOUR MERGE COMMIT> -m 1
    # now continue with the normal "upmerging"
    $ git checkout 5.2
    $ git merge 5.1
    $ ...

Merging while the target branch changed
.......................................

Sometimes, someone else merges a PR in ``5.x`` at the same time as you are
doing it. In these cases, ``gh merge ...`` fails to push. Solve this by
resetting your local branch and restarting the merge:

.. code-block:: terminal

    $ gh merge ...
    # this failed

    # fetch the updated 5.x branch from GitHub
    $ git fetch upstream
    $ git checkout 5.x
    $ git reset --hard upstream/5.x

    # restart the merge
    $ gh merge ...

.. _`symfony/symfony-docs`: https://github.com/symfony/symfony-docs
.. _`Symfony Docs team`: https://github.com/orgs/symfony/teams/team-symfony-docs
.. _`Symfony's respectful review comments`: https://symfony.com/doc/current/contributing/community/review-comments.html
.. _`Git`: https://git-scm.com/
