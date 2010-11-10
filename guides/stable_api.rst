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

.. note::

    The stable API is based on a whitelist. Therefore, everything not listed
    explicitly in this document is not part of the stable API.

.. note::

    This is a work in progress and the definitive list will be published when
    Symfony2 final will be released. In the meantime, if you think that some
    methods deserve to be in this list, please start a discussion on the
    Symfony developer mailing-list.

.. tip::

    Any method part of the stable API is marked as such on the Symfony2 API
    website (has the ``@stable`` annotation).

.. tip::

    Any third party bundle should also publish its own stable API.

HttpKernel Component
--------------------

* HttpKernelInterface:::method:`Symfony\\Components\\HttpKernel\\HttpKernelInterface::handle`
