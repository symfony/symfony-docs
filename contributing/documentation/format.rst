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

    :doc:`Spooling Email </email/spool>`

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

For a new feature or a behavior change use the ``.. versionadded:: 3.x``
directive:

.. code-block:: rst

    .. versionadded:: 3.4

        The special ``!`` template prefix was introduced in Symfony 3.4.

If you are documenting a behavior change, it may be helpful to *briefly*
describe how the behavior has changed:

.. code-block:: rst

    .. versionadded:: 3.4

        Support for annotation routing without an external bundle was added in
        Symfony 3.4. Prior, you needed to install the SensioFrameworkExtraBundle.

For a deprecation use the ``.. deprecated:: 3.X`` directive:

.. code-block:: rst

    .. deprecated:: 3.3

        This technique is discouraged and the ``addClassesToCompile()`` method was
        deprecated in Symfony 3.3 because modern PHP versions make it unnecessary.

Whenever a new major version of Symfony is released (e.g. 3.0, 4.0, etc),
a new branch of the documentation is created from the ``master`` branch.
At this point, all the ``versionadded`` and ``deprecated`` tags for Symfony
versions that have a lower major version will be removed. For example, if
Symfony 4.0 were released today, 3.0 to 3.4 ``versionadded`` and ``deprecated``
tags would be removed from the new ``4.0`` branch.

.. _reStructuredText: http://docutils.sourceforge.net/rst.html
.. _Sphinx: http://sphinx-doc.org/
.. _`Symfony documentation`: https://github.com/symfony/symfony-docs
.. _`reStructuredText Primer`: http://sphinx-doc.org/rest.html
.. _`reStructuredText Reference`: http://docutils.sourceforge.net/docs/user/rst/quickref.html
.. _`Sphinx Markup Constructs`: http://sphinx-doc.org/markup/
.. _`supported languages`: http://pygments.org/languages/
