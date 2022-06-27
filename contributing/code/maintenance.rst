Maintenance
===========

During the lifetime of a minor version, new releases (patch versions) are
published on a monthly basis. This document describes the boundaries of
acceptable changes.

**Bug fixes** are accepted under the following conditions:

* The change does not break valid unit tests;
* New unit tests cover the bug fix;
* The current buggy behavior is not widely used as a "feature".

.. note::

    When documentation (or PHPDoc) is not in sync with the code, code behavior
    should always be considered as being the correct one.

To avoid backward compatibility breaks, we tend to be very strict about changes
accepted for patch versions.

Besides bug fixes, other minor changes might be accepted in a patch version on
a case by case basis:

* **Newer versions of PHP**: Fixes that add support for newer versions of PHP
  are acceptable if they don't break the unit test suite, but we never add
  support for newer PHP features;

* **Newer versions of popular OSes**: Fixes that add support for newer versions
  of popular OSes (Linux, MacOS and Windows) are acceptable if they don't break
  the unit test suite, but we never add support for newer PHP features or newer
  versions of OSes;

* **Translations**: Translation updates and additions are always merged in the
  oldest maintained branch;

* **External data**: Updates for external data included in Symfony can be
  updated (like ICU for instance);

* **Version updates for Composer dependencies**: Changing the minimal version
  of a dependency is possible, bumping to a major one or increasing PHP
  minimal version is not;

* **Tests**: Tests that increase the code coverage can be added.

The following changes are **generally not accepted** in a patch version, except
on a case by case basis (mostly when this is related to fixing a security
issue):

* **Performance improvement**: Performance improvement should only be accepted
  if the changes are local (located in one class) and only for algorithmic
  issues (any such patches must come with numbers that show a significant
  improvement on real-world code);

* **Coding standard and refactoring**: Coding standard fixes or code
  refactoring are almost never accepted except for consistency with the
  existing code base, if they are not too invasive, and if merging them into
  higher branches would not lead to complex branch merging.

* **Adding new classes or non private methods**: While working on a bug fix,
  never introduce new classes or public/protected methods (or global
  functions).

* **Adding configuration options**: Introducing new configuration options is
  never allowed.

* **Adding new deprecations**: After a version reaches stability, new
  deprecations cannot be added anymore.

Anything not explicitly listed above should be done on the next minor or major
version instead. For instance, the following changes are never accepted in a
patch version:

* **New features**;

* **Security hardening**;

* **Backward compatibility breaks**: Note that backward compatibility breaks
  can be done when fixing a security issue if it would not be possible to fix
  it otherwise;

* **Support for external platforms**: Adding support for new platforms (like
  Google App Engine) cannot be done in patch versions;

* **Exception messages**: Exception messages must not be changed as some
  automated systems might rely on them (even if this is not recommended);

* **Adding new Composer dependencies**;

* **Support for newer major versions of Composer dependencies**: Taking into
  account support for newer versions of an existing dependency is not
  acceptable.

* **Web design**: Changing the web design of built-in pages like exceptions,
  the toolbar or the profiler is not allowed.

.. note::

    This policy is designed to enable a continuous upgrade path that allows one
    to move forward with the newest Symfony versions in the safest way. One should
    be able to move PHP versions, OS or Symfony versions almost independently.
    That's the reason why supporting the latest PHP versions or OS features is
    considered as bug fixes.
