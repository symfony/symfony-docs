cascade_validation
~~~~~~~~~~~~~~~~~~

.. caution::

    The ``cascade_validation`` option has been deprecated in Symfony 2.8 and will be removed
    in 3.0. Instead, use the ``Valid`` constraint in your model to cascade validation. Be aware
    of the fact that the ``validation_group`` option will not be considered for child forms.

**type**: ``boolean`` **default**: ``false``

Set this option to ``true`` to force validation on embedded form types.
For example, if you have a ``ProductType`` with an embedded ``CategoryType``,
setting ``cascade_validation`` to ``true`` on ``ProductType`` will cause
the data from ``CategoryType`` to also be validated.

.. tip::

    Instead of using this option, it is recommended that you use the :doc:`Valid </reference/constraints/Valid>`
    constraint in your model to force validation on a child object stored
    on a property. This cascades only the validation but not the use of
    the :ref:`validation_groups <book-forms-validation-groups>` option on child forms. You can read more
    about this in the section about :ref:`Embedding a Single Object <forms-embedding-single-object>`.

.. include:: /reference/forms/types/options/_error_bubbling_hint.rst.inc
