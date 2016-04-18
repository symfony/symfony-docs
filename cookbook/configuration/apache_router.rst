.. index::
    single: Apache Router

How to Use the Apache Router
============================

.. caution::

    **Using the Apache Router is no longer considered a good practice**.
    The small increase obtained in the application routing performance is not
    worth the hassle of continuously updating the routes configuration.

    The Apache Router will be removed in Symfony 3 and it's highly recommended
    to not use it in your applications.

Symfony, while fast out of the box, also provides various ways to increase that
speed with a little bit of tweaking. One of these ways is by letting Apache
handle routes directly, rather than using Symfony for this task.

.. caution::

    Apache router was deprecated in Symfony 2.5 and removed in Symfony 3.0.
    Since the PHP implementation of the Router was improved, performance gains
    were no longer significant (while it's very hard to replicate the same
    behavior).
