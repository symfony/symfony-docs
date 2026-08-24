Contributing Code
=================

There are many ways to contribute to the Symfony code, listed here from the
quickest to the most involved: reporting a bug you found, fixing a bug, or
adding a new feature. This guide covers all of them.

.. admonition:: Screencast
    :class: screencast

    Prefer video? Check out the `Contributing Back To Symfony`_ screencast series.

.. _reporting-a-bug:

Reporting a Bug
---------------

The simplest way to contribute is to report a bug you found. It helps us make
Symfony better even if you don't fix it yourself.

.. warning::

    If you think you've found a **security issue**, don't report it publicly.
    Follow the :doc:`security procedure </contributing/code/security>` instead.

.. note::

    This is about **bugs**. If you have an idea for a **new feature**, discuss it
    first on the ``#contribs`` channel of the `Symfony Slack`_ or open an issue
    (ideally alongside a pull request) rather than just filing a request.

Before Reporting
~~~~~~~~~~~~~~~~

* Check the :doc:`documentation </index>` to make sure it's not a misuse of the
  framework;
* Ask on the ``#support`` channel of the `Symfony Slack`_ if you're not sure
  it's a bug.

Writing a Good Bug Report
~~~~~~~~~~~~~~~~~~~~~~~~~

Report the bug on the official `issue tracker`_ following these rules:

* Use the title to clearly describe the issue;
* Describe the steps to reproduce the bug with short code examples (a failing
  unit test is best);
* Give as much detail as possible about your environment (OS, PHP version,
  Symfony version, enabled extensions);
* If an exception was thrown, include its :ref:`stack trace <getting-a-stack-trace>`
  as **plain text** (never a screenshot, so search engines can index it).
  **Redact any sensitive information first**;
* If the bug is complex or spans several layers, add a
  :ref:`reproducer <reproducing-the-bug>`.

.. _reproducing-the-bug:

Reproducing the Bug
~~~~~~~~~~~~~~~~~~~

A *bug reproducer* is the minimum amount of code needed to trigger the bug. It
makes the difference between a bug that's fixed quickly and one that can't be
understood.

**For a component used outside the framework**, a small PHP script is enough::

    // first, run "composer require symfony/validator"
    require_once __DIR__.'/vendor/autoload.php';

    use Symfony\Component\Validator\Constraints;

    $wrongUrl = 'http://example.com/exploit.html?<script>alert(1);</script>';
    $urlValidator = new Constraints\UrlValidator();
    $urlConstraint = new Constraints\Url();

    // this should display an error, but it displays "null" instead
    var_dump($urlValidator->validate($wrongUrl, $urlConstraint));

**For a bug in the framework**, create a minimal project, push it to a new GitHub
repository and link it in your issue:

.. code-block:: terminal

    $ composer create-project symfony/skeleton bug_app

The key is to add **only** the code needed to reproduce the bug: don't copy your
whole application. Start from the feature you suspect (a route, a service, an
event listener...), add the smallest possible code that shows the bug, and use
the :doc:`Symfony CLI </setup/symfony_cli>` (``symfony server:start``) to check
it. Keep adding small changes until the bug appears.

.. _getting-a-stack-trace:

Getting a Stack Trace
~~~~~~~~~~~~~~~~~~~~~

A stack trace shows the trail of function calls that led to an exception. When
reporting an exception, always include the **plain text** stack trace.

.. tip::

    When reading a stack trace, focus first on the lines under ``src/`` (your
    code) rather than ``vendor/``, since third-party code is less likely to be
    the source of the bug. If several exceptions are shown, the most relevant one
    is usually the first (``exception [1/n]``). Report the bug to the library
    that throws it (for Symfony components, use ``composer home symfony/symfony``,
    which opens the package's repository URL or homepage in your browser).

In your Web Browser
...................

Symfony's exception page provides the **Stack Trace** in plain text, ready to
paste into a bug report (remember to remove sensitive data):

.. image:: /_images/contributing/code/stack-trace.gif
    :alt: The default Symfony exception page with the "Exceptions", "Logs" and "Stack Traces" tabs.
    :class: with-browser

In the Console
..............

Commands only show the exception message by default. Add ``--verbose`` to get the
full stack trace:

.. code-block:: terminal

    $ php bin/console --verbose your:command

For API Calls
.............

When the response isn't suitable for sharing, open the profiler to get a plain
text trace. Its URL is in the ``X-Debug-Token-Link`` response header:

.. code-block:: terminal

    $ curl --head http://localhost:8000/api/posts/1
    …
    X-Debug-Token-Link: http://localhost:8000/_profiler/110e1e

.. _step-1-setup-your-environment:

Set up your Environment
-----------------------

To fix a bug or work on a feature, set up a local copy of Symfony. You only need
to do this once.

Install **Git** and **PHP 8.2 or higher**. Then set your name and email in Git
(they appear in your commits):

.. code-block:: terminal

    $ git config --global user.name "Your Name"
    $ git config --global user.email you@example.com

.. tip::

    On Windows, make sure Git doesn't rewrite line endings when cloning:
    ``git config --global core.autocrlf input``.

Get the Source Code
~~~~~~~~~~~~~~~~~~~

#. Create a `GitHub`_ account and sign in.

#. `Fork the Symfony repository`_ (click the "Fork" button). This creates your
   own copy of Symfony where you can push your changes.

#. Clone your fork locally (this creates a ``symfony/`` directory):

   .. code-block:: terminal

       $ git clone git@github.com:YOUR_USERNAME/symfony.git

#. Add the original repository as a remote called ``upstream``. You'll use it to
   keep your fork up to date:

   .. code-block:: terminal

       $ cd symfony/
       $ git remote add upstream https://github.com/symfony/symfony.git

Now check that everything works by :ref:`running the test suite <running-symfony-tests>`.

.. _test-your-changes-in-a-real-project:

Test your Changes in a Real Project
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To try your Symfony changes inside an existing project, use the ``link`` script
included in the repository. It replaces the Symfony packages in your project's
``vendor/`` directory with symbolic links to your local Symfony clone:

.. code-block:: terminal

    $ cd symfony/
    $ php link /path/to/your/project

Make sure the project's dependencies are installed (``composer install``) before
running it. Use ``--copy`` if your environment can't resolve symlinks, and
``--rollback`` to restore the original dependencies when you're done.

Proposing a Change
------------------

.. tip::

    **Quick reference**

    .. code-block:: terminal

        # fork symfony/symfony on GitHub, then:
        $ git clone git@github.com:YOUR_USERNAME/symfony.git
        $ cd symfony
        $ git remote add upstream https://github.com/symfony/symfony.git
        $ git fetch upstream
        $ git checkout -b fix_XXX upstream/6.4   # bug fix: oldest maintained branch
        # ... change + add tests ...
        $ composer update                        # install test dependencies
        $ php ./phpunit src/Symfony/...          # run the relevant tests
        $ git commit -m "[Component] Describe the change"
        $ git push origin fix_XXX                # then open a PR against 6.4

    New features target the current development branch instead of ``6.4``.

A pull request (PR) is the way to send a bug fix or a new feature to Symfony.

Before working on a change, `search the existing issues and pull requests`_ to
see if someone already reported the topic or started a PR. If you have any
question during the process, ask on the ``#contribs`` channel of the
`Symfony Slack`_.

All the code you contribute must be :ref:`released under the MIT license <symfony-license>`.

Choose the Branch
~~~~~~~~~~~~~~~~~

.. include:: /contributing/_branch_note.rst.inc

Create a topic branch off the branch you chose. Use a descriptive name
(``fix_XXX``, where ``XXX`` is the issue number, is a good convention for bug
fixes):

.. code-block:: terminal

    $ git fetch upstream
    $ git checkout -b fix_XXX upstream/6.4

.. _work-on-your-patch:

Make your Change
~~~~~~~~~~~~~~~~

Work on the code and commit as much as you want, keeping these rules in mind:

* Follow the :doc:`coding standards </contributing/code/standards>` and
  :doc:`conventions </contributing/code/conventions>` (run PHP CS Fixer to fix
  code style automatically);
* Add tests that prove the bug is fixed or the feature works;
* Never break backward compatibility (see the :doc:`BC promise </contributing/code/bc>`);
* Don't edit files unrelated to your change (e.g. to fix coding standards)
  because it makes the review harder.

.. _commit-messages:

Write good commit messages. Start with a short subject line: the affected
component, bridge or bundle in square brackets, then a capitalized, imperative
sentence with no trailing period. Add a blank line and a longer description if
needed. For example:

.. code-block:: text

    [HttpClient] Add support for streaming large responses

If your change is a new feature or changes existing behavior, also update:

* the relevant ``CHANGELOG.md`` file(s) (prefix the entry with ``[BC BREAK]`` or
  ``[DEPRECATION]`` when relevant);
* the relevant ``UPGRADE-*.md`` file(s) if you break backward compatibility or
  deprecate something.

Submit your Pull Request
~~~~~~~~~~~~~~~~~~~~~~~~

.. _rebase-your-patch:

Update your branch with the latest changes from ``upstream`` and resolve any
conflict (replace ``6.4`` with the branch you chose):

.. code-block:: terminal

    $ git fetch upstream
    $ git rebase upstream/6.4

Then push your branch to your fork:

.. code-block:: terminal

    $ git push origin fix_XXX

.. _contributing-code-pull-request:

Now open a pull request on the ``symfony/symfony`` repository, targeting the
branch you chose (e.g. ``6.4``). The PR description contains a table you must
fill in; some answers add requirements:

* **Bug fix?** Reference the related issue(s), if any.
* **BC breaks / Deprecations?** Update the relevant ``CHANGELOG.md`` and
  ``UPGRADE-*.md`` files.

Give as much detail as possible in the description: it helps the review and
becomes part of the permanent record when the PR is merged. If the code isn't
finished yet, open the PR as a **draft** so reviewers know it's still in progress.

Receiving Feedback
~~~~~~~~~~~~~~~~~~

We ask all contributors to follow some :doc:`review best practices </contributing/reviews>`
to keep feedback constructive. The :doc:`core team </contributing/core_team>`
decides which PRs get merged, so their feedback is the most relevant. Don't feel
pressured to apply every suggestion immediately. If you receive feedback you
find abusive, contact the :doc:`CARE team </contributing/code_of_conduct/care_team>`.

Automated Checks
................

When you open a PR, several automated checks run on **GitHub Actions**:

* **Code style** is checked with `PHP CS Fixer`_. If it fails, run
  ``php php-cs-fixer.phar fix`` locally and commit the result.
* **Tests** run on the supported PHP versions and operating systems. A failure
  usually points to a real problem in your change, but it can also be unrelated;
  if you suspect that, check whether the target branch fails too and leave a comment.
* **Static analysis** (`PHPStan`_ and `Psalm`_) may comment on potential type errors.
  Review each one, but don't update their baseline files or add ``@phpstan-`` /
  ``@psalm-`` annotations.

Reworking your Pull Request
...........................

To apply review feedback, commit your changes on the same branch, rebase on the
latest ``upstream`` branch (don't merge) and force-push:

.. code-block:: terminal

    $ git rebase upstream/6.4
    $ git push --force origin fix_XXX

.. note::

    Always specify the branch name explicitly when force-pushing, to avoid
    touching other branches.

You don't need to squash your commits: Symfony squashes them automatically when
merging. Once merged, the pull request number is kept in the commit message
(e.g. ``merged branch USER/BRANCH (PR #1111)``) so the full discussion stays
linked to the code.

.. _running-symfony2-tests:
.. _running-symfony-tests:

Running Tests
-------------

Symfony runs the test suite automatically on every pull request, but it's good
practice to run the relevant tests locally before submitting your change.

.. _phpunit:
.. _dependencies_optional:

Before Running the Tests
~~~~~~~~~~~~~~~~~~~~~~~~

To run the Symfony test suite, install the external dependencies used during the
tests, such as Doctrine, Twig and Monolog. To do so, `install Composer`_ and
execute the following:

.. code-block:: terminal

    $ composer update

.. tip::

    Dependencies might fail to update and in this case Composer might need you
    to tell it what Symfony version you are working on. To do so set
    ``COMPOSER_ROOT_VERSION`` variable, e.g.:

    .. code-block:: terminal

        $ COMPOSER_ROOT_VERSION=7.4.x-dev composer update

.. _running:

Running the Tests
~~~~~~~~~~~~~~~~~

Then, run the test suite from the Symfony root directory with the following command:

.. code-block:: terminal

    $ php ./phpunit symfony

The output should display ``OK``. If not, read the reported errors to figure out
what's going on and if the tests are broken because of the new code.

.. tip::

    The entire Symfony suite can take up to several minutes to complete. If you
    want to test a single component, type its path after the ``phpunit`` command,
    e.g.:

    .. code-block:: terminal

        $ php ./phpunit src/Symfony/Component/Finder/

.. tip::

    On Windows, use a modern terminal (like `Windows Terminal`_) to see colored
    test results.

Testing Generated Code
~~~~~~~~~~~~~~~~~~~~~~

Some tests generate code on the fly and verify that it matches the expected
output stored in a file. To regenerate those files, run the tests with the
environment variable ``TEST_GENERATE_FIXTURES`` set to ``1``:

.. code-block:: terminal

    $ TEST_GENERATE_FIXTURES=1 php ./phpunit src/Symfony/Component/Config/Tests/Builder/GeneratedConfigTest.php

.. _symfony2-license:
.. _symfony-license:

License
-------

Symfony code is released under `the MIT license`_:

Copyright (c) 2004-present Fabien Potencier

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Check out the :ref:`license of the Symfony documentation <symfony-documentation-license>`
and other `Symfony licenses and trademarks`_.

.. tip::

    Once your pull request is merged, you're credited on the
    `Symfony Code Contributors`_ list.

.. _`GitHub`: https://github.com/signup
.. _`Fork the Symfony repository`: https://github.com/symfony/symfony/fork
.. _`search the existing issues and pull requests`: https://github.com/symfony/symfony/issues?q=+is%3Aopen+
.. _`Symfony Slack`: https://symfony.com/slack
.. _`PHP CS Fixer`: https://github.com/PHP-CS-Fixer/PHP-CS-Fixer
.. _`PHPStan`: https://phpstan.org/
.. _`Psalm`: https://psalm.dev/
.. _`issue tracker`: https://github.com/symfony/symfony/issues
.. _`install Composer`: https://getcomposer.org/download/
.. _`Windows Terminal`: https://learn.microsoft.com/windows/terminal/
.. _`the MIT license`: https://en.wikipedia.org/wiki/MIT_License
.. _`Symfony licenses and trademarks`: https://symfony.com/license
.. _`Symfony Code Contributors`: https://symfony.com/contributors/code
.. _`Contributing Back To Symfony`: https://symfonycasts.com/screencast/contributing
