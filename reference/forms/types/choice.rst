.. index::
   single: Forms; Fields; ChoiceType

ChoiceType Field (select drop-downs, radio buttons & checkboxes)
================================================================

A multi-purpose field used to allow the user to "choose" one or more options.
It can be rendered as a ``select`` tag, radio buttons, or checkboxes.

To use this field, you must specify *either* ``choices`` or ``choice_loader`` option.

+-------------+------------------------------------------------------------------------------+
| Rendered as | can be various tags (see below)                                              |
+-------------+------------------------------------------------------------------------------+
| Options     | - `choices`_                                                                 |
|             | - `choice_attr`_                                                             |
|             | - `choice_label`_                                                            |
|             | - `choice_loader`_                                                           |
|             | - `choice_name`_                                                             |
|             | - `choice_translation_domain`_                                               |
|             | - `choice_value`_                                                            |
|             | - `choices_as_values`_ (deprecated)                                          |
|             | - `expanded`_                                                                |
|             | - `group_by`_                                                                |
|             | - `multiple`_                                                                |
|             | - `placeholder`_                                                             |
|             | - `preferred_choices`_                                                       |
+-------------+------------------------------------------------------------------------------+
| Overridden  | - `compound`_                                                                |
| options     | - `empty_data`_                                                              |
|             | - `error_bubbling`_                                                          |
+-------------+------------------------------------------------------------------------------+
| Inherited   | - `by_reference`_                                                            |
| options     | - `data`_                                                                    |
|             | - `disabled`_                                                                |
|             | - `error_mapping`_                                                           |
|             | - `inherit_data`_                                                            |
|             | - `label`_                                                                   |
|             | - `label_attr`_                                                              |
|             | - `label_format`_                                                            |
|             | - `mapped`_                                                                  |
|             | - `required`_                                                                |
|             | - `translation_domain`_                                                      |
+-------------+------------------------------------------------------------------------------+
| Parent type | :doc:`FormType </reference/forms/types/form>`                                |
+-------------+------------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType`         |
+-------------+------------------------------------------------------------------------------+

Example Usage
-------------

The easiest way to use this field is to specify the choices directly via
the ``choices`` option::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('isAttending', ChoiceType::class, array(
        'choices'  => array(
            'Maybe' => null,
            'Yes' => true,
            'No' => false,
        ),
    ));

This will create a ``select`` drop-down like this:

.. image:: /_images/reference/form/choice-example1.png
   :align: center

If the user selects ``No``, the form will return ``false`` for this field. Similarly,
if the starting data for this field is ``true``, then ``Yes`` will be auto-selected.
In other words, the **value** of each item is the value you want to get/set in PHP
code, while the **key** is what will be shown to the user.

Advanced Example (with Objects!)
--------------------------------

This field has a *lot* of options and most control how the field is displayed. In
this example, the underlying data is some ``Category`` object that has a ``getName()``
method::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use AppBundle\Entity\Category;
    // ...

    $builder->add('category', ChoiceType::class, [
        'choices' => [
            new Category('Cat1'),
            new Category('Cat2'),
            new Category('Cat3'),
            new Category('Cat4'),
        ],
        'choice_label' => function($category, $key, $index) {
            /** @var Category $category */
            return strtoupper($category->getName());
        },
        'choice_attr' => function($category, $key, $index) {
            return ['class' => 'category_'.strtolower($category->getName())];
        },
        'group_by' => function($category, $key, $index) {
            // randomly assign things into 2 groups
            return rand(0, 1) == 1 ? 'Group A' : 'Group B';
        },
        'preferred_choices' => function($category, $key, $index) {
            return $category->getName() == 'Cat2' || $category->getName() == 'Cat3';
        },
    ]);

You can also customize the `choice_name`_ and `choice_value`_ of each choice if
you need further HTML customization.

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

You can easily "group" options in a select by passing a multi-dimensional choices array::

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
            ]
        ],
    );

.. image:: /_images/reference/form/choice-example4.png
   :align: center

To get fancier, use the `group_by`_ option.

Field Options
-------------

choices
~~~~~~~

**type**: ``array`` **default**: ``array()``

This is the most basic way to specify the choices that should be used
by this field. The ``choices`` option is an array, where the array key
is the item's label and the array value is the item's value::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('inStock', ChoiceType::class, array(
        'choices' => array('In Stock' => true, 'Out of Stock' => false),
    ));

.. include:: /reference/forms/types/options/choice_attr.rst.inc

.. _reference-form-choice-label:

.. include:: /reference/forms/types/options/choice_label.rst.inc

choice_loader
~~~~~~~~~~~~~

**type**: :class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\ChoiceLoaderInterface`

The ``choice_loader`` can be used to only partially load the choices in cases where
a fully-loaded list is not necessary. This is only needed in advanced cases and
would replace the ``choices`` option.

.. include:: /reference/forms/types/options/choice_name.rst.inc

.. include:: /reference/forms/types/options/choice_translation_domain.rst.inc

.. include:: /reference/forms/types/options/choice_value.rst.inc

choices_as_values
~~~~~~~~~~~~~~~~~

This option is deprecated and you should remove it from your 3.x projects (removing
it will have *no* effect). For its purpose in 2.x, see the 2.7 documentation.

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/group_by.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

Overridden Options
------------------

compound
~~~~~~~~

**type**: ``boolean`` **default**: same value as ``expanded`` option

This option specifies if a form is compound. The value is by default
overridden by the value of the ``expanded`` option.

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The actual default value of this option depends on other field options:

* If ``multiple`` is ``false`` and ``expanded`` is ``false``, then ``''``
  (empty string);
* Otherwise ``array()`` (empty array).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

error_bubbling
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Set that error on this field must be attached to the field instead of
the parent field (the form in most cases).

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/by_reference.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/choice_type_translation_domain.rst.inc

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
| choice_translation_domain  | ``mixed``    | ``boolean``, ``null`` or ``string`` to determine if the value     |
|                            |              | should be translated.                                             |
+----------------------------+--------------+-------------------------------------------------------------------+
| is_selected                | ``callable`` | A callable which takes a ``ChoiceView`` and the selected value(s) |
|                            |              | and returns whether the choice is in the selected value(s).       |
+----------------------------+--------------+-------------------------------------------------------------------+
| placeholder_in_choices     | ``boolean``  | Whether the empty value is in the choice list.                    |
+----------------------------+--------------+-------------------------------------------------------------------+

.. tip::

    It's significantly faster to use the :ref:`form-twig-selectedchoice`
    test instead when using Twig.
