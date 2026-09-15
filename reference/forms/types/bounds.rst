BoundsType Field
================

.. versionadded:: 8.2

    The ``BoundsType`` was introduced in Symfony 8.2.

This is a special field "group" that renders a lower and an upper bound of
the same field type. It's useful to define a range of values, such as a
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
        'from_options' => ['label' => 'Minimum price'],
        'to_options' => ['label' => 'Maximum price'],
        'compare' => true,
    ]);

The bounds are two fields named ``from`` and ``to``. Their data is mapped to
the ``from`` and ``to`` keys of an array (e.g. ``['from' => 10, 'to' => 50]``)
or to the ``from`` and ``to`` properties of an object. If your model uses other
names, define the ``property_path`` option of each bound::

    $builder->add('price', BoundsType::class, [
        'type' => MoneyType::class,
        'from_options' => ['property_path' => 'minPrice'],
        'to_options' => ['property_path' => 'maxPrice'],
    ]);

When both bounds are left empty, the data of the field is ``null``. When only
one of them is empty, the field keeps the other bound (e.g. to define a range
without an upper limit).

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

When the `compare`_ option is enabled, an error is displayed if the lower bound
is greater than the upper bound. Both bounds can be equal.

The errors of the whole field, such as the ones caused by the validation
constraints applied to it, are displayed on the ``from`` field.

Field Options
-------------

``compare``
~~~~~~~~~~~

**type**: ``boolean`` or ``callable`` **default**: ``false``

Whether to check that the lower bound is not greater than the upper bound.
When set to ``true``, scalar values, ``DateTimeInterface`` objects and enums
(which are ordered as their cases are declared) are compared. The bounds are
compared as normalized by the inner field type, so for example a ``DateType``
is compared as a date, regardless of its ``input`` option.

For other values, pass a callable that compares both bounds and returns an
integer lower than, equal to, or greater than zero, like the ``<=>`` operator::

    use App\Form\Type\VersionType;
    use App\Model\Version;
    use Symfony\Component\Form\Extension\Core\Type\BoundsType;
    // ...

    $builder->add('supportedVersions', BoundsType::class, [
        'type' => VersionType::class,
        'compare' => static function (Version $from, Version $to): int {
            return version_compare((string) $from, (string) $to);
        },
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

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
