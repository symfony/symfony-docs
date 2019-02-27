Experimental Features
=====================

All Symfony features benefit from our :doc:`Backward Compatibility Promise
</contributing/code/bc>` to give developers the confidence to upgrade to new
versions safely and more often.

But sometimes, a new feature is controversial or you cannot find a convincing API.
In such cases, we prefer to gather feedback from real-world usage, adapt
the API, or remove it altogether. Doing so is not possible with a no BC-break
approach.

To avoid being bound to our backward compatibility promise, such features can
be marked as **experimental** and their classes and methods must be marked with
the ``@experimental`` tag.

A feature can be marked as being experimental for only one minor version, and
can never be introduced in an :ref:`LTS version <releases-lts>`. The core team
can decide to extend the experimental period for another minor version on a
case by case basis.

To ease upgrading projects using experimental features, the changelog must
explain backward incompatible changes and explain how to upgrade code.
