Documentation Format
====================

The Symfony documentation uses `reStructuredText`_ as its markup language and
`Sphinx`_ for generating the documentation in the formats read by the end users,
such as HTML and PDF.

reStructuredText
----------------

reStructuredText is a plain text markup syntax similar to Markdown, but much
stricter with its syntax. If you are new to reStructuredText, take some time to
familiarize with this format by reading the existing `Symfony documentation`_
source code.

If you want to learn more about this format, check out the `reStructuredText Primer`_
tutorial and the `reStructuredText Reference`_.

.. caution::

    If you are familiar with Markdown, be careful as things are sometimes very
    similar but different:

    * Lists start at the beginning of a line (no indentation is allowed);
    * Inline code blocks use double-ticks (````like this````).

Sphinx
------

Sphinx_ is a build system that provides tools to create documentation from
reStructuredText documents. As such, it adds new directives and interpreted text
roles to the standard reStructuredText markup. Read more about the `Sphinx Markup Constructs`_.

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

The previous reStructuredText snippet renders as follow:

.. configuration-block::

    .. code-block:: yaml

        # Configuration in YAML

    .. code-block:: xml

        <!-- Configuration in XML -->

    .. code-block:: php

        // Configuration in PHP

All code examples assume that you are using that feature inside a Symfony
application. If you ever need to also show how to use it when working with
standalone components in any PHP application, use the special formats
``php-symfony`` and ``php-standalone``, which will be rendered like this:

.. configuration-block::

    .. code-block:: php-symfony

        // PHP code using features provided by the Symfony framework

    .. code-block:: php-standalone

        // PHP code using standalone components

The current list of supported formats are the following:

===================  ==============================================================================
Markup Format        Use It to Display
===================  ==============================================================================
``caddy``            Caddy web server configuration
``env``              Bash files (like ``.env`` files)
``html+php``         PHP code blended with HTML
``html+twig``        Twig markup blended with HTML
``html``             HTML
``ini``              INI
``php-annotations``  PHP Annotations
``php-attributes``   PHP Attributes
``php-standalone``   PHP code to be used in any PHP application using standalone Symfony components
``php-symfony``      PHP code example when using the Symfony framework
``php``              PHP
``rst``              reStructuredText markup
``terminal``         Renders the contents as a console terminal (use it to show which commands to run)
``twig``             Pure Twig markup
``varnish3``         Varnish Cache 3 configuration
``varnish4``         Varnish Cache 4 configuration
``vcl``              Varnish Configuration Language
``xml``              XML
``yaml``             YAML
===================  ==============================================================================

Displaying Tabs
~~~~~~~~~~~~~~~

It is possible to display tabs in the documentation. They look similar to
configuration blocks when rendered, but tabs can hold any type of content:

.. code-block:: rst

    .. tabs:: UX Installation

        .. tab:: Webpack Encore

            Introduction to Webpack

            .. code-block:: yaml

                webpack:
                    # ...

        .. tab:: AssetMapper

            Introduction to AssetMapper

            Something else about AssetMapper

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

**Links to specific page sections** follow a different syntax. First, define a
target above section you will link to (syntax: ``.. _`` + target name + ``:``):

.. code-block:: rst

    # /service_container/autowiring.rst

    # define the target
    .. _autowiring-calls:

    Autowiring other Methods (e.g. Setters and Public Typed Properties)
    -------------------------------------------------------------------

    // section content ...

Then, use the ``:ref::`` directive to link to that section from another file:

.. code-block:: rst

    # /reference/attributes.rst

    :ref:`Required <autowiring-calls>`

**Links to the API** follow a different syntax, where you must specify the type
of the linked resource (``class`` or ``method``):

.. code-block:: rst

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

For a new feature or a behavior change use the ``.. versionadded:: 7.x``
directive:

.. code-block:: rst

    .. versionadded:: 6.2

        ... ... ... was introduced in Symfony 6.2.

If you are documenting a behavior change, it may be helpful to *briefly*
describe how the behavior has changed:

.. code-block:: rst

    .. versionadded:: 6.2

       ... ... ... was introduced in Symfony 6.2. Prior to this,
       ... ... ... ... ... ... ... ... .

For a deprecation use the ``.. deprecated:: 6.x`` directive:

.. code-block:: rst

    .. deprecated:: 6.2

        ... ... ... was deprecated in Symfony 6.2.

Whenever a new major version of Symfony is released (e.g. 7.0, 8.0, etc), a new
branch of the documentation is created from the ``x.4`` branch of the previous
major version. At this point, all the ``versionadded`` and ``deprecated`` tags
for Symfony versions that have a lower major version will be removed. For
example, if Symfony 7.0 were released today, 6.0 to 6.4 ``versionadded`` and
``deprecated`` tags would be removed from the new ``7.0`` branch.

.. _reStructuredText: https://docutils.sourceforge.io/rst.html
.. _Sphinx: https://www.sphinx-doc.org/
.. _`Symfony documentation`: https://github.com/symfony/symfony-docs
.. _`reStructuredText Primer`: https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html
.. _`reStructuredText Reference`: https://docutils.sourceforge.io/docs/user/rst/quickref.html
.. _`Sphinx Markup Constructs`: https://www.sphinx-doc.org/en/1.7/markup/index.html
.. _`supported languages`: https://pygments.org/languages/
