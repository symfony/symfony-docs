Contributing Documentation
==========================

The Symfony documentation is as open as the code. Fixing a typo, clarifying a
confusing paragraph or documenting a new feature are all valuable contributions.

.. tip::

    **Quick reference**

    Small fix? Click **Edit this page** on any documentation page and propose the
    change directly on GitHub. Larger change? Fork
    `symfony/symfony-docs`_, branch off ``6.4`` (the oldest maintained branch),
    edit the ``.rst`` files, and open a pull request against ``6.4``.

Before you start, you need a free `GitHub`_ account and a basic familiarity with
the `reStructuredText`_ format used by the docs (see the
:doc:`format guide </contributing/documentation/format>` for a quick overview).

.. _minor-changes-e-g-typos:

Fixing Typos and Small Changes Online
-------------------------------------

For a small change like a typo or rewording, the fastest way is to edit the file
directly on GitHub, without installing anything:

#. Click the **Edit this page** button at the top of the documentation page:

   .. image:: /_images/contributing/docs-github-edit-page.png
       :alt: The "Edit this page" button is located directly below the first heading.
       :class: with-browser

#. Edit the contents (the first time, GitHub asks you to fork the repository).
   Then describe your change and click **Propose changes**:

   .. image:: /_images/contributing/docs-github-create-pr.png
       :alt: The "Comparing changes" page on GitHub.
       :class: with-browser

#. Click **Create pull request**. That's it, **congratulations!** The community
   will review your pull request and may suggest tweaks.

For anything larger, or if you prefer to work on your own computer, use the local
workflow below.

Contributing on your Computer
-----------------------------

.. include:: /contributing/_branch_note.rst.inc

#. `Fork`_ the `symfony/symfony-docs`_ repository (you only do this once) and
   clone your fork:

   .. code-block:: terminal

       $ git clone git@github.com:YOUR_USERNAME/symfony-docs.git
       $ cd symfony-docs/

#. Add the original repository as a remote called ``upstream`` and create a
   branch for your changes based on the branch you chose (use ``fix_XXX`` for
   reported issues, where ``XXX`` is the issue number):

   .. code-block:: terminal

       $ git remote add upstream https://github.com/symfony/symfony-docs.git
       $ git fetch upstream
       $ git checkout -b improve_docs upstream/6.4

#. Make your changes following the
   :doc:`/contributing/documentation/standards`, then commit and push them to
   your fork:

   .. code-block:: terminal

       $ git commit -am "Describe your change"
       $ git push origin improve_docs

#. Go to your fork on GitHub and open a **pull request** against the ``6.4``
   branch of ``symfony/symfony-docs``. Add a short description of your changes
   and submit it.

.. _pull-request-format:

If reviewers ask for changes, don't open a new pull request. Commit on the same
branch and push again; the pull request updates automatically:

.. code-block:: terminal

    $ git checkout improve_docs
    # ... make the requested changes ...
    $ git commit -am "Apply review feedback"
    $ git push

.. note::

    When you open the pull request, automated checks look for common errors
    (syntax issues, broken links, typos), so you don't need to install anything
    to validate your changes. If you prefer to build the documentation locally
    to debug issues or read it offline, follow the instructions in
    `the README of the symfony-docs repository`_.

Once merged, your changes appear on the Symfony website within a few hours, and
you join the `Symfony Documentation Contributors`_ list.

Frequently Asked Questions
--------------------------

Why Should I Use the ``6.4`` Branch Instead of the Latest One?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Like the code, the documentation is split into branches matching the maintained
Symfony versions. Basing fixes on the oldest maintained branch (``6.4``) lets the
documentation team apply them to all newer branches automatically. Only base your
change on a newer branch when you're documenting a feature introduced there.

Can I Submit Unfinished Work?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Yes. Open it as a **draft pull request** so reviewers know it's still in
progress. If you're documenting a feature that hasn't been merged into the core
code yet, mention it: the PR won't be merged until the code is.

Would You Accept a Huge Pull Request?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Make sure the changes are related; otherwise, split them into separate pull
requests. Before submitting a large change, it's a good idea to open an issue
first to check that the documentation team agrees with your proposal, so you
don't waste your time.

Translations
------------

The official Symfony documentation is published only in English. You can read
about the reasons in `this blog post`_.

We have taken steps to improve the experience when using `Google Translate`_ to
prevent code blocks from being translated. To translate any page, copy its URL
and paste it into the form on the Google Translate site.

.. note::

    Contributing *translations of Symfony's built-in messages* (validation
    errors, security messages, etc.) is a different task, explained in
    :doc:`/contributing/translation`.

.. _symfony2-documentation-license:
.. _symfony-documentation-license:

License
-------

The Symfony documentation is licensed under a Creative Commons
Attribution-Share Alike 3.0 Unported License (`CC BY-SA 3.0`_).

**You are free** to *share* (copy, distribute and transmit) and to *remix*
(adapt) the work, **under the following conditions:**

* *Attribution*: You must attribute the work in the manner specified by the
  author or licensor (but not in any way that suggests they endorse you or your
  use of the work);
* *Share Alike*: If you alter, transform, or build upon this work, you may
  distribute the resulting work only under the same or a similar license.

This is a human-readable summary of the `Legal Code (the full license)`_. Check
out the :ref:`license of the Symfony code <symfony-license>` and other
`Symfony licenses and trademarks`_.

.. _`GitHub`: https://github.com/
.. _`reStructuredText`: https://docutils.sourceforge.io/rst.html
.. _`symfony/symfony-docs`: https://github.com/symfony/symfony-docs
.. _`Fork`: https://github.com/symfony/symfony-docs/fork
.. _`Symfony Documentation Contributors`: https://symfony.com/contributors/doc
.. _`the README of the symfony-docs repository`: https://github.com/symfony/symfony-docs#readme
.. _`this blog post`: https://symfony.com/blog/discontinuing-the-symfony-community-translations
.. _`Google Translate`: https://translate.google.com
.. _`CC BY-SA 3.0`: https://creativecommons.org/licenses/by-sa/3.0/
.. _`Legal Code (the full license)`: https://creativecommons.org/licenses/by-sa/3.0/legalcode
.. _`Symfony licenses and trademarks`: https://symfony.com/license
