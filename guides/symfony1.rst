Symfony2 for symfony 1 users
============================

Applications
------------

In a symfony 1 project, it is common to have several applications: one for the
frontend and one for the backend for instance.

In a Symfony2 project, you only need to create one application (a blog
application, an intranet application, ...). Most of the time, if you want to
create a second application, you'd better create another project and share
some bundles between them.

And if you need to separate the frontend and the backend features of some
bundles, create sub-namespaces for controllers, sub-directories for templates,
different semantic configurations, separate routing configurations, and so on.

.. tip::

    Read the definition of a :term:`Project`, an :term:`Application`, and a
    :term:`Bundle` in the glossary.