PolymorphicCollectionType Field
===============================

This field type renders a collection whose entries do not all share the same
type. Use :doc:`CollectionType </reference/forms/types/collection>` instead when
every entry has the same type.

+---------------------------+--------------------------------------------------------------------------------------+
| Rendered as               | depends on the `entry_types`_ option                                                 |
+---------------------------+--------------------------------------------------------------------------------------+
| Default invalid message   | The collection is invalid.                                                           |
+---------------------------+--------------------------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                                        |
+---------------------------+--------------------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\PolymorphicCollectionType`  |
+---------------------------+--------------------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

.. versionadded:: 8.2

    The ``PolymorphicCollectionType`` was introduced in Symfony 8.2.

Basic Usage
-----------

Consider a page built from a list of blocks, where a block is either a heading,
a paragraph of text or a footer. Each kind of block has its own form type, so
the collection cannot use a single ``entry_type``. Declare one type per kind in
the `entry_types`_ option and tell the collection which one to use for a given
entry with the `entry_type_provider`_ option::

    use App\Form\Block\BlockEntryTypeProvider;
    use App\Form\Block\FooterType;
    use App\Form\Block\HeadingType;
    use App\Form\Block\ParagraphType;
    use Symfony\Component\Form\Extension\Core\Type\PolymorphicCollectionType;
    // ...

    $builder->add('blocks', PolymorphicCollectionType::class, [
        'entry_types' => [
            'heading' => HeadingType::class,
            'paragraph' => ParagraphType::class,
            'footer' => FooterType::class,
        ],
        'entry_type_provider' => new BlockEntryTypeProvider(),
    ]);

The provider is a class implementing
:class:`Symfony\\Component\\Form\\EntryTypeProviderInterface`. It receives the
data of a single entry and returns the key of the `entry_types`_ option to build
it with. The same entry reaches the provider in two different shapes, hence the
two methods: ``forModelData()`` gets the object or value stored in your model,
while ``forSubmittedData()`` gets the raw data of an entry added by the client,
which only happens when `allow_add`_ is enabled::

    // src/Form/Block/BlockEntryTypeProvider.php
    namespace App\Form\Block;

    use App\Entity\Footer;
    use App\Entity\Heading;
    use Symfony\Component\Form\EntryTypeProviderInterface;

    class BlockEntryTypeProvider implements EntryTypeProviderInterface
    {
        public function forModelData(mixed $data): int|string
        {
            return match (true) {
                $data instanceof Heading => 'heading',
                $data instanceof Footer => 'footer',
                default => 'paragraph',
            };
        }

        public function forSubmittedData(mixed $data): int|string
        {
            return $data['type'] ?? 'paragraph';
        }
    }

Returning a key that is not declared in `entry_types`_ throws an exception.
Nothing tells the submitted data apart on its own, so the entry types must
submit whatever ``forSubmittedData()`` reads, here a ``type`` field.

The options that configure the entries (`entry_options`_,
`prototype_options`_ and `prototype_data`_) keep the names they have on
:doc:`CollectionType </reference/forms/types/collection>`, but they are keyed by
entry name, so each kind of entry gets its own configuration::

    $builder->add('blocks', PolymorphicCollectionType::class, [
        'entry_types' => [
            'heading' => HeadingType::class,
            'paragraph' => ParagraphType::class,
        ],
        'entry_type_provider' => new BlockEntryTypeProvider(),
        'entry_options' => [
            'heading' => ['label' => 'Heading'],
            'paragraph' => ['label' => 'Paragraph'],
        ],
    ]);

Rendering
---------

Entries are rendered like the entries of a
:doc:`CollectionType </reference/forms/types/collection>`: they get the
``collection_entry`` block prefix, so the form themes you already use for
collections apply to them too.

When `allow_add`_ is enabled, one prototype is built per entry type instead of
a single one. They are available in the ``prototypes`` view variable, indexed by
entry name:

.. code-block:: twig

    {{ form_row(form.blocks.vars.prototypes['heading']) }}

If you render the whole collection field at once, each prototype is exposed as a
``data-prototype-<name>`` attribute of the element that surrounds the collection
(e.g. ``data-prototype-heading``). Your JavaScript reads the attribute matching
the kind of block the user asked for, replaces the ``__name__`` placeholder as
usual and inserts the result in the form.

Field Options
-------------

entry_types
~~~~~~~~~~~

**type**: ``array`` **required**

The form types the entries of the collection can use, as an array of form type
class names indexed by an entry name of your choosing. These names are the
values `entry_type_provider`_ returns and the keys of the `entry_options`_,
`prototype_options`_ and `prototype_data`_ options.

entry_type_provider
~~~~~~~~~~~~~~~~~~~

**type**: :class:`Symfony\\Component\\Form\\EntryTypeProviderInterface` **required**

The object deciding which of the `entry_types`_ an entry is built with. See
`Basic Usage`_ above.

entry_options
~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The options passed to the entries, as one array of options per entry name::

    $builder->add('blocks', PolymorphicCollectionType::class, [
        'entry_types' => [
            'heading' => HeadingType::class,
            'paragraph' => ParagraphType::class,
        ],
        'entry_type_provider' => new BlockEntryTypeProvider(),
        'entry_options' => [
            'heading' => ['attr' => ['maxlength' => 100]],
            'paragraph' => ['attr' => ['rows' => 10]],
        ],
    ]);

Entry names you leave out get an empty array of options.

prototype_options
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The options passed to the prototypes, as one array of options per entry name.
They are merged into `entry_options`_, so you only need to list the options that
differ between adding a new entry and editing an existing one::

    $builder->add('blocks', PolymorphicCollectionType::class, [
        // ...
        'entry_options' => [
            'heading' => ['help' => 'You can edit this heading here.'],
        ],
        'prototype_options' => [
            'heading' => ['help' => 'You can enter a new heading here.'],
        ],
    ]);

prototype_data
~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The data each new entry starts with, as one value per entry name::

    $builder->add('blocks', PolymorphicCollectionType::class, [
        // ...
        'allow_add' => true,
        'prototype_data' => [
            'heading' => 'New heading',
        ],
    ]);

Entry names you leave out keep the data configured in `entry_options`_.

allow_add
~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Behaves as the ``allow_add`` option of
:doc:`CollectionType </reference/forms/types/collection>`, except that one
prototype is built per entry type. See `Rendering`_ above.

allow_delete
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Behaves as the ``allow_delete`` option of
:doc:`CollectionType </reference/forms/types/collection>`.

delete_empty
~~~~~~~~~~~~

**type**: ``boolean`` | ``callable`` **default**: ``false``

Behaves as the ``delete_empty`` option of
:doc:`CollectionType </reference/forms/types/collection>`.

keep_as_list
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Behaves as the ``keep_as_list`` option of
:doc:`CollectionType </reference/forms/types/collection>`.

prototype
~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

Behaves as the ``prototype`` option of
:doc:`CollectionType </reference/forms/types/collection>`, except that the
prototypes are exposed in the ``prototypes`` view variable. See `Rendering`_
above.

prototype_name
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``__name__``

Behaves as the ``prototype_name`` option of
:doc:`CollectionType </reference/forms/types/collection>`. The same placeholder
is used by every prototype.

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`.
Not all options are listed here - only the most applicable to this type:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/by_reference.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The default value is ``[]`` (empty array).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

error_bubbling
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

.. include:: /reference/forms/types/options/_error_bubbling_body.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Field Variables
---------------

============  ===========  ==========================================================
Variable      Type         Usage
============  ===========  ==========================================================
allow_add     ``boolean``  The value of the `allow_add`_ option.
allow_delete  ``boolean``  The value of the `allow_delete`_ option.
prototypes    ``array``    One prototype view per entry name, when `allow_add`_
                           and `prototype`_ are both enabled.
============  ===========  ==========================================================
