``inherit_data``
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

This option determines if the form will inherit data from its parent form.
This can be useful if you have a set of fields that are duplicated across
multiple forms. See :doc:`/form/inherit_data_option`.

.. warning::

    When a field has the ``inherit_data`` option set, it uses the data of
    the parent form as is. This means that
    :doc:`Data Transformers </form/data_transformers>` won't be
    applied to that field.

.. warning::

    Forms with ``inherit_data`` set to ``true`` do not dispatch the
    ``PRE_SET_DATA`` and ``POST_SET_DATA`` :doc:`form events </form/events>`,
    because they don't have their own data lifecycle. If you need to modify
    fields dynamically based on data, add the event listeners to the parent
    form instead.
