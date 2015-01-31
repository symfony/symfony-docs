.. index::
   single: CssSelector
   single: Components; CssSelector

The CssSelector Component
=========================

    The CssSelector component converts CSS selectors to XPath expressions.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/css-selector`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/CssSelector).

Usage
-----

Why to Use CSS selectors?
~~~~~~~~~~~~~~~~~~~~~~~~~

When you're parsing an HTML or an XML document, by far the most powerful
method is XPath.

XPath expressions are incredibly flexible, so there is almost always an
XPath expression that will find the element you need. Unfortunately, they
can also become very complicated, and the learning curve is steep. Even common
operations (such as finding an element with a particular class) can require
long and unwieldy expressions.

Many developers -- particularly web developers -- are more comfortable
using CSS selectors to find elements. As well as working in stylesheets,
CSS selectors are used in JavaScript with the ``querySelectorAll`` function
and in popular JavaScript libraries such as jQuery, Prototype and MooTools.

CSS selectors are less powerful than XPath, but far easier to write, read
and understand. Since they are less powerful, almost all CSS selectors can
be converted to an XPath equivalent. This XPath expression can then be used
with other functions and classes that use XPath to find elements in a
document.

The CssSelector Component
~~~~~~~~~~~~~~~~~~~~~~~~~

The component's only goal is to convert CSS selectors to their XPath
equivalents::

    use Symfony\Component\CssSelector\CssSelector;

    print CssSelector::toXPath('div.item > h4 > a');

This gives the following output:

.. code-block:: text

    descendant-or-self::div[@class and contains(concat(' ',normalize-space(@class), ' '), ' item ')]/h4/a

You can use this expression with, for instance, :phpclass:`DOMXPath` or
:phpclass:`SimpleXMLElement` to find elements in a document.

.. tip::

    The :method:`Crawler::filter() <Symfony\\Component\\DomCrawler\\Crawler::filter>` method
    uses the CssSelector component to find elements based on a CSS selector
    string. See the :doc:`/components/dom_crawler` for more details.

Limitations of the CssSelector Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Not all CSS selectors can be converted to XPath equivalents.

There are several CSS selectors that only make sense in the context of a
web-browser.

* link-state selectors: ``:link``, ``:visited``, ``:target``
* selectors based on user action: ``:hover``, ``:focus``, ``:active``
* UI-state selectors: ``:invalid``, ``:indeterminate`` (however, ``:enabled``,
  ``:disabled``, ``:checked`` and ``:unchecked`` are available)

Pseudo-elements (``:before``, ``:after``, ``:first-line``,
``:first-letter``) are not supported because they select portions of text
rather than elements.

Several pseudo-classes are not yet supported:

* ``*:first-of-type``, ``*:last-of-type``, ``*:nth-of-type``,
  ``*:nth-last-of-type``, ``*:only-of-type``. (These work with an element
  name (e.g. ``li:first-of-type``) but not with ``*``.

.. _Packagist: https://packagist.org/packages/symfony/css-selector
