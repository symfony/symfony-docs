.. versionadded:: 2.3
    The ``method`` option was introduced in Symfony 2.3.

method
~~~~~~

**type**: ``string`` **default**: ``POST``

This option specifies the HTTP method used to submit the form's data. Possible
values are:

* POST
* GET
* PUT
* DELETE
* PATCH

.. note::
    Using ``PUT``, ``PATCH`` and ``DELETE`` is only allowed if
    :ref:`configuration-framework-http_method_override` is enabled.
