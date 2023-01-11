The Release Process
===================

This document explains the process followed by the Symfony project to develop,
release and maintain its different versions.

Symfony releases follow the `semantic versioning`_ strategy and they are
published through a *time-based model*:

* A new **Symfony patch version** (e.g. 5.4.12, 6.1.9) comes out roughly every
  month. It only contains bug fixes, so you can safely upgrade your applications;
* A new **Symfony minor version** (e.g. 5.4, 6.0, 6.1) comes out every *six months*:
  one in *May* and one in *November*. It contains bug fixes and new features,
  can contain new deprecations but it doesn't include any breaking change,
  so you can safely upgrade your applications;
* A new **Symfony major version** (e.g. 5.0, 6.0, 7.0) comes out every *two years*
  in November of odd years (e.g. 2019, 2021, 2023). It can contain breaking changes,
  so you may need to do some changes in your applications before upgrading.

.. tip::

    `Subscribe to Symfony Release notifications`_ to receive an email when a new
    Symfony version is published or when a Symfony version reaches its end of life.

.. _contributing-release-development:

Development
-----------

.. note::

    The Symfony project is an open-source community-driven development framework.
    There is no roadmap written or defined in advance. Every feature request
    may or may not be developed in future versions based on the community.
    Symfony Core Team members can help move things forward if there's enough interest.

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

    Check out the `Symfony Release`_ to learn more about any specific version.

.. _contributing-release-maintenance:
.. _symfony-versions:
.. _releases-lts:

Maintenance
-----------

Starting from the Symfony 3.x branch, the number of minor versions is limited to
five per branch (X.0, X.1, X.2, X.3 and X.4). The last minor version of a branch
(e.g. 5.4, 6.4) is considered a **long-term support version** and the other
ones are considered **standard versions**:

=======================  =====================  ================================
Version Type             Bugs are fixed for...  Security issues are fixed for...
=======================  =====================  ================================
Standard                 8 months               8 months
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
adds a new preferred one alongside. Read the
:ref:`conventions <contributing-code-conventions-deprecations>` document to
learn more about how deprecations are handled in Symfony.

.. _major-version-development:

This deprecation policy also requires a custom development process for major
versions (6.0, 7.0, etc.) In those cases, Symfony develops at the same time
two versions: the new major one (e.g. 6.0) and the latest version of the
previous branch (e.g. 5.4).

Both versions have the same new features, but they differ in the deprecated
features. The oldest version (5.4 in this example) contains all the deprecated
features whereas the new version (6.0 in this example) removes all of them.

This allows you to upgrade your projects to the latest minor version (e.g. 5.4),
see all the deprecation messages and fix them. Once you have fixed all those
deprecations, you can upgrade to the new major version (e.g. 6.0) without
effort, because it contains the same features (the only difference are the
deprecated features, which your project no longer uses).

PHP Compatibility
-----------------

The **minimum** PHP version is decided for each **major** Symfony version by consensus
amongst the :doc:`core team </contributing/code/core_team>` and documented as
part of the :ref:`technical requirements for running Symfony applications
<symfony-tech-requirements>`.

Throughout each Symfony release's support lifetime, all released versions of PHP
including new major versions will be supported. In this way, the **maximum** supported
version of PHP for a maintained Symfony release is the latest released
one that is publicly available.

For out-of-support releases of Symfony, the latest PHP version at time of EOL is the last
supported PHP version. Newer versions of PHP may or may not function.

.. note::

    By exception to the rule, bumping the minimum **minor** version of PHP is
    possible for a **minor** Symfony version when this helps fix important
    issues.

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
.. _`Subscribe to Symfony Release notifications`: https://symfony.com/account/notifications
.. _`Symfony Release`: https://symfony.com/releases
.. _`professional Symfony support`: https://sensiolabs.com/
