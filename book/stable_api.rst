.. index::
   single: Stable API

The Symfony2 Stable API
=======================

The Symfony2 stable API is a subset of all Symfony2 published public methods
(components and core bundles) that share the following properties:

* The namespace and class name won't change;
* The method name won't change;
* The method signature (arguments and return value type) won't change;
* The semantic of what the method does won't change.

The implementation itself can change though. The only valid case for a change
in the stable API is in order to fix a security issue.

The stable API is based on a whitelist, tagged with `@api`. Therefore,
everything not tagged explicitly is not part of the stable API.

.. tip::

    Read more about the stable API in :doc:`/contributing/code/bc`.

.. tip::

    Any third party bundle should also publish its own stable API.

As of the latest stable release of Symfony, the following components have
a public tagged API:

* BrowserKit
* ClassLoader
* Console
* CssSelector
* DependencyInjection
* DomCrawler
* EventDispatcher
* Filesystem
* Finder
* HttpFoundation
* HttpKernel
* Process
* Routing
* Templating
* Translation
* Validator
* Yaml
