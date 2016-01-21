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
|             | - `choices_as_values`_                                                       |
|             | - `choice_loader`_                                                           |
|             | - `choice_label`_                                                            |
|             | - `choice_attr`_                                                             |
|             | - `placeholder`_                                                             |
|             | - `expanded`_                                                                |
|             | - `multiple`_                                                                |
|             | - `preferred_choices`_                                                       |
|             | - `group_by`_                                                                |
|             | - `choice_value`_                                                            |
|             | - `choice_name`_                                                             |
|             | - `choice_list`_ (deprecated)                                                |
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
|             | - `mapped`_                                                                  |
|             | - `read_only`_ (deprecated as of 2.8)                                        |
|             | - `required`_                                                                |
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
        // *this line is important*
        'choices_as_values' => true,
    ));

This will create a ``select`` drop-down like this:

.. image:: /images/reference/form/choice-example1.png
   :align: center

If the user selects ``No``, the form will return ``false`` for this field. Similarly,
if the starting data for this field is ``true``, then ``Yes`` will be auto-selected.
In other words, the **value** of each item is the value you want to get/set in PHP
code, while the **key** is what will be shown to the user.

.. caution::

    The ``choices_as_values`` *must* be set to ``true`` in all cases. This activates
    the "new" choice type API, which was introduced in Symfony 2.7. If you omit this
    option (or set it to ``false``), you'll activate the old API, which is deprecated
    and will be removed in 3.0. To read about the old API, read an older version of
    the docs.

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
        'choices_as_values' => true,
        'choice_label' => function($category, $key, $index) {
            /** @var Category $category */
            return strtoupper($category->getName());
        },
        'choice_attr' => function($category, $key, $index) {
            return ['class' => 'category_'.strtolower($category->getName())];
        },
        'group_by' => function($category, $key, $index) {
            // randomly assign things into 2 groups
            return rand(0, 1) == 1 ? 'Group A' : 'Group B'
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
        'choices_as_values' => true,
    );

.. image:: /images/reference/form/choice-example4.png
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
        // always include this
        'choices_as_values' => true,
    ));

choices_as_values
~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: false

.. versionadded:: 2.7

    The ``choices_as_values`` option was introduced in Symfony 2.7.

The ``choices_as_values`` option was added to keep backward compatibility with the
*old* way of handling the ``choices`` option. When set to ``false`` (or omitted),
the choice keys are used as the underlying value and the choice values are shown
to the user.

* Before 2.7 (and deprecated now)::

    $builder->add('gender', 'choice', array(
        // Shows "Male" to the user, returns "m" when selected
        'choices'  => array('m' => 'Male', 'f' => 'Female'),
        // before 2.7, this option didn't actually exist, but the
        // behavior was equivalent to setting this to false in 2.7.
        'choices_as_values' => false,
    ));

* Since 2.7::

    $builder->add('gender', ChoiceType::class, array(
        // Shows "Male" to the user, returns "m" when selected
        'choices' => array('Male' => 'm', 'Female' => 'f'),
        'choices_as_values' => true,
    ));

In Symfony 3.0, the ``choices_as_values`` option doesn't exist, but the ``choice``
type behaves as if it were set to true:

* Default for 3.0::

    $builder->add('gender', ChoiceType::class, array(
        'choices' => array('Male' => 'm', 'Female' => 'f'),
    ));

choice_loader
~~~~~~~~~~~~~

.. versionadded:: 2.7

    The ``choice_loader`` option was added in Symfony 2.7.

**type**: :class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\ChoiceLoaderInterface`

The ``choice_loader`` can be used to only partially load the choices in cases where
a fully-loaded list is not necessary. This is only needed in advanced cases and
would replace the ``choices`` option.

.. _reference-form-choice-label:

.. include:: /reference/forms/types/options/choice_label.rst.inc

.. include:: /reference/forms/types/options/choice_attr.rst.inc

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/choice_translation_domain.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/group_by.rst.inc

.. include:: /reference/forms/types/options/choice_value.rst.inc

.. include:: /reference/forms/types/options/choice_name.rst.inc


choice_list
~~~~~~~~~~~

.. caution::

    The ``choice_list`` option of ChoiceType was deprecated in Symfony 2.7.
    You should use `choices`_ or `choice_loader`_ now.

**type**: :class:`Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ChoiceListInterface`

This is one way of specifying the options to be used for this field.
The ``choice_list`` option must be an instance of the ``ChoiceListInterface``.
For more advanced cases, a custom class that implements the interface
can be created to supply the choices.

With this option you can also allow float values to be selected as data.
For example::

    use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

    // ...
    $builder->add('status', ChoiceType::class, array(
        'choice_list' => new ChoiceList(
            array(1, 0.5, 0.1),
            array('Full', 'Half', 'Almost empty')
        )
    ));

The ``status`` field created by the code above will be rendered as:

.. code-block:: html

    <select name="status">
        <option value="0">Full</option>
        <option value="1">Half</option>
        <option value="2">Almost empty</option>
    </select>

But don't be confused! If ``Full`` is selected (value ``0`` in HTML), ``1``
will be returned in your form. If ``Almost empty`` is selected (value ``2``
in HTML), ``0.1`` will be returned.

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

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

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
