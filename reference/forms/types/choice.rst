ChoiceType Field (select drop-downs, radio buttons & checkboxes)
================================================================

A multi-purpose field used to allow the user to "choose" one or more options.
It can be rendered as a ``select`` tag, radio buttons, or checkboxes.

To use this field, you must specify *either* ``choices`` or ``choice_loader`` option.

+---------------------------+----------------------------------------------------------------------+
| Rendered as               | can be various tags (see below)                                      |
+---------------------------+----------------------------------------------------------------------+
| Default invalid message   | The selected choice is invalid.                                      |
+---------------------------+----------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                  |
+---------------------------+----------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                        |
+---------------------------+----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType` |
+---------------------------+----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Example Usage
-------------

The easiest way to use this field is to define the ``choices`` option to specify
the choices as an associative array where the keys are the labels displayed to
end users and the array values are the internal values used in the form field::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('isAttending', ChoiceType::class, [
        'choices'  => [
            'Maybe' => null,
            'Yes' => true,
            'No' => false,
        ],
    ]);

This will create a ``select`` drop-down like this:

.. image:: /_images/reference/form/choice-example1.png
    :alt: A choice list form input with the options "Maybe", "Yes" and "No".

If the user selects ``No``, the form will return ``false`` for this field. Similarly,
if the starting data for this field is ``true``, then ``Yes`` will be auto-selected.
In other words, the **choice** of each item is the value you want to get/set in PHP
code, while the **key** is the **label** that will be shown to the user.

Advanced Example (with Objects!)
--------------------------------

This field has a *lot* of options and most control how the field is displayed. In
this example, the underlying data is some ``Category`` object that has a ``getName()``
method::

    use App\Entity\Category;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('category', ChoiceType::class, [
        'choices' => [
            new Category('Cat1'),
            new Category('Cat2'),
            new Category('Cat3'),
            new Category('Cat4'),
        ],
        // "name" is a property path, meaning Symfony will look for a public
        // property or a public method like "getName()" to define the input
        // string value that will be submitted by the form
        'choice_value' => 'name',
        // a callback to return the label for a given choice
        // if a placeholder is used, its empty value (null) may be passed but
        // its label is defined by its own "placeholder" option
        'choice_label' => function (?Category $category): string {
            return $category ? strtoupper($category->getName()) : '';
        },
        // returns the html attributes for each option input (may be radio/checkbox)
        'choice_attr' => function (?Category $category): array {
            return $category ? ['class' => 'category_'.strtolower($category->getName())] : [];
        },
        // every option can use a string property path or any callable that get
        // passed each choice as argument, but it may not be needed
        'group_by' => function (): string {
            // randomly assign things into 2 groups
            return rand(0, 1) === 1 ? 'Group A' : 'Group B';
        },
        // a callback to return whether a category is preferred
        'preferred_choices' => function (?Category $category): bool {
            return $category && 100 < $category->getArticleCounts();
        },
    ]);

You can also customize the `choice_name`_ of each choice. You can learn more
about all of these options in the sections below.

.. caution::

    The *placeholder* is a specific field, when the choices are optional the
    first item in the list must be empty, so the user can unselect.
    Be sure to always handle the empty choice ``null`` when using callbacks.

.. _forms-reference-choice-tags:

.. include:: /reference/forms/types/options/select_how_rendered.rst.inc

Customizing each Option's Text (Label)
--------------------------------------

Normally, the array key of each item in the ``choices`` option is used as the
text that's shown to the user. But that can be completely customized via the
`choice_label`_ option. Check it out for more details.

.. _form-choices-simple-grouping:

Grouping Options
----------------

You can group the ``<option>`` elements of a ``<select>`` into ``<optgroup>``
by passing a multi-dimensional ``choices`` array::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('stockStatus', ChoiceType::class, [
        'choices' => [
            'Main Statuses' => [
                'Yes' => 'stock_yes',
                'No' => 'stock_no',
            ],
            'Out of Stock Statuses' => [
                'Backordered' => 'stock_backordered',
                'Discontinued' => 'stock_discontinued',
            ],
        ],
    ]);

.. image:: /_images/reference/form/choice-example4.png
    :alt: A choice list with the options "Yes" and "No" grouped under "Main Statuses" and the options "Backordered" and "Discontinued" under "Out of Stock Statuses".

To get fancier, use the `group_by`_ option instead.

Field Options
-------------

choices
~~~~~~~

**type**: ``array`` **default**: ``[]``

This is the most basic way to specify the choices that should be used
by this field. The ``choices`` option is an array, where the array key
is the item's label and the array value is the item's value::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('inStock', ChoiceType::class, [
        'choices' => [
            'In Stock' => true,
            'Out of Stock' => false,
        ],
    ]);

If there are choice values that are not scalar or the stringified
representation is not unique Symfony will use incrementing integers
as values. When the form gets submitted the correct values with the
correct types will be assigned to the model.

.. include:: /reference/forms/types/options/choice_attr.rst.inc

.. include:: /reference/forms/types/options/choice_filter.rst.inc

.. _reference-form-choice-label:

.. include:: /reference/forms/types/options/choice_label.rst.inc

.. _reference-form-choice-loader:

.. include:: /reference/forms/types/options/choice_loader.rst.inc

.. include:: /reference/forms/types/options/choice_name.rst.inc

.. include:: /reference/forms/types/options/choice_translation_domain_enabled.rst.inc

.. include:: /reference/forms/types/options/choice_translation_parameters.rst.inc

.. include:: /reference/forms/types/options/choice_value.rst.inc

.. include:: /reference/forms/types/options/duplicate_preferred_choices.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/group_by.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/placeholder_attr.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

Overridden Options
------------------

compound
~~~~~~~~

**type**: ``boolean`` **default**: same value as ``expanded`` option

This option specifies if a form is compound. The value is by default
overridden by the value of the ``expanded`` option.

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The actual default value of this option depends on other field options:

* If ``multiple`` is ``false`` and ``expanded`` is ``false``, then ``''``
  (empty string);
* Otherwise ``[]`` (empty array).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

error_bubbling
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Set that error on this field must be attached to the field instead of
the parent field (the form in most cases).

.. include:: /reference/forms/types/options/choice_type_trim.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/by_reference.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. include:: /reference/forms/types/options/choice_type_translation_domain.rst.inc

.. include:: /reference/forms/types/options/label_translation_parameters.rst.inc

.. include:: /reference/forms/types/options/attr_translation_parameters.rst.inc

.. include:: /reference/forms/types/options/help_translation_parameters.rst.inc

Field Variables
---------------

+----------------------------+--------------+-------------------------------------------------------------------+
| Variable                   | Type         | Usage                                                             |
+============================+==============+===================================================================+
| multiple                   | ``boolean``  | The value of the `multiple`_ option.                              |
+----------------------------+--------------+-------------------------------------------------------------------+
| expanded                   | ``boolean``  | The value of the `expanded`_ option.                              |
+----------------------------+--------------+-------------------------------------------------------------------+
| preferred_choices          | ``array``    | A nested array containing the ``ChoiceView`` objects of           |
|                            |              | choices which should be presented to the user with priority.      |
+----------------------------+--------------+-------------------------------------------------------------------+
| choices                    | ``array``    | A nested array containing the ``ChoiceView`` objects of           |
|                            |              | the remaining choices.                                            |
+----------------------------+--------------+-------------------------------------------------------------------+
| separator                  | ``string``   | The separator to use between choice groups.                       |
+----------------------------+--------------+-------------------------------------------------------------------+
| placeholder                | ``mixed``    | The empty value if not already in the list, otherwise             |
|                            |              | ``null``.                                                         |
+----------------------------+--------------+-------------------------------------------------------------------+
| placeholder_attr           | ``array``    | The value of the `placeholder_attr`_ option.                      |
+----------------------------+--------------+-------------------------------------------------------------------+
| choice_translation_domain  | ``mixed``    | ``boolean``, ``null`` or ``string`` to determine if the value     |
|                            |              | should be translated.                                             |
+----------------------------+--------------+-------------------------------------------------------------------+
| is_selected                | ``callable`` | A callable which takes a ``ChoiceView`` and the selected value(s) |
|                            |              | and returns whether the choice is in the selected value(s).       |
+----------------------------+--------------+-------------------------------------------------------------------+
| placeholder_in_choices     | ``boolean``  | Whether the empty value is in the choice list.                    |
+----------------------------+--------------+-------------------------------------------------------------------+

.. tip::

    In Twig template, instead of using ``is_selected()``, it's significantly
    faster to use the :ref:`selectedchoice <form-twig-selectedchoice>` test.

Accessing Form Choice Data
..........................

The ``form.vars`` variable of each choice entry holds data such as whether the
choice is selected or not. If you need to get the full list of choices data and
values, use the ``choices`` variable from the parent form of the choice entry
(which is the ``ChoiceType`` itself) with ``form.parent.vars.choices``::

.. code-block:: twig

    {# `true` or `false`, whether the current choice is selected as radio or checkbox #}
    {{ form.vars.data }}

    {# the current choice value (i.e a category name when `'choice_value' => 'name'` #}
    {{ form.vars.value }}

    {# a map of `ChoiceView` or `ChoiceGroupView` instances indexed by choice values or group names #}
    {{ form.parent.vars.choices }}

Following the same advanced example as above (where choices values are entities),
the ``Category`` object is inside ``form.parent.vars.choices[key].data``::

.. code-block:: html+twig

    {% block _form_categories_entry_widget %}
        {% set entity = form.parent.vars.choices[form.vars.value].data %}

        <tr>
            <td>{{ form_widget(form) }}</td>
            <td>{{ form.vars.label }}</td>
            <td>
                {{ entity.name }} | {{ entity.group }}
            </td>
        </tr>
    {% endblock %}
