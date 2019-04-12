.. index::
   single: Inflector
   single: Components; Inflector

The Inflector Component
=======================

    The Inflector component converts English words between their singular and
    plural forms.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/inflector

.. include:: /components/require_autoload.rst.inc

When you May Need an Inflector
------------------------------

In some scenarios such as code generation and code introspection, it's usually
required to convert words from/to singular/plural. For example, if you need to
know which property is associated with an *adder* method, you must convert from
plural to singular (``addStories()`` method -> ``$story`` property).

Although most human languages define simple pluralization rules, they also
define lots of exceptions. For example, the general rule in English is to add an
``s`` at the end of the word (``book`` -> ``books``) but there are lots of
exceptions even for common words (``woman`` -> ``women``, ``life`` -> ``lives``,
``news`` -> ``news``, ``radius`` -> ``radii``, etc.)

This component abstracts all those pluralization rules so you can convert
from/to singular/plural with confidence. However, due to the complexity of the
human languages, this component only provides support for the English language.

Usage
-----

The Inflector component provides two static methods to convert from/to
singular/plural::

    use Symfony\Component\Inflector\Inflector;

    Inflector::singularize('alumni');   // 'alumnus'
    Inflector::singularize('knives');   // 'knife'
    Inflector::singularize('mice');     // 'mouse'

    Inflector::pluralize('grandchild'); // 'grandchildren'
    Inflector::pluralize('news');       // 'news'
    Inflector::pluralize('bacterium');  // 'bacteria'

Sometimes it's not possible to determine a unique singular/plural form for the
given word. In those cases, the methods return an array with all the possible
forms::

    use Symfony\Component\Inflector\Inflector;

    Inflector::singularize('indices'); // ['index', 'indix', 'indice']
    Inflector::singularize('leaves');  // ['leaf', 'leave', 'leaff']

    Inflector::pluralize('matrix');    // ['matricies', 'matrixes']
    Inflector::pluralize('person');    // ['persons', 'people']
