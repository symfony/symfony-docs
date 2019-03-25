The Release Process
===================

This document explains the process followed by the Symfony project to develop,
release and maintain its different versions.

Symfony releases follow the `semantic versioning`_ strategy and they are
published through a *time-based model*:

* A new **Symfony patch version** (e.g. 2.8.15, 4.1.7) comes out roughly every
  month. It only contains bug fixes, so you can safely upgrade your applications;
* A new **Symfony minor version** (e.g. 2.8, 3.2, 4.1) comes out every *six months*:
  one in *May* and one in *November*. It contains bug fixes and new features, but
  it doesn't include any breaking change, so you can safely upgrade your applications;
* A new **Symfony major version** (e.g. 3.0, 4.0) comes out every *two years*.
  It can contain breaking changes, so you may need to do some changes in your
  applications before upgrading.

.. tip::

    `Subscribe to Symfony Roadmap notifications`_ to receive an email when a new
    Symfony version is published or when a Symfony version reaches its end of life.

.. _contributing-release-development:

Development
-----------

The full development period for any major or minor version lasts six months and
is divided into two phases:

* **Development**: *Four months* to add new features and to enhance existing
  ones;

* **Stabilization**: *Two months* to fix bugs, prepare the release, and wait
  for the whole Symfony ecosystem (third-party libraries, bundles, and
  projects using Symfony) to catch up.

During the development phase, any new feature can be reverted if it won't be
finished in time or if it won't be stable enough to be included in the current
final release.

.. tip::

    Check out the `Symfony Roadmap`_ to learn more about any specific version.

.. _contributing-release-maintenance:
.. _symfony-versions:
.. _releases-lts:

Maintenance
-----------

Starting from the Symfony 3.x branch, the number of minor versions is limited to
five per branch (X.0, X.1, X.2, X.3 and X.4). The last minor version of a branch
(e.g. 3.4, 4.4, 5.4) is considered a **long-term support version** and the other
ones are considered **standard versions**:

=======================  =====================  ================================
Version Type             Bugs are fixed for...  Security issues are fixed for...
=======================  =====================  ================================
Standard                 8 months               14 months
Long-Term Support (LTS)  3 years                4 years
=======================  =====================  ================================

.. note::

    After the active maintenance of a Symfony version has ended, you can get
    `professional Symfony support`_ from SensioLabs, the company which sponsors
    the Symfony project.

.. _deprecations:

Backward Compatibility
----------------------

Our :doc:`Backward Compatibility Promise </contributing/code/bc>` is very
strict and allows developers to upgrade with confidence from one minor version
of Symfony to the next one.

When a feature implementation cannot be replaced with a better one without
breaking backward compatibility, Symfony deprecates the old implementation and
adds a new preferred one along side. Read the
:ref:`conventions <contributing-code-conventions-deprecations>` document to
learn more about how deprecations are handled in Symfony.

Rationale
---------

This release process was adopted to give more *predictability* and
*transparency*. It was discussed based on the following goals:

* Shorten the release cycle (allow developers to benefit from the new
  features faster);
* Give more visibility to the developers using the framework and Open-Source
  projects using Symfony;
* Improve the experience of Symfony core contributors: everyone knows when a
  feature might be available in Symfony;
* Coordinate the Symfony timeline with popular PHP projects that work well
  with Symfony and with projects using Symfony;
* Give time to the Symfony ecosystem to catch up with the new versions
  (bundle authors, documentation writers, translators, ...);
* Give companies a strict and predictable timeline they can rely on to plan
  their own projects development.

The six month period was chosen as two releases fit in a year. It also allows
for plenty of time to work on new features and it allows for non-ready
features to be postponed to the next version without having to wait too long
for the next cycle.

The dual maintenance mode was adopted to make every Symfony user happy. Fast
movers, who want to work with the latest and the greatest, use the standard
version: a new version is published every six months, and there is a two months
period to upgrade. Companies wanting more stability use the LTS versions: a new
version is published every two years and there is a year to upgrade.

.. _`semantic versioning`: https://semver.org/
.. _`Subscribe to Symfony Roadmap notifications`: https://symfony.com/account
.. _`Symfony Roadmap`: https://symfony.com/roadmap#checker
.. _`professional Symfony support`: https://sensiolabs.com/
