Documentation Standards
=======================

In order to help the reader as much as possible and to create code examples that
look and feel familiar, you should follow these standards.

Sphinx
------

* The following characters are choosen for different heading levels: level 1
  is ``=``, level 2 ``-``, level 3 ``~``, level 4 ``.`` and level 5 ``"``;
* Each line should break approximately after the first word that crosses the
  72nd character (so most lines end up being 72-78 characters);
* The ``::`` shorthand is *preferred* over ``.. code-block:: php`` to begin a PHP
  code block (read `the Sphinx documentation`_ to see when you should use the
  shorthand);
* Inline hyperlinks are **not** used. Seperate the link and their target
  definition, which you add on the bottom of the page;
* You should use a form of *you* instead of *we*.

Example
~~~~~~~

.. code-block:: text

    Example
    =======

    When you are working on the docs, you should follow the `Symfony Docs`_
    standards.

    Level 2
    -------

    A PHP example would be::

        echo 'Hello World';

    Level 3
    ~~~~~~~

    .. code-block:: php

        echo 'You cannot use the :: shortcut here';

    .. _`Symfony Docs`: http://symfony.com/doc/current/contributing/documentation/standards.html

Code Examples
-------------

* The code follows the :doc:`Symfony Coding Standards</contributing/code/standards>`
  as well as the `Twig Coding Standards`_;
* To avoid horizontal scrolling on code blocks, we prefer to break a line
  correctly if it crosses the 85th character;
* When you fold one or more lines of code, place ``...`` in a comment at the point
  of the fold. These comments are: ``// ...`` (php), ``# ...`` (yaml/bash), ``{# ... #}``
  (twig), ``<!-- ... -->`` (xml/html), ``; ...`` (ini), ``...`` (text);
* When you fold a part of a line, e.g. a variable value, put ``...`` (without comment)
  at the place of the fold;
* Description of the folded code: (optional)
  If you fold several lines: the description of the fold can be placed after the ``...``
  If you fold only part of a line: the description can be placed before the line;
* If useful, a ``codeblock`` should begin with a comment containing the filename
  of the file in the code block. Don't place a blank line after this comment,
  unless the next line is also a comment;
* You should put a ``$`` in front of every bash line.

Formats
~~~~~~~

Configuration examples should show all supported formats using
:ref:`configuration blocks <docs-configuration-blocks>`. The supported formats
(and their orders) are:

* **Configuration** (including services and routing): Yaml, Xml, Php
* **Validation**: Yaml, Annotations, Xml, Php
* **Doctrine Mapping**: Annotations, Yaml, Xml, Php

Example
~~~~~~~

.. code-block:: php

    // src/Foo/Bar.php

    // ...
    class Bar
    {
        // ...

        public function foo($bar)
        {
            // set foo with a value of bar
            $foo = ...;

            // ... check if $bar has the correct value

            return $foo->baz($bar, ...);
        }
    }

.. caution::

    In Yaml you should put a space after ``{`` and before ``}`` (e.g. ``{ _controller: ... }``),
    but this should not be done in Twig (e.g.  ``{'hello' : 'value'}``).

.. _`the Sphinx documentation`: http://sphinx-doc.org/rest.html#source-code
.. _`Twig Coding Standards`: http://twig.sensiolabs.org/doc/coding_standards.html
