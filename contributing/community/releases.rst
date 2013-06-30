The Release Process
===================

This document explains the Symfony release process (Symfony being the code
hosted on the main ``symfony/symfony`` `Git repository`_).

Symfony manages its releases through a *time-based model*; a new Symfony
release comes out every *six months*: one in *May* and one in *November*.

.. note::

    This release process has been adopted as of Symfony 2.2, and all the
    "rules" explained in this document must be strictly followed as of Symfony
    2.4.

.. _contributing-release-development:

Development
-----------

The six-months period is divided into two phases:

* *Development*: *Four months* to add new features and to enhance existing
  ones;

* *Stabilisation*: *Two months* to fix bugs, prepare the release, and wait
  for the whole Symfony ecosystem (third-party libraries, bundles, and
  projects using Symfony) to catch up.

During the development phase, any new feature can be reverted if it won't be
finished in time or if it won't be stable enough to be included in the current
final release.

.. _contributing-release-maintenance:

Maintenance
-----------

Each Symfony version is maintained for a fixed period of time, depending on
the type of the release. We have two maintenance periods:

* *Bug fixes and security fixes*: During this period, all issues can be fixed.
  The end of this period is referenced as being the *end of maintenance* of a
  release.

* *Security fixes only*: During this period, only security related issues can
  be fixed. The end of this period is referenced as being the *end of
  life* of a release.

Standard Releases
~~~~~~~~~~~~~~~~~

A standard release is maintained for an *eight month* period for bug fixes,
and for a *fourteen month* period for security issue fixes.

Long Term Support Releases
~~~~~~~~~~~~~~~~~~~~~~~~~~

Every two years, a new Long Term Support Release (aka LTS release) is
published. Each LTS release is supported for a *three year* period for bug
fixes, and for a *four year* period for security issue fixes.

.. note::

    Paid support after the three year support provided by the community can
    also be bought from `SensioLabs`_.

Schedule
--------

Below is the schedule for the first few versions that use this release model:

.. image:: /images/release-process.jpg
   :align: center

* **Yellow** represents the Development phase
* **Blue** represents the Stabilisation phase
* **Green** represents the Maintenance period

This results in very predictable dates and maintenance periods:

+---------+---------+---------------------+-------------+
| Version | Release | End of Maintenance  | End of Life |
+=========+=========+=====================+=============+
| 2.0     | 07/2011 | 03/2013 (20 months) | 09/2013     |
+---------+---------+---------------------+-------------+
| 2.1     | 09/2012 | 05/2013 (9 months)  | 11/2013     |
+---------+---------+---------------------+-------------+
| 2.2     | 03/2013 | 11/2013 (8 months)  | 05/2014     |
+---------+---------+---------------------+-------------+
| **2.3** | 05/2013 | 05/2016 (36 months) | 05/2017     |
+---------+---------+---------------------+-------------+
| 2.4     | 11/2013 | 07/2014 (8 months)  | 01/2015     |
+---------+---------+---------------------+-------------+
| 2.5     | 05/2014 | 01/2015 (8 months)  | 07/2016     |
+---------+---------+---------------------+-------------+
| 2.6     | 11/2014 | 07/2015 (8 months)  | 01/2016     |
+---------+---------+---------------------+-------------+
| **2.7** | 05/2015 | 05/2018 (36 months) | 05/2019     |
+---------+---------+---------------------+-------------+
| 2.8     | 11/2015 | 07/2016 (8 months)  | 01/2017     |
+---------+---------+---------------------+-------------+
| ...     | ...     | ...                 | ...         |
+---------+---------+---------------------+-------------+

.. tip::

    If you want to learn more about the timeline of any given Symfony version,
    use the online `timeline calculator`_. You can also get all data as a JSON
    string via a URL like `http://symfony.com/roadmap.json?version=2.x`.

Backward Compatibility
----------------------

After the release of Symfony 2.3, backward compatibility will be kept at all
cost. If it is not possible, the feature, the enhancement, or the bug fix will
be scheduled for the next major version: Symfony 3.0.

.. note::

    The work on Symfony 3.0 will start whenever enough major features breaking
    backward compatibility are waiting on the todo-list.

Deprecations
------------

When a feature implementation cannot be replaced with a better one without
breaking backward compatibility, there is still the possibility to deprecate
the old implementation and add a new preferred one along side. Read the
:ref:`conventions<contributing-code-conventions-deprecations>` document to
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
  (bundle authors, documentation writers, translators, ...).

The six month period was chosen as two releases fit in a year. It also allows
for plenty of time to work on new features and it allows for non-ready
features to be postponed to the next version without having to wait too long
for the next cycle.

The dual maintenance mode was adopted to make every Symfony user happy. Fast
movers, who want to work with the latest and the greatest, use the standard
releases: a new version is published every six months, and there is a two
months period to upgrade. Companies wanting more stability use the LTS
releases: a new version is published every two years and there is a year to
upgrade.

.. _Git repository: https://github.com/symfony/symfony
.. _SensioLabs:     http://sensiolabs.com/
.. _roadmap:        http://symfony.com/roadmap
.. _`timeline calculator`: http://symfony.com/roadmap
