Documentation Format
====================

The Symfony2 documentation uses `reStructuredText`_ as its markup language and
`Sphinx`_ for building the output (HTML, PDF, ...).

reStructuredText
----------------

reStructuredText *"is an easy-to-read, what-you-see-is-what-you-get plaintext
markup syntax and parser system"*.

You can learn more about its syntax by reading existing Symfony2 `documents`_
or by reading the `reStructuredText Primer`_ on the Sphinx website.

.. caution::

    If you are familiar with Markdown, be careful as things are sometimes very
    similar but different:

    * Lists starts at the beginning of a line (no indentation is allowed);
    * Inline code blocks use double-ticks (````like this````).

Sphinx
------

Sphinx is a build system that adds some nice tools to create documentation
from reStructuredText documents. As such, it adds new directives and
interpreted text roles to the standard reST `markup`_.

Syntax Highlighting
~~~~~~~~~~~~~~~~~~~

All code examples uses PHP as the default highlighted language. You can change
it with the ``code-block`` directive:

.. code-block:: rst

    .. code-block:: yaml

        { foo: bar, bar: { foo: bar, bar: baz } }

If your PHP code begins with ``<?php``, then you need to use ``html+php`` as
the highlighted pseudo-language:

.. code-block:: rst

    .. code-block:: html+php

        <?php echo $this->foobar(); ?>

.. note::

    A list of supported languages is available on the `Pygments website`_.

.. _docs-configuration-blocks:

Configuration Blocks
~~~~~~~~~~~~~~~~~~~~

Whenever you show a configuration, you must use the ``configuration-block``
directive to show the configuration in all supported configuration formats
(``PHP``, ``YAML``, and ``XML``)

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

===============  ==============
Markup format    Displayed
===============  ==============
html             HTML
xml              XML
php              PHP
yaml             YAML
jinja            Twig
html+jinja       Twig
html+php         PHP
ini              INI
php-annotations  Annotations
php-standalone   Standalone Use
php-symfony      Framework Use
===============  ==============

Adding Links
~~~~~~~~~~~~

To add links to other pages in the documents use the following syntax:

.. code-block:: rst

    :doc:`/path/to/page`

Using the path and filename of the page without the extension, for example:

.. code-block:: rst

    :doc:`/book/controller`

    :doc:`/components/event_dispatcher/introduction`

    :doc:`/cookbook/configuration/environments`

The link text will be the main heading of the document linked to. You can
also specify alternative text for the link:

.. code-block:: rst

    :doc:`Spooling Email </cookbook/email/spool>`

You can also add links to the API documentation:

.. code-block:: rst

    :namespace:`Symfony\\Component\\BrowserKit`

    :class:`Symfony\\Component\\Routing\\Matcher\\ApacheUrlMatcher`

    :method:`Symfony\\Component\\HttpKernel\\Bundle\\Bundle::build`

and to the PHP documentation:

.. code-block:: rst

    :phpclass:`SimpleXMLElement`

    :phpmethod:`DateTime::createFromFormat`

    :phpfunction:`iterator_to_array`

Testing Documentation
~~~~~~~~~~~~~~~~~~~~~

To test documentation before a commit:

* Install `Sphinx`_;
* Install the Sphinx extensions using git submodules: ``git submodule update --init``;
* (Optionally) Install the bundle docs and CMF docs: ``bash install.sh``;
* Run ``make html`` and view the generated HTML in the ``build`` directory.

.. _reStructuredText:        http://docutils.sourceforge.net/rst.html
.. _Sphinx:                  http://sphinx-doc.org/
.. _documents:               https://github.com/symfony/symfony-docs
.. _reStructuredText Primer: http://sphinx-doc.org/rest.html
.. _markup:                  http://sphinx-doc.org/markup/
.. _Pygments website:        http://pygments.org/languages/
.. _Sphinx quick setup:      http://sphinx-doc.org/tutorial.html#setting-up-the-documentation-sources
