Documentation Standards
=======================

In order to help the reader as much as possible and to create code examples that
look and feel familiar, you should follow these standards.

Sphinx
------

* The following characters are chosen for different heading levels: level 1
  is ``=`` (equal sign), level 2 ``-`` (dash), level 3 ``~`` (tilde), level 4
  ``.`` (dot) and level 5 ``"`` (double quote);
* Each line should break approximately after the first word that crosses the
  72nd character (so most lines end up being 72-78 characters);
* The ``::`` shorthand is *preferred* over ``.. code-block:: php`` to begin a PHP
  code block (read `the Sphinx documentation`_ to see when you should use the
  shorthand);
* Inline hyperlinks are **not** used. Separate the link and their target
  definition, which you add on the bottom of the page;
* Inline markup should be closed on the same line as the open-string;

Example
~~~~~~~

.. code-block:: text

    Example
    =======

    When you are working on the docs, you should follow the
    `Symfony Documentation`_ standards.

    Level 2
    -------

    A PHP example would be::

        echo 'Hello World';

    Level 3
    ~~~~~~~

    .. code-block:: php

        echo 'You cannot use the :: shortcut here';

    .. _`Symfony Documentation`: https://symfony.com/doc

Code Examples
-------------

* The code follows the :doc:`Symfony Coding Standards </contributing/code/standards>`
  as well as the `Twig Coding Standards`_;
* The code examples should look real for a web application context. Avoid abstract
  or trivial examples (``foo``, ``bar``, ``demo``, etc.);
* The code should follow the :doc:`Symfony Best Practices </best_practices/introduction>`.
  Unless the example requires a custom bundle, make sure to always use the
  ``AppBundle`` bundle to store your code;
* Use ``Acme`` when the code requires a vendor name;
* Use ``example.com`` as the domain of sample URLs and ``example.org`` and
  ``example.net`` when additional domains are required. All of these domains are
  `reserved by the IANA`_.
* To avoid horizontal scrolling on code blocks, we prefer to break a line
  correctly if it crosses the 85th character;
* When you fold one or more lines of code, place ``...`` in a comment at the point
  of the fold. These comments are: ``// ...`` (php), ``# ...`` (yaml/bash), ``{# ... #}``
  (twig), ``<!-- ... -->`` (xml/html), ``; ...`` (ini), ``...`` (text);
* When you fold a part of a line, e.g. a variable value, put ``...`` (without comment)
  at the place of the fold;
* Description of the folded code: (optional)

  * If you fold several lines: the description of the fold can be placed after the ``...``;
  * If you fold only part of a line: the description can be placed before the line;

* If useful to the reader, a PHP code example should start with the namespace
  declaration;
* When referencing classes, be sure to show the ``use`` statements at the
  top of your code block. You don't need to show *all* ``use`` statements
  in every example, just show what is actually being used in the code block;
* If useful, a ``codeblock`` should begin with a comment containing the filename
  of the file in the code block. Don't place a blank line after this comment,
  unless the next line is also a comment;
* You should put a ``$`` in front of every bash line.

Formats
~~~~~~~

Configuration examples should show all supported formats using
:ref:`configuration blocks <docs-configuration-blocks>`. The supported formats
(and their orders) are:

* **Configuration** (including services): YAML, XML, PHP
* **Routing**: Annotations, YAML, XML, PHP
* **Validation**: Annotations, YAML, XML, PHP
* **Doctrine Mapping**: Annotations, YAML, XML, PHP
* **Translation**: XML, YAML, PHP

Example
~~~~~~~

.. code-block:: php

    // src/Foo/Bar.php
    namespace Foo;

    use Acme\Demo\Cat;
    // ...

    class Bar
    {
        // ...

        public function foo($bar)
        {
            // set foo with a value of bar
            $foo = ...;

            $cat = new Cat($foo);

            // ... check if $bar has the correct value

            return $cat->baz($bar, ...);
        }
    }

.. caution::

    In YAML you should put a space after ``{`` and before ``}`` (e.g. ``{ _controller: ... }``),
    but this should not be done in Twig (e.g.  ``{'hello' : 'value'}``).

Files and Directories
---------------------

* When referencing directories, always add a trailing slash to avoid confusions
  with regular files (e.g. "execute the ``console`` script located at the ``app/``
  directory").
* When referencing file extensions explicitly, you should include a leading dot
  for every extension (e.g. "XML files use the ``.xml`` extension").
* When you list a Symfony file/directory hierarchy, use ``your-project/`` as the
  top level directory. E.g.

  .. code-block:: text

      your-project/
      ├─ app/
      ├─ src/
      ├─ vendor/
      └─ ...

English Language Standards
--------------------------

Symfony documentation uses the United States English dialect, commonly called
`American English`_. The `American English Oxford Dictionary`_ is used as the
vocabulary reference.

In addition, documentation follows these rules:

* **Section titles**: use a variant of the title case, where the first
  word is always capitalized and all other words are capitalized, except for
  the closed-class words (read Wikipedia article about `headings and titles`_).

  E.g.: The Vitamins are in my Fresh California Raisins

* **Punctuation**: avoid the use of `Serial (Oxford) Commas`_;
* **Pronouns**: avoid the use of `nosism`_ and always use *you* instead of *we*.
  (i.e. avoid the first person point of view: use the second instead);
* **Gender-neutral language**: when referencing a hypothetical person, such as
  *"a user with a session cookie"*, use gender-neutral pronouns (they/their/them).
  For example, instead of:

  * he or she, use they
  * him or her, use them
  * his or her, use their
  * his or hers, use theirs
  * himself or herself, use themselves

.. _`the Sphinx documentation`: http://sphinx-doc.org/rest.html#source-code
.. _`Twig Coding Standards`: http://twig.sensiolabs.org/doc/coding_standards.html
.. _`reserved by the IANA`: http://tools.ietf.org/html/rfc2606#section-3
.. _`American English`: https://en.wikipedia.org/wiki/American_English
.. _`American English Oxford Dictionary`: http://www.oxforddictionaries.com/definition/american_english/
.. _`headings and titles`: https://en.wikipedia.org/wiki/Letter_case#Headings_and_publication_titles
.. _`Serial (Oxford) Commas`: https://en.wikipedia.org/wiki/Serial_comma
.. _`nosism`: https://en.wikipedia.org/wiki/Nosism
