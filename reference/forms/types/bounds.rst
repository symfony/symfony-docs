BoundsType Field
================

.. versionadded:: 8.2

    The ``BoundsType`` was introduced in Symfony 8.2.

This is a special field "group" that renders two fields of the same type: a
lower bound and an upper bound. Use it to define a range of values, such as a
price range or a date range.

+---------------------------+----------------------------------------------------------------------+
| Rendered as               | two input ``text`` fields by default, but see `type`_ option         |
+---------------------------+----------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                        |
+---------------------------+----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BoundsType` |
+---------------------------+----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Example Usage
-------------

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    use Symfony\Component\Form\Extension\Core\Type\MoneyType;
    // ...

    $builder->add('price', BoundsType::class, [
        'type' => MoneyType::class,
        'options' => ['currency' => 'EUR'],
        'compare' => true,
    ]);

The two bounds are fields named ``from`` and ``to``. By default, their data is
mapped to the ``from`` and ``to`` keys of an array (e.g.
``['from' => 10, 'to' => 50]``). To map them to the properties of an object,
define the ``data_class`` option. If your model uses other names, define the
``property_path`` option of each bound::

    use App\Model\PriceRange;
    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    use Symfony\Component\Form\Extension\Core\Type\MoneyType;
    // ...

    // maps the bounds to the minPrice and maxPrice properties of a PriceRange object
    $builder->add('price', BoundsType::class, [
        'type' => MoneyType::class,
        'data_class' => PriceRange::class,
        'from_options' => ['property_path' => 'minPrice'],
        'to_options' => ['property_path' => 'maxPrice'],
    ]);

    // maps the bounds to the "min" and "max" keys of an array
    $builder->add('price', BoundsType::class, [
        'type' => MoneyType::class,
        'from_options' => ['property_path' => '[min]'],
        'to_options' => ['property_path' => '[max]'],
    ]);

When both bounds are left empty, the data of the field is ``null``. When only
one of them is empty, the other bound is kept, so you can define open ranges
(e.g. a minimum price without a maximum price).

The ``required``, ``translation_domain`` and ``error_bubbling`` options of the
field are passed to both bounds. You can override them for the bounds with the
`options`_, `from_options`_ and `to_options`_ options.

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

The row of the field only renders the rows of the two bounds, so the label,
help and errors of the field itself are not displayed. Use the `from_options`_
and `to_options`_ options to define the label and help of each bound.

Validation
~~~~~~~~~~

When the `compare`_ option is enabled, an error is displayed if the lower bound
is greater than the upper bound (both bounds can be equal).

The errors of the field itself, such as the ones caused by the validation
constraints applied to it, are displayed on the ``from`` field (see the
`error_mapping`_ option).

Field Options
-------------

``compare``
~~~~~~~~~~~

**type**: ``boolean`` or ``callable`` **default**: ``false``

Whether to check that the lower bound is not greater than the upper bound.
When set to ``true``, it compares scalar values, ``DateTimeInterface`` objects
and cases of the same enum (which are ordered as they are declared). The bounds
are compared using the normalized data of the inner field type, so for example
a ``DateType`` is compared as a date, regardless of its ``input`` option.

For other values, pass a callable that receives the normalized data of both
bounds and returns an integer lower than, equal to, or greater than zero, like
the ``<=>`` operator does::

    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    // ...

    $builder->add('supportedVersions', BoundsType::class, [
        'compare' => static fn (string $from, string $to): int
            => version_compare($from, $to),
    ]);

The bounds are not compared when any of them is empty.

``compare_message``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The lower bound must not be greater than the upper bound.``

The error message displayed on the ``from`` field when the `compare`_ option
is enabled and the lower bound is greater than the upper bound. It's translated
using the ``validators`` translation domain.

``from_options``
~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Additional options (merged into `options`_ below) that are passed *only* to
the ``from`` field. This is especially useful for customizing the label::

    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    // ...

    $builder->add('price', BoundsType::class, [
        'from_options' => ['label' => 'Minimum price'],
        'to_options' => ['label' => 'Maximum price'],
    ]);

``options``
~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

This options array is passed to both underlying fields. In other words, these
are the options that customize the individual field types. For example, if the
``type`` option is set to ``MoneyType::class``, this array might contain the
``currency`` option.

``to_options``
~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Additional options (merged into `options`_ above) that are passed *only* to
the ``to`` field (see `from_options`_).

``type``
~~~~~~~~

**type**: ``string`` **default**: ``Symfony\Component\Form\Extension\Core\Type\TextType``

The two underlying fields are of this field type. For example, passing
``DateType::class`` renders two date fields.

Overridden Options
------------------

``error_bubbling``
~~~~~~~~~~~~~~~~~~

**default**: ``false``

This option is passed to both bounds, so their errors are displayed next to
each bound instead of on the field itself, whose errors are not rendered.

``error_mapping``
~~~~~~~~~~~~~~~~~

**default**: ``['.' => 'from']``

When the Validator component is installed, the errors of the field itself are
mapped to the ``from`` field.

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/data_class.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/translation_domain.rst.inc
