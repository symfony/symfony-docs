.. versionadded:: 2.3
    The ``method`` option was introduced in Symfony 2.3.

method
~~~~~~

**type**: ``string`` **default**: ``POST``

This option specifies the HTTP method used to submit the form's data. It's
value is rendered as the ``method`` attribute of the ``form`` element.
Possible values are:

* POST
* GET
* PUT
* DELETE
* PATCH

As PUT, DELETE and PATCH are not nativly supported by most clients, Symfony
simulates them, see :doc:`/cookbook/routing/method_parameters`.

.. note::
    Using ``PUT``, ``PATCH`` and ``DELETE`` is only allowed if
    :ref:`configuration-framework-http_method_override` is enabled.
