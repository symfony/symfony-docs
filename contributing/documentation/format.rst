Documentation Format
====================

The Symfony2 documentation uses `reStructuredText`_ as its markup language and
`Sphinx`_ for building the output (HTML, PDF, ...).

reStructuredText
----------------

reStructuredText "is an easy-to-read, what-you-see-is-what-you-get plaintext
markup syntax and parser system".

You can learn more about its syntax by reading existing Symfony2 `documents`_
or by reading the `reStructuredText Primer`_ on the Sphinx website.

If you are familiar with Markdown, be careful as things as sometimes very
similar but different:

* Lists starts at the beginning of a line (no indentation is allowed);

* Inline code blocks use double-ticks (````like this````).

Sphinx
------

Sphinx is a build system that adds some nice tools to create documentation
from reStructuredText documents. As such, it adds new directives and
interpreted text roles to standard reST `markup`_.

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

            <!-- Configuration in XML //-->

        .. code-block:: php

            // Configuration in PHP

The previous reST snippet renders as follow:

.. configuration-block::

    .. code-block:: yaml

        # Configuration in YAML

    .. code-block:: xml

        <!-- Configuration in XML //-->

    .. code-block:: php

        // Configuration in PHP

The current list of supported formats are the following:

+-----------------+-------------+
| Markup format   | Displayed   |
+=================+=============+
| html            | HTML        |
+-----------------+-------------+
| xml             | XML         |
+-----------------+-------------+
| php             | PHP         |
+-----------------+-------------+
| yaml            | YAML        |
+-----------------+-------------+
| jinja           | Twig        |
+-----------------+-------------+
| html+jinja      | Twig        |
+-----------------+-------------+
| jinja+html      | Twig        |
+-----------------+-------------+
| php+html        | PHP         |
+-----------------+-------------+
| html+php        | PHP         |
+-----------------+-------------+
| ini             | INI         |
+-----------------+-------------+
| php-annotations | Annotations |
+-----------------+-------------+

Testing Documentation
~~~~~~~~~~~~~~~~~~~~~

To test documentation before a commit:

 * Install `Sphinx`_;

 * Run the `Sphinx quick setup`_;

 * Install the configuration-block Sphinx extension (see below);

 * Run ``make html`` and view the generated HTML in the ``build`` directory.

Installing the configuration-block Sphinx extension
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 * Download the extension from the `configuration-block source`_ repository
  
 * Copy the ``configurationblock.py`` to the ``_exts`` folder under your
   source folder (where ``conf.py`` is located)
   
 * Add the following to the ``conf.py`` file:

.. code-block:: py
    
    # ...
    sys.path.append(os.path.abspath('_exts'))
    
    # ...
    # add configurationblock to the list of extensions
    extensions = ['configurationblock']

.. _reStructuredText:           http://docutils.sf.net/rst.html
.. _Sphinx:                     http://sphinx.pocoo.org/
.. _documents:                  http://github.com/symfony/symfony-docs
.. _reStructuredText Primer:    http://sphinx.pocoo.org/rest.html
.. _markup:                     http://sphinx.pocoo.org/markup/
.. _Pygments website:           http://pygments.org/languages/
.. _configuration-block source: https://github.com/fabpot/sphinx-php
.. _Sphinx quick setup:         http://sphinx.pocoo.org/tutorial.html#setting-up-the-documentation-sources
