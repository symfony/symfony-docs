cascade_validation
~~~~~~~~~~~~~~~~~~

**type**: Boolean **default**: false

Set this option to ``true`` to force validation on embedded form types.
For example, if you have a ``ProductType`` with an embedded ``CategoryType``,
setting ``cascade_validation`` to ``true`` on ``ProductType`` will cause
the data from ``CategoryType`` to also be validated.

.. tip::

    Instead of using this option, it is recommended that you use the ``Valid``
    constraint in your model to force validation on a child object stored on
    a property. This cascades only the validation but not the use of the
    ``validation_group`` option on child forms. You can read more about this
    in the section about :ref:`Embedding a Single Object <forms-embedding-single-object>`.

.. include:: /reference/forms/types/options/_error_bubbling_hint.rst.inc
