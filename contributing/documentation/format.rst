Documentation Format
====================

The Symfony documentation uses `reStructuredText`_ as its markup language and
`Sphinx`_ for generating the documentation in the formats read by the end users,
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

Sphinx_ is a build system that provides tools to create documentation from
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

    :doc:`/controller`

    :doc:`/components/event_dispatcher`

    :doc:`/configuration/environments`

The title of the linked page will be automatically used as the text of the link.
If you want to modify that title, use this alternative syntax:

.. code-block:: rst

    :doc:`Doctrine Associations </doctrine/associations>`

.. note::

    Although they are technically correct, avoid the use of relative internal
    links such as the following, because they break the references in the
    generated PDF documentation:

    .. code-block:: rst

        :doc:`controller`

        :doc:`event_dispatcher`

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

New Features, Behavior Changes or Deprecations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you are documenting a brand new feature, a change or a deprecation that's
been made in Symfony, you should precede your description of the change with
the corresponding directive and a short description:

For a new feature or a behavior change use the ``.. versionadded:: 4.x``
directive:

.. code-block:: rst

    .. versionadded:: 4.2

        Named autowiring aliases have been introduced in Symfony 4.2.

If you are documenting a behavior change, it may be helpful to *briefly*
describe how the behavior has changed:

.. code-block:: rst

    .. versionadded:: 4.2

       Support for ICU MessageFormat was introduced in Symfony 4.2. Prior to this,
       pluralization was managed by the ``transChoice`` method.

For a deprecation use the ``.. deprecated:: 4.X`` directive:

.. code-block:: rst

    .. deprecated:: 4.2

        Not passing the root node name to ``TreeBuilder`` was deprecated in Symfony 4.2.

Whenever a new major version of Symfony is released (e.g. 5.0, 6.0, etc),
a new branch of the documentation is created from the ``master`` branch.
At this point, all the ``versionadded`` and ``deprecated`` tags for Symfony
versions that have a lower major version will be removed. For example, if
Symfony 5.0 were released today, 4.0 to 4.4 ``versionadded`` and ``deprecated``
tags would be removed from the new ``5.0`` branch.

.. _reStructuredText: http://docutils.sourceforge.net/rst.html
.. _Sphinx: https://www.sphinx-doc.org/
.. _`Symfony documentation`: https://github.com/symfony/symfony-docs
.. _`reStructuredText Primer`: https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html
.. _`reStructuredText Reference`: http://docutils.sourceforge.net/docs/user/rst/quickref.html
.. _`Sphinx Markup Constructs`: https://www.sphinx-doc.org/en/1.7/markup/index.html
.. _`supported languages`: http://pygments.org/languages/
