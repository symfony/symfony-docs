.. index::
   single: Forms; Fields; password

password Field Type
===================

The ``password`` field renders an input password text box.

+-------------+------------------------------------------------------------------------+
| Rendered as | ``input`` ``password`` field                                           |
+-------------+------------------------------------------------------------------------+
| Options     | - `always_empty`_ (deprecated as of 2.6)                               |
|             | - `reset_on_submit`_                                                   |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `disabled`_                                                          |
| options     | - `empty_data`_                                                        |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `mapped`_                                                            |
|             | - `max_length`_ (deprecated as of 2.5)                                 |
|             | - `read_only`_                                                         |
|             | - `required`_                                                          |
|             | - `trim`_                                                              |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`text </reference/forms/types/text>`                              |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType` |
+-------------+------------------------------------------------------------------------+

Field Options
-------------

always_empty
~~~~~~~~~~~~

.. caution::

    The ``always_empty`` option has been deprecated and will be removed in 3.0.
    Use the ``reset_on_submit`` option instead.

reset_on_submit
~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If set to true, the field will *always* render blank, even if the corresponding
field has a value. When set to false, the password field will be rendered
with the ``value`` attribute set to its true value only upon submission.

Put simply, if for some reason you want to render your password field
*with* the password value already entered into the box, set this to false
and submit the form.


Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

trim
~~~~

**type**: ``Boolean`` **default**: ``false``

If true, the whitespace of the submitted string value will be stripped
via the :phpfunction:`trim` function when the data is bound. This guarantees that
if a value is submitted with extra whitespace, it will be removed before
the value is merged back onto the underlying object.
