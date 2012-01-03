.. index::
   single: CSS Selector

The CssSelector Component
=========================

    The CssSelector Component converts CSS selectors to XPath expressions.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/CssSelector);
* Install it via PEAR ( `pear.symfony.com/CssSelector`);
* Install it via Composer (`symfony/css-selector` on Packagist).

Usage
-----

The component only goal is to convert CSS selectors to their XPath
equivalents::

    use Symfony\Component\CssSelector\CssSelector;

    print CssSelector::toXPath('div.item > h4 > a');
