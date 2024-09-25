WeekType Field
==============

This field type allows the user to modify data that represents a specific
`ISO 8601`_ week number (e.g. ``1984-W05``).

Can be rendered as a text input or select tags. The underlying format of
the data can be a string or an array.

+---------------------------+--------------------------------------------------------------------+
| Underlying Data Type      | can be a string, or array (see the ``input`` option)               |
+---------------------------+--------------------------------------------------------------------+
| Rendered as               | single text box, two text boxes or two select fields               |
+---------------------------+--------------------------------------------------------------------+
| Default invalid message   | Please enter a valid week.                                         |
+---------------------------+--------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                      |
+---------------------------+--------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\WeekType` |
+---------------------------+--------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

.. include:: /reference/forms/types/options/choice_translation_domain.rst.inc

placeholder
~~~~~~~~~~~

**type**: ``string`` | ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. When the placeholder value is a string,
it will be used as the **blank value** of all select boxes::

    use Symfony\Component\Form\Extension\Core\Type\WeekType;

    $builder->add('startWeek', WeekType::class, [
        'placeholder' => 'Select a value',
    ]);

Alternatively, you can use an array that configures different placeholder
values for the year and week fields::

    use Symfony\Component\Form\Extension\Core\Type\WeekType;

    $builder->add('startDateTime', WeekType::class, [
        'placeholder' => [
            'year' => 'Year',
            'week' => 'Week',
        ],
    ]);

.. include:: /reference/forms/types/options/html5.rst.inc

input
~~~~~

**type**: ``string`` **default**: ``array``

The format of the *input* data - i.e. the format that the date is stored
on your underlying object. Valid values are:

* ``string`` (e.g. ``"2011-W17"``)
* ``array`` (e.g. ``[2011, 17]``)

The value that comes back from the form will also be normalized back into
this format.

widget
~~~~~~

**type**: ``string`` **default**: ``choice``

The basic way in which this field should be rendered. Can be one of the
following:

* ``choice``: renders two select inputs;
* ``text``: renders a two field input of type ``text`` (year and week);
* ``single_text``: renders a single input of type ``week``.

years
~~~~~

**type**: ``array`` **default**: ten years before to ten years after the
current year

List of years available to the year field type. This option is only relevant
when the ``widget`` option is set to ``choice``.

.. include:: /reference/forms/types/options/weeks.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The actual default value of this option depends on other field options:

* If ``widget`` is ``single_text``, then ``''`` (empty string);
* Otherwise ``[]`` (empty array).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

error_bubbling
~~~~~~~~~~~~~~

**default**: ``false``

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Field Variables
---------------

+----------+------------+----------------------------------------------------------------------+
| Variable | Type       | Usage                                                                |
+==========+============+======================================================================+
| widget   | ``mixed``  | The value of the `widget`_ option.                                   |
+----------+------------+----------------------------------------------------------------------+
| type     | ``string`` | Only present when widget is ``single_text`` and HTML5 is activated,  |
|          |            | contains the input type to use (``datetime``, ``date`` or ``time``). |
+----------+------------+----------------------------------------------------------------------+

.. _`ISO 8601`: https://en.wikipedia.org/wiki/ISO_8601
