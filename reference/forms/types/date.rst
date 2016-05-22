.. index::
   single: Forms; Fields; date

date Field Type
===============

A field that allows the user to modify date information via a variety of
different HTML elements.

This field can be rendered in a variety of different ways via the `widget`_ option
and can understand a number of different input formats via the `input`_ option.

+----------------------+-----------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+----------------------+-----------------------------------------------------------------------------+
| Rendered as          | single text box or three select fields                                      |
+----------------------+-----------------------------------------------------------------------------+
| Options              | - `days`_                                                                   |
|                      | - `empty_value`_                                                            |
|                      | - `format`_                                                                 |
|                      | - `input`_                                                                  |
|                      | - `model_timezone`_                                                         |
|                      | - `months`_                                                                 |
|                      | - `view_timezone`_                                                          |
|                      | - `widget`_                                                                 |
|                      | - `years`_                                                                  |
+----------------------+-----------------------------------------------------------------------------+
| Overridden options   | - `by_reference`_                                                           |
|                      | - `compound`_                                                               |
|                      | - `data_class`_                                                             |
|                      | - `error_bubbling`_                                                         |
+----------------------+-----------------------------------------------------------------------------+
| Inherited            | - `data`_                                                                   |
| options              | - `disabled`_                                                               |
|                      | - `error_mapping`_                                                          |
|                      | - `inherit_data`_                                                           |
|                      | - `invalid_message`_                                                        |
|                      | - `invalid_message_parameters`_                                             |
|                      | - `mapped`_                                                                 |
|                      | - `read_only`_                                                              |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | :doc:`form </reference/forms/types/form>`                                   |
+----------------------+-----------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DateType`          |
+----------------------+-----------------------------------------------------------------------------+

Basic Usage
-----------

This field type is highly configurable, but easy to use. The most important
options are ``input`` and ``widget``.

Suppose that you have a ``publishedAt`` field whose underlying date is a
``DateTime`` object. The following configures the ``date`` type for that
field as **three different choice fields**::

    $builder->add('publishedAt', 'date', array(
        'widget' => 'choice',
    ));

If your underlying date is *not* a ``DateTime`` object (e.g. it's a unix timestamp),
configure the `input`_ option.

Rendering a single HTML5 Textbox
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For a better user experience, you may want to render a single text field and use
some kind of "date picker" to help your user fill in the right format. To do that,
use the ``single_text`` widget::

    $builder->add('publishedAt', 'date', array(
        // render as a single text box
        'widget' => 'single_text',
    ));

This will render as an ``input type="date"`` HTML5 field, which means that **some -
but not all - browsers will add nice date picker functionality to the field**. If you
want to be absolutely sure that *every* user has a consistent date picker, use an
external JavaScript library.

For example, suppose you want to use the `Bootstrap Datepicker`_ library. First,
make the following changes::

    $builder->add('publishedAt', 'date', array(
        'widget' => 'single_text',

        // do not render as type="date", to avoid HTML5 date pickers
        'html5' => false,

        // add a class that can eb selected in JavaScript
        'attr' => ['class' => 'js-datepicker'],
    ));

Assuming you're using jQuery, you can initialize the date picker via:

.. code-block:: html

    <script>
        $(document).ready(function() {
            $('.js-datepicker').datepicker({
                format: 'yyyy-mm-dd'
            });
        });
    </script>

This ``format`` key tells the date picker to use the date format that Symfony expects.
This can be tricky: if the date picker is misconfigured, Symfony won't understand
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

empty_value
~~~~~~~~~~~

**type**: ``string`` or ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. The ``empty_value`` option can be used
to add a "blank" entry to the top of each select box::

    $builder->add('dueDate', 'date', array(
        'empty_value' => '',
    ));

Alternatively, you can specify a string to be displayed for the "blank" value::

    $builder->add('dueDate', 'date', array(
        'empty_value' => array('year' => 'Year', 'month' => 'Month', 'day' => 'Day')
    ));

.. _reference-forms-type-date-format:

.. include:: /reference/forms/types/options/date_format.rst.inc

.. _form-reference-date-input:

.. include:: /reference/forms/types/options/date_input.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/view_timezone.rst.inc

.. include:: /reference/forms/types/options/date_widget.rst.inc

.. include:: /reference/forms/types/options/years.rst.inc

Overridden Options
------------------

by_reference
~~~~~~~~~~~~

**default**: ``false``

The ``DateTime`` classes are treated as immutable objects.

.. include:: /reference/forms/types/options/compound_type.rst.inc

.. include:: /reference/forms/types/options/data_class_date.rst.inc

error_bubbling
~~~~~~~~~~~~~~

**default**: ``false``

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>`
type:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

Field Variables
---------------

+--------------+------------+----------------------------------------------------------------------+
| Variable     | Type       | Usage                                                                |
+==============+============+======================================================================+
| widget       | ``mixed``  | The value of the `widget`_ option.                                   |
+--------------+------------+----------------------------------------------------------------------+
| type         | ``string`` | Only present when widget is ``single_text`` and HTML5 is activated,  |
|              |            | contains the input type to use (``datetime``, ``date`` or ``time``). |
+--------------+------------+----------------------------------------------------------------------+
| date_pattern | ``string`` | A string with the date format to use.                                |
+--------------+------------+----------------------------------------------------------------------+

.. _`Bootstrap Datepicker`: https://github.com/eternicode/bootstrap-datepicker
