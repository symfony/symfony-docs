.. index::
   single: Forms; Fields; choice

choice Field Type
=================

A multi-purpose field used to allow the user to "choose" one or more options.
It can be rendered as a ``select`` tag, radio buttons, or checkboxes.

To use this field, you must specify *either* the ``choice_list`` or ``choices``
option.

+-------------+------------------------------------------------------------------------------+
| Rendered as | can be various tags (see below)                                              |
+-------------+------------------------------------------------------------------------------+
| Options     | - `choices`_                                                                 |
|             | - `choice_list`_                                                             |
|             | - `multiple`_                                                                |
|             | - `expanded`_                                                                |
|             | - `preferred_choices`_                                                       |
|             | - `empty_value`_                                                             |
+-------------+------------------------------------------------------------------------------+
| Overridden  | - `empty_data`_                                                              |
| options     | - `compound`_                                                                |
|             | - `error_bubbling`_                                                          |
+-------------+------------------------------------------------------------------------------+
| Inherited   | - `required`_                                                                |
| options     | - `label`_                                                                   |
|             | - `label_attr`_                                                              |
|             | - `data`_                                                                    |
|             | - `read_only`_                                                               |
|             | - `disabled`_                                                                |
|             | - `error_mapping`_                                                           |
|             | - `mapped`_                                                                  |
|             | - `inherit_data`_                                                            |
|             | - `by_reference`_                                                            |
+-------------+------------------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                                    |
+-------------+------------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType`         |
+-------------+------------------------------------------------------------------------------+

Example Usage
-------------

The easiest way to use this field is to specify the choices directly via the
``choices`` option. The key of the array becomes the value that's actually
set on your underlying object (e.g. ``m``), while the value is what the
user sees on the form (e.g. ``Male``).

.. code-block:: php

    $builder->add('gender', 'choice', array(
        'choices'   => array('m' => 'Male', 'f' => 'Female'),
        'required'  => false,
    ));

By setting ``multiple`` to true, you can allow the user to choose multiple
values. The widget will be rendered as a multiple ``select`` tag or a series
of checkboxes depending on the ``expanded`` option:

.. code-block:: php

    $builder->add('availability', 'choice', array(
        'choices'   => array(
            'morning'   => 'Morning',
            'afternoon' => 'Afternoon',
            'evening'   => 'Evening',
        ),
        'multiple'  => true,
    ));

You can also use the ``choice_list`` option, which takes an object that can
specify the choices for your widget.

.. _forms-reference-choice-tags:

.. include:: /reference/forms/types/options/select_how_rendered.rst.inc

Field Options
-------------

choices
~~~~~~~

**type**: ``array`` **default**: ``array()``

This is the most basic way to specify the choices that should be used
by this field. The ``choices`` option is an array, where the array key
is the item value and the array value is the item's label::

    $builder->add('gender', 'choice', array(
        'choices' => array('m' => 'Male', 'f' => 'Female')
    ));

choice_list
~~~~~~~~~~~

**type**: ``Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface``

This is one way of specifying the options to be used for this field.
The ``choice_list`` option must be an instance of the ``ChoiceListInterface``.
For more advanced cases, a custom class that implements the interface
can be created to supply the choices.

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/empty_value.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/empty_data.rst.inc

compound
~~~~~~~~

**type**: ``boolean`` **default**: same value as ``expanded`` option

This option specifies if a form is compound. The value is by default
overridden by the value of the ``expanded`` option.

error_bubbling
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Set that error on this field must be attached to the field instead of
the parent field (the form in most cases).

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/by_reference.rst.inc

Field Variables
---------------

+------------------------+--------------+-------------------------------------------------------------------+
| Variable               | Type         | Usage                                                             |
+========================+==============+===================================================================+
| multiple               | ``Boolean``  | The value of the `multiple`_ option.                              |
+------------------------+--------------+-------------------------------------------------------------------+
| expanded               | ``Boolean``  | The value of the `expanded`_ option.                              |
+------------------------+--------------+-------------------------------------------------------------------+
| preferred_choices      | ``array``    | A nested array containing the ``ChoiceView`` objects of           |
|                        |              | choices which should be presented to the user with priority.      |
+------------------------+--------------+-------------------------------------------------------------------+
| choices                | ``array``    | A nested array containing the ``ChoiceView`` objects of           |
|                        |              | the remaining choices.                                            |
+------------------------+--------------+-------------------------------------------------------------------+
| separator              | ``string``   | The seperator to use between choice groups.                       |
+------------------------+--------------+-------------------------------------------------------------------+
| empty_value            | ``mixed``    | The empty value if not already in the list, otherwise             |
|                        |              | ``null``.                                                         |
+------------------------+--------------+-------------------------------------------------------------------+
| is_selected            | ``callable`` | A callable which takes a ``ChoiceView`` and the selected value(s) |
|                        |              | and returns whether the choice is in the selected value(s).       |
+------------------------+--------------+-------------------------------------------------------------------+
| empty_value_in_choices | ``Boolean``  | Whether the empty value is in the choice list.                    |
+------------------------+--------------+-------------------------------------------------------------------+

.. tip::

    It's significantly faster to use the :ref:`form-twig-selectedchoice` test
    instead when using Twig.
