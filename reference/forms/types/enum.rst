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
        case Left = 'Left/Start aligned';
        case Center = 'Center/Middle aligned';
        case Right = 'Right/End aligned';
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

Since the label displayed in the ``<select>`` options is the enum name, you might sometimes
want more flexibility as PHP strongly restricts the usable characters for those.
You could do this by implementing a function in your enum class which returns a label 
or even a translation string for each possible enum::

    // src/Config/TextAlign.php
    namespace App\Config;

    enum TextAlign: string
    {
        case Left = 'Left/Start aligned';
        case Center = 'Center/Middle aligned';
        case Right = 'Right/End aligned';
        
        public function label(): string
        {
            return match ($this) {
                self::Left => 'text_align.left.label',
                self::Center => 'text_align.center.label',
                self::Right  => 'text_align.right.label',
            };
        }
    }
    
You can then use the ``choice_label`` option of ``EnumType`` with a function that 
returns the label::

    ->add('textAlign', EnumType::class, [
        'class' => TextAlign::class,
        'choice_label' => static function (TextAlign $choice): string {
            return $choice->label();
        },
    ])

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
