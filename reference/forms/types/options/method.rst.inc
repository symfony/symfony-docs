.. versionadded:: 2.3
    The ``method`` option was introduced in Symfony 2.3.

method
~~~~~~

**type**: ``string`` **default**: ``POST``

This option specifies the HTTP method used to submit the form's data. Its
value is rendered as the ``method`` attribute of the ``form`` element and is
used to decide whether to process the form submission in the
``handleRequest()`` method after submission. Possible values are:

* POST
* GET
* PUT
* DELETE
* PATCH

.. note:

    If not natively supported by a client, Symfony allows to simulate PUT,
    DELETE and PATCH requests by using a POST request instead and passing the
    intended "real" method by either using a ``X-HTTP-Method-Override``
    request header or by passing it via the special ``_method`` data field.
    For more information see :doc:`/cookbook/routing/method_parameters`.
