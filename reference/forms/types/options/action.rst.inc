action
~~~~~~

.. versionadded:: 2.3
    The ``action`` option was introduced in Symfony 2.3.

**type**: ``string`` **default**: empty string

This option specifies where to send the form's data on submission (usually
a URI). Its value is rendered as the ``action`` attribute of the ``form``
element. An empty value is considered a same-document reference, i.e. the
form will be submitted to the same URI that rendered the form.
