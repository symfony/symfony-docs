``inherit_data``
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

This option determines if the form will inherit data from its parent form.
This can be useful if you have a set of fields that are duplicated across
multiple forms. See :doc:`/form/inherit_data_option`.

.. caution::

    When a field has the ``inherit_data`` option set, it uses the data of
    the parent form as is. This means that
    :doc:`Data Transformers </form/data_transformers>` will not be
    applied to that field.
