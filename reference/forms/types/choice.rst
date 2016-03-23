.. index::
   single: Forms; Fields; choice

choice Field Type (select drop-downs, radio buttons & checkboxes)
=================================================================

A multi-purpose field used to allow the user to "choose" one or more options.
It can be rendered as a ``select`` tag, radio buttons, or checkboxes.

To use this field, you must specify *either* ``choices`` or ``choice_loader`` option.

+-------------+------------------------------------------------------------------------------+
| Rendered as | can be various tags (see below)                                              |
+-------------+------------------------------------------------------------------------------+
| Options     | - `choices`_                                                                 |
|             | - `choice_attr`_                                                             |
|             | - `choice_label`_                                                            |
|             | - `choice_list`_ (deprecated)                                                |
|             | - `choice_loader`_                                                           |
|             | - `choice_name`_                                                             |
|             | - `choice_translation_domain`_                                               |
|             | - `choice_value`_                                                            |
|             | - `choices_as_values`_                                                       |
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
|             | - `read_only`_                                                               |
|             | - `required`_                                                                |
|             | - `translation_domain`_                                                      |
+-------------+------------------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                                    |
+-------------+------------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType`         |
+-------------+------------------------------------------------------------------------------+

Example Usage
-------------

The easiest way to use this field is to specify the choices directly via
the ``choices`` option::

    $builder->add('isAttending', 'choice', array(
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

The model data of this field, the **choice** may be any of the ``choices`` option
values, while **keys** are used as default label that the user will see and select.

If the starting data for this field is ``true``, then ``Yes`` will be auto-selected.
In other words, each value of the ``choices`` option is the **choice** data you
want to deal with in PHP code, while the **key** is the default label that will be
shown to the user and the **value** is the string that will be submitted to the
form and used in the template for the corresponding html attribute.

.. caution::

    The ``choices_as_values`` *must* be set to ``true`` in all cases. This activates
    the "new" choice type API, which was introduced in Symfony 2.7. If you omit this
    option (or set it to ``false``), you'll activate the old API, which is deprecated
    and will be removed in 3.0. To read about the old API, read an older version of
    the docs.

.. note::

    Pre selected choices will depend on the **data** passed to the field and
    the values of the ``choices`` option. However submitted choices will depend
    on the **string** matching the **choice**. In the example above, the default
    values are incrementing integers because ``null`` cannot be casted to string.
    You should consider it as well when dealing with ``empty_data`` option::

        $builder->add('isAttending', 'choice', array(
            'choices'  => array(
                'Maybe' => null,
                'Yes' => true,
                'No' => false,
            ),
            'choices_as_values' => true,
            'data' => true, // pre selected choice
            'empty_data' => '1', // default submitted value
        ));

    When the ``multiple`` option is ``true`` the submitted data is an array of
    strings, you should the set the ``empty_value`` option accordingly.
    Also note that as a scalar ``false`` data as string **value** is by default
    ``"0"`` to avoid conflict with placeholder value which is always an empty
    string.

Advanced Example (with Objects!)
--------------------------------

This field has a *lot* of options and most control how the field is displayed. In
this example, the underlying data is some ``Category`` object that has a ``getName()``
method::

    $builder->add('category', 'choice', array(
        'choices' => array(
            new Category('Cat1'),
            new Category('Cat2'),
            new Category('Cat3'),
            new Category('Cat4'),
        ),
        'choices_as_values' => true,
        'choice_label' => function(Category $category, $key, $value) {
            return strtoupper($category->getName());
        },
        'choice_attr' => function(Category $category, $key, $value) {
            return array('class' => 'category_'.strtolower($category->getName()));
        },
        'group_by' => function(Category $category, $key, $value) {
            // randomly assign things into 2 groups
            return rand(0, 1) == 1 ? 'Group A' : 'Group B';
        },
        'preferred_choices' => function(Category $category, $key, $value) {
            return 'Cat2' === $category->getName() || 'Cat3' === $category->getName();
        },
    ));

You can also customize the `choice_name`_ and `choice_value`_ of each choice if
you need further HTML customization.

.. caution::

    When dealing with objects as choices, you should be careful about how
    string values are set to use them with the `empty_data` option.
    In the example above, the default values are incrementing integers if the
    ``Category`` class does not implement ``toString`` method.
    To get a full control of the string values use the `choice_value`_ option::

        $builder->add('category', 'choice', array(
            'choices'  => array(
            new Category('Cat1'),
            new Category('Cat2'),
            new Category('Cat3'),
            new Category('Cat4'),
            ),
            'choices_as_values' => true,
            'choice_value' => function(Category $category = null) {
                if (null === $category) {
                    return '';
                }

                return strtolower($category->getName());
            },
            'choice_label' => function(Category $category, $key, $value) {
                return strtoupper($category->getName());
            },
            'multiple' => true,
            'empty_data' => array('cat2'), // default submitted value
                                           // an array because of multiple option
        ));

    Note that `choice_value`_ option set as a callable can get passed ``null``
    when no data is preset or submitted.

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

    $builder->add('stockStatus', 'choice', array(
        'choices' => array(
            'Main Statuses' => array(
                'Yes' => 'stock_yes',
                'No' => 'stock_no',
            ),
            'Out of Stock Statuses' => array(
                'Backordered' => 'stock_backordered',
                'Discontinued' => 'stock_discontinued',
            ),
        ),
        'choices_as_values' => true,
    ));

.. image:: /images/reference/form/choice-example4.png
   :align: center

To get fancier, use the `group_by`_ option.

Field Options
-------------

choices
~~~~~~~

**type**: ``array`` or ``\Traversable`` **default**: ``array()``

This is the most basic way to specify the choices that should be used
by this field. The ``choices`` option is an array, where the array key
is the choice's label and the array value is the choice's data::

    $builder->add('inStock', 'choice', array(
        'choices' => array(
            'In Stock' => true,
            'Out of Stock' => false,
        ),
        // always include this
        'choices_as_values' => true,
    ));

The component will try to cast the choices data to string to use it in view
format, in that case ``"0"`` and ``"1"``, but you can customize it using the
`choice_value`_ option.

.. include:: /reference/forms/types/options/choice_attr.rst.inc

.. _reference-form-choice-label:

.. include:: /reference/forms/types/options/choice_label.rst.inc

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

    // ...
    $builder->add('status', 'choice', array(
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

choice_loader
~~~~~~~~~~~~~

.. versionadded:: 2.7

    The ``choice_loader`` option was added in Symfony 2.7.

**type**: :class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\ChoiceLoaderInterface`

The ``choice_loader`` can be used to load the choices form a data source with a
custom logic (e.g query language) such as database or search engine.
The list will be fully loaded to display the form, but while submission only the
submitted choices will be loaded.

Also, the :class:``Symfony\\Component\\Form\\ChoiceList\\Factory\\ChoiceListFactoryInterface`` will cache the choice list
so the same :class:``Symfony\\Component\\Form\\ChoiceList\\Loader\\ChoiceLoaderInterface`` can be used in different fields with more performance
(reducing N queries to 1).

.. include:: /reference/forms/types/options/choice_name.rst.inc

.. include:: /reference/forms/types/options/choice_translation_domain.rst.inc

.. include:: /reference/forms/types/options/choice_value.rst.inc

choices_as_values
~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: false

.. versionadded:: 2.7

    The ``choices_as_values`` option was introduced in Symfony 2.7.

The ``choices_as_values`` option was added to keep backward compatibility with the
*old* way of handling the ``choices`` option. When set to ``false`` (or omitted),
the choice keys are used as the view value and the choice values are shown
to the user as label.

* Before 2.7 (and deprecated now)::

    $builder->add('agree', 'choice', array(
        // Shows "Yes" to the user, returns "1" when selected
        'choices'  => array('1' => 'Yes', '0' => 'No'),
        // before 2.7, this option didn't actually exist, but the
        // behavior was equivalent to setting this to false in 2.7.
        'choices_as_values' => false,
    ));

* Since 2.7::

    $builder->add('agree', 'choice', array(
        // Shows "Yes" to the user, returns "1" when selected
        'choices' => array('Yes' => '1', 'No' => '0'),
        'choices_as_values' => true,
    ));

As of Symfony 3.0, the ``choices_as_values`` option is ``true`` by default:

* Default for 3.0::

    $builder->add('agree', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
        'choices' => array('Yes' => '1', 'No' => '0'),
    ));

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

These options inherit from the :doc:`form </reference/forms/types/form>`
type:

.. include:: /reference/forms/types/options/by_reference.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

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
