.. index::
   single: Forms; Fields; EnumType

EnumType Field
==============

.. versionadded:: 5.4

   The ``EnumType`` form field was introduced in Symfony 5.4.

A multi-purpose field used to allow the user to "choose" one or more options
defined in a `PHP enumeration`_. It extends the :doc:`ChoiceType </reference/forms/types/choice>`
field and defines the same options.

+---------------------------+----------------------------------------------------------------------+
| Rendered as               | can be various tags (see below)                                      |
+---------------------------+----------------------------------------------------------------------+
| Default invalid message   | The selected choice is invalid.                                      |
+---------------------------+----------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                  |
+---------------------------+----------------------------------------------------------------------+
| Parent type               | :doc:`ChoiceType </reference/forms/types/choice>`                    |
+---------------------------+----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\EnumType`   |
+---------------------------+----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Example Usage
-------------

Before using this field, you'll need to have some PHP enumeration (or "enum" for
short) defined somewhere in your application. This enum has to be of type
"backed enum", where each keyword defines a scalar value such as a string::

    // src/Config/TextAlign.php
    namespace App\Config;

    enum TextAlign: string
    {
        case Left = 'Left aligned';
        case Center = 'Center aligned';
        case Right = 'Right aligned';
    }

Instead of using the values of the enumeration in a ``choices`` option, the
``EnumType`` only requires to define the ``class`` option pointing to the enum::

    use App\Config\TextAlign;
    use Symfony\Component\Form\Extension\Core\Type\EnumType;
    // ...

    $builder->add('alignment', EnumType::class, ['class' => TextAlign::class]);

This will display a ``<select>`` tag with the three possible values defined in
the ``TextAlign`` enum. Use the `expanded`_ and `multiple`_ options to display
these values as ``<input type="checkbox">`` or ``<input type="radio">``.

The label displayed in the ``<option>`` elements of the ``<select>`` is the enum
name. PHP defines some strict rules for these names (e.g. they can't contain
dots or spaces). If you need more flexibility for these labels, use the
``choice_label`` option and define a function that returns the custom label::

    ->add('textAlign', EnumType::class, [
        'class' => TextAlign::class,
        'choice_label' => fn ($choice) => match ($choice) {
            TextAlign::Left => 'text_align.left.label',
            TextAlign::Center => 'text_align.center.label',
            TextAlign::Right  => 'text_align.right.label',
        },
    ]);

Field Options
-------------

class
~~~~~

**type**: ``string`` **default**: (it has no default)

The fully-qualified class name (FQCN) of the PHP enum used to get the values
displayed by this form field.

Inherited Options
-----------------

These options inherit from the :doc:`ChoiceType </reference/forms/types/choice>`:

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

``group_by``
~~~~~~~~~~~~

**type**: ``string`` or ``callable`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``null``

You can group the ``<option>`` elements of a ``<select>`` into ``<optgroup>``
by passing a multi-dimensional array to ``choices``. See the
:ref:`Grouping Options <form-choices-simple-grouping>` section about that.

The ``group_by`` option is an alternative way to group choices, which gives you
a bit more flexibility.

Let's add a few cases to our ``TextAlign`` enumeration::

    // src/Config/TextAlign.php
    namespace App\Config;

    enum TextAlign: string
    {
        case UpperLeft = 'Upper Left aligned';
        case LowerLeft = 'Lower Left aligned';

        case Center = 'Center aligned';

        case UpperRight = 'Upper Right aligned';
        case LowerRight = 'Lower Right aligned';
    }

We can now group choices by the enum case value::

    use App\Config\TextAlign;
    use Symfony\Component\Form\Extension\Core\Type\EnumType;
    // ...

    $builder->add('alignment', EnumType::class, [
        'class' => TextAlign::class,
        'group_by' => function(TextAlign $choice, int $key, string $value): ?string {
            if (str_starts_with($value, 'Upper')) {
                return 'Upper';
            }

            if (str_starts_with($value, 'Lower')) {
                return 'Lower';
            }

            return 'Other';
        }
    ]);

This callback will group choices in 3 categories: ``Upper``, ``Lower`` and ``Other``.

If you return ``null``, the option won't be grouped.

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/choice_type_trim.rst.inc

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. _`PHP enumeration`: https://www.php.net/manual/language.enumerations.php
