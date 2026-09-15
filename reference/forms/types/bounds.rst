BoundsType Field
================

.. versionadded:: 8.2

    The ``BoundsType`` field was introduced in Symfony 8.2.

This is a field "group" that renders a lower and an upper bound of the same
inner type, such as an age range, a price range or a date range. The two
bounds are children of the field named ``from`` and ``to``.

+---------------------------+------------------------------------------------------------------------+
| Rendered as               | two ``input`` ``text`` fields by default, but see `type`_ option       |
+---------------------------+------------------------------------------------------------------------+
| Default compare message   | The lower bound must not be greater than the upper bound.              |
+---------------------------+------------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                          |
+---------------------------+------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BoundsType`   |
+---------------------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Example Usage
-------------

The ``type`` option defines the field type of both bounds, and the ``options``
option configures them::

    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    use Symfony\Component\Form\Extension\Core\Type\MoneyType;
    // ...

    $builder->add('price', BoundsType::class, [
        'type' => MoneyType::class,
        'options' => ['currency' => 'EUR'],
        'from_options' => ['label' => 'Minimum price'],
        'to_options' => ['label' => 'Maximum price'],
        'compare' => true,
    ]);

Any field type can be used as the inner type. For example, use
:doc:`DateType </reference/forms/types/date>` for a date range,
:doc:`ChoiceType </reference/forms/types/choice>` to render two select
boxes or :doc:`RangeType </reference/forms/types/range>` to render two sliders.

Data of the Field
~~~~~~~~~~~~~~~~~

Upon submission, the data of the field is an array with the ``from`` and
``to`` keys (e.g. ``['from' => 18, 'to' => 65]``). Keys of that array which are
not bounds are kept as they are.

When both bounds are left empty, the data of the field is ``null``. When only
one of them is empty, the range is kept as submitted (e.g.
``['from' => 18, 'to' => null]``).

The data can also be an object. If the bounds are stored under other names,
use the ``property_path`` option of each bound::

    use App\Model\PriceRange;
    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    // ...

    $builder->add('price', BoundsType::class, [
        'data_class' => PriceRange::class,
        'from_options' => ['property_path' => 'minPrice'],
        'to_options' => ['property_path' => 'maxPrice'],
    ]);

When the data is an array, use the array syntax of the property path instead
(e.g. ``'property_path' => '[minPrice]'``).

Rendering
~~~~~~~~~

The bounds field type is actually two underlying fields, which you can render
all at once, or individually. To render all at once, use something like:

.. code-block:: twig

    {{ form_row(form.price) }}

To render each field individually, use something like this:

.. code-block:: twig

    {{ form_row(form.price.from) }}
    {{ form_row(form.price.to) }}

Validation
~~~~~~~~~~

By default, the two bounds are not checked against each other. Set the
`compare`_ option to ``true`` to report an error when the lower bound is
greater than the upper bound. The error is displayed on the lower bound, the
one that has to change to make the range valid.

Validation errors that apply to the field as a whole (e.g. a constraint
defined on the ``price`` property) are displayed on the lower bound too.

Field Options
-------------

``compare``
~~~~~~~~~~~

**type**: ``boolean`` or ``callable`` **default**: ``false``

If ``true``, an error is added to the lower bound when it's greater than the
upper bound. Equal bounds are valid, and nothing is checked when one of the
bounds is empty.

Bounds are compared in their normalized form. For example, a ``DateType``
bound is compared as a date whatever the value of its ``input`` option.
Scalar values and ``DateTimeInterface`` objects are compared natively. Enums
are compared by the order in which their cases are declared, which is also the
order in which :doc:`EnumType </reference/forms/types/enum>` lists them.

Other values (e.g. the arrays returned by a ``ChoiceType`` with the
``multiple`` option) cannot be compared this way and throw a
:class:`Symfony\\Component\\Form\\Exception\\LogicException`. In that case,
pass a callable which receives both bounds and returns an integer lower than,
equal to, or greater than zero, as the ``<=>`` operator does::

    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    // ...

    $builder->add('supportedVersions', BoundsType::class, [
        'compare' => static fn (string $from, string $to): int
            => version_compare($from, $to),
    ]);

``compare_message``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The lower bound must not be greater than the upper bound.``

This is the error message displayed when the bounds are out of order (see the
`compare`_ option). The message is translated using the ``validators``
translation domain.

``from_options``
~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Additional options (will be merged into `options`_ below) that should be
passed *only* to the lower bound. This is especially useful for customizing
the label::

    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    // ...

    $builder->add('age', BoundsType::class, [
        'from_options' => ['label' => 'From'],
        'to_options' => ['label' => 'To'],
    ]);

``options``
~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

This options array will be passed to each of the two underlying fields. In
other words, these are the options that customize the individual field types.
For example, if the ``type`` option is set to ``MoneyType::class``, this array
might contain the ``currency`` option.

The ``required``, ``translation_domain`` and ``error_bubbling`` options of the
bounds field are passed to both bounds too, unless they are defined in this
array.

``to_options``
~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Additional options (will be merged into `options`_ above) that should be
passed *only* to the upper bound (see `from_options`_).

``type``
~~~~~~~~

**type**: ``string`` **default**: ``Symfony\Component\Form\Extension\Core\Type\TextType``

The two underlying fields will be of this field type. For example, passing
``DateType::class`` will render two date fields.

Overridden Options
------------------

``error_bubbling``
~~~~~~~~~~~~~~~~~~

**default**: ``false``

``error_mapping``
~~~~~~~~~~~~~~~~~

**default**: ``['.' => 'from']``

Errors of the field as a whole are mapped to the lower bound. This default
value is defined when the form integration of the Validator component is
enabled, which is the case in Symfony applications.

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

When it's an array, its ``from`` and ``to`` keys define the empty data of each
bound (e.g. ``['from' => '18']``). A bound which is not defined there keeps the
empty data of its own field type.

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
