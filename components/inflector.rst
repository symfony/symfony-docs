.. index::
   single: Inflector
   single: Components; Inflector

The Inflector Component
=======================

    The Inflector component converts English words from plural to singular.

Installation
------------

You can install the component in two different ways:

* :doc:`Install it via Composer</components/using_components>` (``symfony/inflector`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/inflector).

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Inflector component provides an easy and consistent way to convert English
words between singular and plural forms. The static ``singularize()`` method
defined by the :class:`Symfony\\Component\\Inflector\\Inflector` class returns
the singular form of the given word::

    use Symfony\Component\Inflector\Inflector;

    $word = Inflector::singularize('cars');
    // $word = 'car'

Besides the regular singular-plural conversions, this component supports most of
the arbitrary pluralization rules defined by the English language::

    use Symfony\Component\Inflector\Inflector;

    Inflector::singularize('alumni');  // 'alumnus'
    Inflector::singularize('knives');  // 'knife'
    Inflector::singularize('mice');    // 'mouse'
    Inflector::singularize('stories'); // 'story'
    Inflector::singularize('women');   // 'woman'

Sometimes it's not possible to determine a unique singular form for the given
word. In those edge cases, the ``singularize()`` method returns an array with
all the compatible singular forms::

    use Symfony\Component\Inflector\Inflector;

    Inflector::singularize('indices'); // array('index', 'indix', 'indice')
    Inflector::singularize('leaves');  // array('leaf', 'leave', 'leaff')

.. _Packagist: https://packagist.org/packages/symfony/inflector
