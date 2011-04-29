.. index::
   single: Forms; Fields; choice

``choice`` Field Type
=====================

A multi-purpose field used to allow the user to "choose" one or more options. It
can be rendered as a ``select`` tag, radio buttons, or checkboxes.

To use this field, you must specify *either* the ``choice_list`` or ``choices``
option.

+-------------+-----------------------------------------------------------------------------+
| Rendered as | can be various tags (see below)                                             |
+-------------+-----------------------------------------------------------------------------+
| Options     | - ``choices``                                                               |
|             | - ``choice_list``                                                           |
|             | - ``multiple``                                                              |
|             | - ``expanded``                                                              |
|             | - ``preferred_choices``                                                     |
|             | - ``required``                                                              |
|             | - ``label``                                                                 |
|             | - ``read_only``                                                             |
|             | - ``error_bubbling``                                                        |
+-------------+-----------------------------------------------------------------------------+
| Parent type | :doc:`form</reference/forms/types/form>` (if expanded), ``field`` otherwise |
+-------------+-----------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType`        |
+-------------+-----------------------------------------------------------------------------+

.. _forms-reference-choice-tags:

Select tag, Checkboxes or Radio Buttons
---------------------------------------

This field may be rendered as one of several different HTML fields, depending on
the ``expanded`` and ``multiple`` options:

+------------------------------------------+----------+----------+
| element type                             | expanded | multiple |
+==========================================+==========+==========+
| select tag                               | false    | false    |
+------------------------------------------+----------+----------+
| select tag (with ``multiple`` attribute) | false    | true     |
+------------------------------------------+----------+----------+
| radio buttons                            | true     | false    |
+------------------------------------------+----------+----------+
| checkboxes                               | true     | true     |
+------------------------------------------+----------+----------+

Adding an "empty value"
-----------------------

If you're using the non-expanded version of the type (i.e. a ``select`` tag)
element and you'd like to have a blank entry (e.g. "Choose an option") at the
top of the select box, you can easily do so:

* Set the ``multiple`` option to false
* Set the ``required`` option to false

With these two options, a blank choice will display at the top of the select
box. To customize what that entry says, add the following when rendering the
field:

.. configuration-block::

    .. code-block:: jinja

        {{ form_widget(form.foo_choices, { 'empty_value': 'Choose an option' }) }}

    .. code-block:: php
    
        <?php echo $view['form']->widget($form['foo_choices'], array('empty_value' => 'Choose an option')) ?>

Options
-------

* ``choices`` [type: array]
    This is the most basic way to specify the choices that should be used
    by this field. The ``choices`` option is an array, where the array key
    is the item value and the array value is the item's label:
    
    .. code-block:: php
    
        $builder->add('gender', 'choice', array(
            'choices' => array('m' => 'Male', 'f' => 'Female')
        ));

* ``choice_list`` [type: ``Symfony\Component\Form\ChoiceList\ChoiceListInterface``]
    This is one way of specifying the options to be used for this field.
    The ``choice_list`` option must be an instance of the ``ChoiceListInterface``.
    For more advanced cases, a custom class that implements the interface
    can be created to supply the choices.

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
