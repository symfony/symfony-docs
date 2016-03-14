Documentation Format
====================

The Symfony documentation uses reStructuredText_ as its markup language and
Sphinx_ for generating the documentation in the formats read by the end users,
such as HTML and PDF.

reStructuredText
----------------

reStructuredText is a plaintext markup syntax similar to Markdown, but much
stricter with its syntax. If you are new to reStructuredText, take some time to
familiarize with this format by reading the existing `Symfony documentation`_
source code.

If you want to learn more about this format, check out the `reStructuredText Primer`_
tutorial and the `reStructuredText Reference`_.

.. caution::

    If you are familiar with Markdown, be careful as things are sometimes very
    similar but different:

    * Lists starts at the beginning of a line (no indentation is allowed);
    * Inline code blocks use double-ticks (````like this````).

Sphinx
------

Sphinx is a build system that provides tools to create documentation from
reStructuredText documents. As such, it adds new directives and interpreted text
roles to the standard reST markup. Read more about the `Sphinx Markup Constructs`_.

Syntax Highlighting
~~~~~~~~~~~~~~~~~~~

PHP is the default syntax highlighter applied to all code blocks. You can
change it with the ``code-block`` directive:

.. code-block:: rst

    .. code-block:: yaml

        { foo: bar, bar: { foo: bar, bar: baz } }

.. note::

    Besides all of the major programming languages, the syntax highlighter
    supports all kinds of markup and configuration languages. Check out the
    list of `supported languages`_ on the syntax highlighter website.

.. _docs-configuration-blocks:

Configuration Blocks
~~~~~~~~~~~~~~~~~~~~

Whenever you include a configuration sample, use the ``configuration-block``
directive to show the configuration in all supported configuration formats
(``PHP``, ``YAML`` and ``XML``). Example:

.. code-block:: rst

    .. configuration-block::

        .. code-block:: yaml

            # Configuration in YAML

        .. code-block:: xml

            <!-- Configuration in XML -->

        .. code-block:: php

            // Configuration in PHP

The previous reST snippet renders as follow:

.. configuration-block::

    .. code-block:: yaml

        # Configuration in YAML

    .. code-block:: xml

        <!-- Configuration in XML -->

    .. code-block:: php

        // Configuration in PHP

The current list of supported formats are the following:

===================  ======================================
Markup Format        Use It to Display
===================  ======================================
``html``             HTML
``xml``              XML
``php``              PHP
``yaml``             YAML
``twig``             Pure Twig markup
``html+twig``        Twig markup blended with HTML
``html+php``         PHP code blended with HTML
``ini``              INI
``php-annotations``  PHP Annotations
===================  ======================================

Adding Links
~~~~~~~~~~~~

The most common type of links are **internal links** to other documentation pages,
which use the following syntax:

.. code-block:: rst

    :doc:`/absolute/path/to/page`

The page name should not include the file extension (``.rst``). For example:

.. code-block:: rst

    :doc:`/book/controller`

    :doc:`/components/event_dispatcher/introduction`

    :doc:`/cookbook/configuration/environments`

The title of the linked page will be automatically used as the text of the link.
If you want to modify that title, use this alternative syntax:

.. code-block:: rst

    :doc:`Spooling Email </cookbook/email/spool>`

.. note::

    Although they are technically correct, avoid the use of relative internal
    links such as the following, because they break the references in the
    generated PDF documentation:

    .. code-block:: rst

        :doc:`controller`

        :doc:`event_dispatcher/introduction`

        :doc:`environments`

**Links to the API** follow a different syntax, where you must specify the type
of the linked resource (``namespace``, ``class`` or ``method``):

.. code-block:: rst

    :namespace:`Symfony\\Component\\BrowserKit`

    :class:`Symfony\\Component\\Routing\\Matcher\\ApacheUrlMatcher`

    :method:`Symfony\\Component\\HttpKernel\\Bundle\\Bundle::build`

**Links to the PHP documentation** follow a pretty similar syntax:

.. code-block:: rst

    :phpclass:`SimpleXMLElement`

    :phpmethod:`DateTime::createFromFormat`

    :phpfunction:`iterator_to_array`

New Features or Behavior Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're documenting a brand new feature or a change that's been made in
Symfony, you should precede your description of the change with a
``.. versionadded:: 2.X`` directive and a short description:

.. code-block:: rst

    .. versionadded:: 2.3
        The ``askHiddenResponse`` method was introduced in Symfony 2.3.

    You can also ask a question and hide the response. This is particularly [...]

If you're documenting a behavior change, it may be helpful to *briefly* describe
how the behavior has changed:

.. code-block:: rst

    .. versionadded:: 2.3
        The ``include()`` function is a new Twig feature that's available in
        Symfony 2.3. Prior, the ``{% include %}`` tag was used.

Whenever a new minor version of Symfony is released (e.g. 2.4, 2.5, etc),
a new branch of the documentation is created from the ``master`` branch.
At this point, all the ``versionadded`` tags for Symfony versions that have
reached end-of-maintenance will be removed. For example, if Symfony 2.5 were
released today, and 2.2 had recently reached its end-of-life, the 2.2 ``versionadded``
tags would be removed from the new ``2.5`` branch.

Testing Documentation
~~~~~~~~~~~~~~~~~~~~~

When submitting a new content to the documentation repository or when changing
any existing resource, an automatic process will check if your documentation is
free of syntax errors and is ready to be reviewed.

Nevertheless, if you prefer to do this check locally on your own machine before
submitting your documentation, follow these steps:

* Install Sphinx_;
* Install the Sphinx extensions using git submodules: ``$ git submodule update --init``;
* Run ``make html`` and view the generated HTML in the ``_build/html`` directory.

.. _reStructuredText: http://docutils.sourceforge.net/rst.html
.. _Sphinx: http://sphinx-doc.org/
.. _`Symfony documentation`: https://github.com/symfony/symfony-docs
.. _`reStructuredText Primer`: http://sphinx-doc.org/rest.html
.. _`reStructuredText Reference`: http://docutils.sourceforge.net/docs/user/rst/quickref.html
.. _`Sphinx Markup Constructs`: http://sphinx-doc.org/markup/
.. _`supported languages`: http://pygments.org/languages/
