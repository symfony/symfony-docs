.. index::
   single: Forms; Fields; DateType

DateType Field
==============

A field that allows the user to modify date information via a variety of
different HTML elements.

This field can be rendered in a variety of different ways via the `widget`_ option
and can understand a number of different input formats via the `input`_ option.

+---------------------------+-----------------------------------------------------------------------------+
| Underlying Data Type      | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+---------------------------+-----------------------------------------------------------------------------+
| Rendered as               | single text box or three select fields                                      |
+---------------------------+-----------------------------------------------------------------------------+
| Default invalid message   | Please enter a valid date.                                                  |
+---------------------------+-----------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                         |
+---------------------------+-----------------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                               |
+---------------------------+-----------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DateType`          |
+---------------------------+-----------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Basic Usage
-----------

This field type is highly configurable. The most important options
are ``input`` and ``widget``.

Suppose that you have a ``publishedAt`` field whose underlying date is a
``DateTime`` object. The following configures the ``date`` type for that
field as **three different choice fields**::

    use Symfony\Component\Form\Extension\Core\Type\DateType;
    // ...

    $builder->add('publishedAt', DateType::class, [
        'widget' => 'choice',
    ]);

If your underlying date is *not* a ``DateTime`` object (e.g. it is a Unix
timestamp or a ``DateTimeImmutable`` object), configure the `input`_ option::

    $builder->add('publishedAt', DateType::class, [
        'widget' => 'choice',
        'input'  => 'datetime_immutable'
    ]);

Rendering a single HTML5 Text Box
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For a better user experience, you may want to render a single text field and use
some kind of "date picker" to help your user fill in the right format. To do that,
use the ``single_text`` widget::

    use Symfony\Component\Form\Extension\Core\Type\DateType;
    // ...

    $builder->add('publishedAt', DateType::class, [
        // renders it as a single text box
        'widget' => 'single_text',
    ]);

This will render as an ``input type="date"`` HTML5 field, which means that **some -
but not all - browsers will add nice date picker functionality to the field**. If you
want to be absolutely sure that *every* user has a consistent date picker, use an
external JavaScript library.

For example, suppose you want to use the `Bootstrap Datepicker`_ library. First,
make the following changes::

    use Symfony\Component\Form\Extension\Core\Type\DateType;
    // ...

    $builder->add('publishedAt', DateType::class, [
        'widget' => 'single_text',

        // prevents rendering it as type="date", to avoid HTML5 date pickers
        'html5' => false,

        // adds a class that can be selected in JavaScript
        'attr' => ['class' => 'js-datepicker'],
    ]);

Then, add the following JavaScript code in your template to initialize the date
picker:

.. code-block:: html

    <script>
        $(document).ready(function() {
            // you may need to change this code if you are not using Bootstrap Datepicker
            $('.js-datepicker').datepicker({
                format: 'yyyy-mm-dd'
            });
        });
    </script>

This ``format`` key tells the date picker to use the date format that Symfony expects.
This can be tricky: if the date picker is misconfigured, Symfony will not understand
the format and will throw a validation error. You can also configure the format
that Symfony should expect via the `format`_ option.

.. caution::

    The string used by a JavaScript date picker to describe its format (e.g. ``yyyy-mm-dd``)
    may not match the string that Symfony uses (e.g. ``yyyy-MM-dd``). This is because
    different libraries use different formatting rules to describe the date format.
    Be aware of this - it can be tricky to make the formats truly match!

Field Options
-------------

.. include:: /reference/forms/types/options/days.rst.inc

``placeholder``
~~~~~~~~~~~~~~~

**type**: ``string`` | ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. When the placeholder value is a string,
it will be used as the **blank value** of all select boxes::

    $builder->add('dueDate', DateType::class, [
        'placeholder' => 'Select a value',
    ]);

Alternatively, you can use an array that configures different placeholder
values for the year, month and day fields::

    $builder->add('dueDate', DateType::class, [
        'placeholder' => [
            'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
        ],
    ]);

.. _reference-forms-type-date-format:

.. include:: /reference/forms/types/options/date_format.rst.inc

.. include:: /reference/forms/types/options/html5.rst.inc

.. _form-reference-date-input:

.. include:: /reference/forms/types/options/date_input.rst.inc

.. include:: /reference/forms/types/options/date_input_format.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/view_timezone.rst.inc

.. include:: /reference/forms/types/options/date_widget.rst.inc

.. include:: /reference/forms/types/options/years.rst.inc

Overridden Options
------------------

``by_reference``
~~~~~~~~~~~~~~~~

**default**: ``false``

The ``DateTime`` classes are treated as immutable objects.

.. include:: /reference/forms/types/options/choice_translation_domain_disabled.rst.inc

.. include:: /reference/forms/types/options/compound_type.rst.inc

.. include:: /reference/forms/types/options/data_class_date.rst.inc

``error_bubbling``
~~~~~~~~~~~~~~~~~~

**default**: ``false``

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Field Variables
---------------

+------------------+------------+----------------------------------------------------------------------+
| Variable         | Type       | Usage                                                                |
+==================+============+======================================================================+
| ``widget``       | ``mixed``  | The value of the `widget`_ option.                                   |
+------------------+------------+----------------------------------------------------------------------+
| ``type``         | ``string`` | Only present when widget is ``single_text`` and HTML5 is activated,  |
|                  |            | contains the input type to use (``datetime``, ``date`` or ``time``). |
+------------------+------------+----------------------------------------------------------------------+
| ``date_pattern`` | ``string`` | A string with the date format to use.                                |
+------------------+------------+----------------------------------------------------------------------+

.. _`Bootstrap Datepicker`: https://github.com/uxsolutions/bootstrap-datepicker
