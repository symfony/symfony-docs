PolymorphicCollectionType Field
===============================

This field type renders a collection whose entries don't all share the same
form type. Use :doc:`CollectionType </reference/forms/types/collection>` instead
when every entry has the same type.

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

Consider a CMS page built from a list of blocks, where each block is a heading,
a paragraph or a footer. Each kind of block has its own form type, so the
collection can't use a single ``entry_type``. Declare one form type per kind of
entry in the `entry_types`_ option and use the `entry_type_provider`_ option to
decide which one each entry uses::

    use App\Form\Block\BlockEntryTypeProvider;
    use App\Form\Block\FooterType;
    use App\Form\Block\HeadingType;
    use App\Form\Block\ParagraphType;
    use Symfony\Component\Form\Extension\Core\Type\PolymorphicCollectionType;
    // ...

    $builder->add('blocks', PolymorphicCollectionType::class, [
        // the keys are entry names of your choice
        'entry_types' => [
            'heading' => HeadingType::class,
            'paragraph' => ParagraphType::class,
            'footer' => FooterType::class,
        ],
        'entry_type_provider' => new BlockEntryTypeProvider(),
    ]);

The provider is a class that implements
:class:`Symfony\\Component\\Form\\EntryTypeProviderInterface`. It receives the
data of a single entry and returns the entry name to use, which must be one of
the keys of the `entry_types`_ option. The provider receives entries in two
different shapes, so it defines two methods::

    // src/Form/Block/BlockEntryTypeProvider.php
    namespace App\Form\Block;

    use App\Entity\Footer;
    use App\Entity\Heading;
    use Symfony\Component\Form\EntryTypeProviderInterface;

    class BlockEntryTypeProvider implements EntryTypeProviderInterface
    {
        // $data is an entry of the collection stored in your model
        // (e.g. a Heading object) and it's used to build the existing entries
        public function forModelData(mixed $data): int|string
        {
            return match (true) {
                $data instanceof Heading => 'heading',
                $data instanceof Footer => 'footer',
                default => 'paragraph',
            };
        }

        // $data is the raw submitted data of an entry added in the browser;
        // this method is only called when the 'allow_add' option is enabled
        public function forSubmittedData(mixed $data): int|string
        {
            return $data['type'] ?? 'paragraph';
        }
    }

The submitted data of an entry only contains the values of its fields, so it's
hard to guess its entry name from them. Instead, add a hidden unmapped field to
each entry form type to send the entry name along with the other fields::

    // src/Form/Block/HeadingType.php
    use Symfony\Component\Form\Extension\Core\Type\HiddenType;
    use Symfony\Component\Form\FormBuilderInterface;
    // ...

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', HiddenType::class, [
                'data' => 'heading',
                'mapped' => false,
            ])
            // ...
        ;
    }

The `entry_options`_, `prototype_options`_ and `prototype_data`_ options work
as in :doc:`CollectionType </reference/forms/types/collection>`, but their
values are indexed by entry name, so each kind of entry gets its own
configuration.

Rendering
---------

Entries include the ``collection_entry`` block prefix and they use ``entry`` as
their block name, like the entries of a
:doc:`CollectionType </reference/forms/types/collection>`. This means that the
form theme blocks that customize collection entries (e.g.
``collection_entry_row`` or ``_page_blocks_entry_row``) apply to them too. The
collection itself uses the ``polymorphic_collection`` block prefix instead of
``collection``.

When `allow_add`_ and `prototype`_ are enabled, the collection builds one
prototype per entry type instead of a single one. The ``prototypes`` view
variable holds them, indexed by entry name:

.. code-block:: twig

    {{ form_row(form.blocks.vars.prototypes.heading) }}

The collection widget also renders each prototype in a
``data-prototype-<entry name>`` HTML attribute (e.g. ``data-prototype-heading``)
of the element that wraps the entries. Your JavaScript code reads the attribute
of the kind of entry to add, replaces the ``__name__`` placeholder with a unique
index and inserts the result in the collection:

.. code-block:: html+twig

    {# the data-index attribute stores the index of the next entry #}
    {{ form_widget(form.blocks, {attr: {
        'data-index': form.blocks|length > 0 ? form.blocks|last.vars.name + 1 : 0
    }}) }}

    <button type="button" data-add-block="heading">Add a heading</button>
    <button type="button" data-add-block="paragraph">Add a paragraph</button>
    <button type="button" data-add-block="footer">Add a footer</button>

.. code-block:: javascript

    // the ID of the collection element is built from the form and field names
    const collection = document.querySelector('#page_blocks');

    document.querySelectorAll('[data-add-block]').forEach((button) => {
        button.addEventListener('click', () => {
            const blockType = button.dataset.addBlock;
            const prototype = collection.getAttribute('data-prototype-' + blockType);
            const index = collection.dataset.index++;

            const html = prototype.replace(/__name__/g, index);

            collection.insertAdjacentHTML('beforeend', html);
        });
    });

.. seealso::

    The :ref:`form-collections-new-prototype` section of the form collections
    article explains this technique in detail for ``CollectionType``.

Field Options
-------------

allow_add
~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Behaves as the ``allow_add`` option of
:doc:`CollectionType </reference/forms/types/collection>`, except that the
collection builds one prototype per entry type. See `Rendering`_.

allow_delete
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Behaves as the ``allow_delete`` option of
:doc:`CollectionType </reference/forms/types/collection>`.

delete_empty
~~~~~~~~~~~~

**type**: ``boolean`` or ``callable`` **default**: ``false``

Behaves as the ``delete_empty`` option of
:doc:`CollectionType </reference/forms/types/collection>`.

entry_options
~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The options passed to each entry, as one array of options per entry name.
Entry names that you don't include get no options::

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

entry_type_provider
~~~~~~~~~~~~~~~~~~~

**type**: :class:`Symfony\\Component\\Form\\EntryTypeProviderInterface` **required**

The object that decides which of the `entry_types`_ each entry uses. See
`Basic Usage`_.

entry_types
~~~~~~~~~~~

**type**: ``array`` **required**

The form types that the entries of the collection can use, as an array of form
type class names indexed by entry name. The `entry_type_provider`_ returns these
entry names and the `entry_options`_, `prototype_options`_ and `prototype_data`_
options use them as keys.

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
``prototypes`` view variable holds the prototypes. See `Rendering`_.

prototype_data
~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The initial data of the prototypes, as one value per entry name. It's only used
when the `allow_add`_ and `prototype`_ options are enabled::

    use App\Entity\Heading;
    // ...

    $builder->add('blocks', PolymorphicCollectionType::class, [
        // ...
        'allow_add' => true,
        'prototype_data' => [
            'heading' => new Heading('New heading'),
        ],
    ]);

prototype_name
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``__name__``

Behaves as the ``prototype_name`` option of
:doc:`CollectionType </reference/forms/types/collection>`. All prototypes use
the same placeholder.

prototype_options
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The options passed to the prototypes and to the entries added in the browser,
as one array of options per entry name. They override the `entry_options`_,
so you only need to define the options that change between adding a new entry
and editing an existing one::

    $builder->add('blocks', PolymorphicCollectionType::class, [
        // ...
        'entry_options' => [
            'heading' => ['help' => 'You can edit this heading here.'],
        ],
        'prototype_options' => [
            'heading' => ['help' => 'You can enter a new heading here.'],
        ],
    ]);

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

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Field Variables
---------------

============  ===========  =======================================================================================
Variable      Type         Usage
============  ===========  =======================================================================================
allow_add     ``boolean``  The value of the `allow_add`_ option.
allow_delete  ``boolean``  The value of the `allow_delete`_ option.
prototypes    ``array``    One prototype view per entry name, when `allow_add`_ and `prototype`_ are both enabled.
============  ===========  =======================================================================================
