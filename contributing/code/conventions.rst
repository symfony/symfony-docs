Conventions
===========

The :doc:`standards` document describes the coding standards for the Symfony
projects and the internal and third-party bundles. This document describes
coding standards and conventions used in the core framework to make it more
consistent and predictable. You are encouraged to follow them in your own
code, but you don't need to.

.. _method-names:

Naming a Method
---------------

When an object has a "main" many relation with related "things"
(objects, parameters, ...), the method names are normalized:

* ``get()``
* ``set()``
* ``has()``
* ``all()``
* ``replace()``
* ``remove()``
* ``clear()``
* ``isEmpty()``
* ``add()``
* ``register()``
* ``count()``
* ``keys()``

The usage of these methods is only allowed when it is clear that there
is a main relation:

* a ``CookieJar`` has many ``Cookie`` objects;

* a Service ``Container`` has many services and many parameters (as services
  is the main relation, the naming convention is used for this relation);

* a Console ``Input`` has many arguments and many options. There is no "main"
  relation, and so the naming convention does not apply.

For many relations where the convention does not apply, the following methods
must be used instead (where ``XXX`` is the name of the related thing):

+----------------+-------------------+
| Main Relation  | Other Relations   |
+================+===================+
| ``get()``      | ``getXXX()``      |
+----------------+-------------------+
| ``set()``      | ``setXXX()``      |
+----------------+-------------------+
| n/a            | ``replaceXXX()``  |
+----------------+-------------------+
| ``has()``      | ``hasXXX()``      |
+----------------+-------------------+
| ``all()``      | ``getXXXs()``     |
+----------------+-------------------+
| ``replace()``  | ``setXXXs()``     |
+----------------+-------------------+
| ``remove()``   | ``removeXXX()``   |
+----------------+-------------------+
| ``clear()``    | ``clearXXX()``    |
+----------------+-------------------+
| ``isEmpty()``  | ``isEmptyXXX()``  |
+----------------+-------------------+
| ``add()``      | ``addXXX()``      |
+----------------+-------------------+
| ``register()`` | ``registerXXX()`` |
+----------------+-------------------+
| ``count()``    | ``countXXX()``    |
+----------------+-------------------+
| ``keys()``     | n/a               |
+----------------+-------------------+

.. note::

    While ``setXXX()`` and ``replaceXXX()`` are very similar, there is one notable
    difference: ``setXXX()`` may replace, or add new elements to the relation.
    ``replaceXXX()``, on the other hand, cannot add new elements. If an unrecognized
    key is passed to ``replaceXXX()`` it must throw an exception.

Writing a CHANGELOG Entry
-------------------------

When adding a new feature in a minor version or deprecating an existing
behavior, an entry to the relevant CHANGELOG(s) should be added.

New features and deprecations must be described in a file named
``CHANGELOG.md`` that should be at the root directory of the modified
Component, Bridge or Bundle.

The file must be written with the Markdown syntax and follow the following
conventions:

* The main title is always ``CHANGELOG``;

* Each entry must be added to a minor version section (like ``5.3``) as a list
  element;

* No third level sections are allowed;

* Messages should follow the :ref:`commit message conventions <commit-messages>`:
  should be short, capitalize the line, do not end with a period, use an
  imperative verb to start the line;

* New entries must be added on top of the list.

Here is a complete example for reference:

.. code-block:: markdown

    CHANGELOG
    =========

    5.3
    ---

     * Add `MagicConfig` that allows configuring things

.. note::

    The main ``CHANGELOG-*`` files at the ``symfony/symfony`` root directory
    are automatically generated when releases are prepared and should never be
    modified manually.

.. _contributing-code-conventions-deprecations:

Deprecating Code
----------------

From time to time, some classes and/or methods are deprecated in the framework;
that happens when a feature implementation cannot be changed because of
backward compatibility issues, but we still want to propose a "better"
alternative. In that case, the old implementation can be **deprecated**.

Deprecations must only be introduced on the next minor version of the impacted
component (or bundle, or bridge, or contract). They can exceptionally be
introduced on previous supported versions if they are critical.

A new class (or interface, or trait) cannot be introduced as deprecated, or
contain deprecated methods.

A new method cannot be introduced as deprecated.

A feature is marked as deprecated by adding a ``@deprecated`` PHPDoc to
relevant classes, methods, properties, ...::

    /**
     * @deprecated since Symfony 5.1.
     */

The deprecation message must indicate the version in which the feature was deprecated,
and whenever possible, how it was replaced::

    /**
     * @deprecated since Symfony 5.1, use Replacement instead.
     */

When the replacement is in another namespace than the deprecated class, its FQCN must be used::

    /**
     * @deprecated since Symfony 5.1, use A\B\Replacement instead.
     */

A deprecation must also be triggered to help people with the migration
(requires the ``symfony/deprecation-contracts`` package)::

    trigger_deprecation('symfony/package-name', '5.1', 'The "%s" class is deprecated, use "%s" instead.', Deprecated::class, Replacement::class);

When deprecating a whole class the ``trigger_deprecation()`` call should be placed
after the use declarations, like in this example from `ServiceRouterLoader`_::

    namespace Symfony\Component\Routing\Loader\DependencyInjection;

    use Symfony\Component\Routing\Loader\ContainerLoader;

    trigger_deprecation('symfony/routing', '4.4', 'The "%s" class is deprecated, use "%s" instead.', ServiceRouterLoader::class, ContainerLoader::class);

    /**
     * @deprecated since Symfony 4.4, use Symfony\Component\Routing\Loader\ContainerLoader instead.
     */
    class ServiceRouterLoader extends ObjectRouteLoader

The deprecation must be added to the ``CHANGELOG.md`` file of the impacted component:

.. code-block:: markdown

    4.4
    ---

    * Deprecate the `Deprecated` class, use `Replacement` instead

It must also be added to the ``UPGRADE.md`` file of the targeted minor version
(``UPGRADE-4.4.md`` in our example):

.. code-block:: markdown

    DependencyInjection
    -------------------

     * Deprecate the `Deprecated` class, use `Replacement` instead

Finally, its consequences must be added to the ``UPGRADE.md`` file of the next major version
(``UPGRADE-5.0.md`` in our example):

.. code-block:: markdown

    DependencyInjection
    -------------------

     * Remove the `Deprecated` class, use `Replacement` instead

All these tasks are mandatory and must be done in the same pull request.

Removing Deprecated Code
------------------------

Removing deprecated code can only be done once every two years, on the next
major version of the impacted component (``6.0`` branch, ``7.0`` branch, etc.).

When removing deprecated code, the consequences of the deprecation must be added
to the ``CHANGELOG.md`` file of the impacted component:

.. code-block:: markdown

    5.0
    ---

     * Remove the `Deprecated` class, use `Replacement` instead

This task is mandatory and must be done in the same pull request.

Naming Commands and Options
---------------------------

Commands and their options should be named and described using the English
imperative mood (i.e. 'run' instead of 'runs', 'list' instead of 'lists'). Using
the imperative mood is concise and consistent with similar command-line
interfaces (such as Unix man pages).

.. _`ServiceRouterLoader`: https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/Routing/Loader/DependencyInjection/ServiceRouterLoader.php
