CollectionType Field
====================

This field type is used to render a "collection" of some field or form.
In the easiest sense, it could be an array of ``TextType`` fields that populate
an array ``emails`` values. In more complex examples, you can embed entire
forms, which is useful when creating forms that expose one-to-many
relationships (e.g. a product from where you can manage many related product
photos).

When rendered, existing collection entries are indexed by the keys of the array
that is passed as the collection type field data.

+---------------------------+--------------------------------------------------------------------------+
| Rendered as               | depends on the `entry_type`_ option                                      |
+---------------------------+--------------------------------------------------------------------------+
| Default invalid message   | The collection is invalid.                                               |
+---------------------------+--------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                      |
+---------------------------+--------------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                            |
+---------------------------+--------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CollectionType` |
+---------------------------+--------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

.. note::

    If you are working with a collection of Doctrine entities, pay special
    attention to the `allow_add`_, `allow_delete`_ and `by_reference`_ options.
    You can also see a complete example in the :doc:`/form/form_collections`
    article.

Basic Usage
-----------

This type is used when you want to manage a collection of similar items
in a form. For example, suppose you have an ``emails`` field that corresponds
to an array of email addresses. In the form, you want to expose each email
address as its own input text box::

    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    // ...

    $builder->add('emails', CollectionType::class, [
        // each entry in the array will be an "email" field
        'entry_type' => EmailType::class,
        // these options are passed to each "email" type
        'entry_options' => [
            'attr' => ['class' => 'email-box'],
        ],
    ]);

The simplest way to render this is all at once:

.. code-block:: twig

    {{ form_row(form.emails) }}

A much more flexible method would look like this:

.. code-block:: html+twig

    {{ form_label(form.emails) }}
    {{ form_errors(form.emails) }}

    <ul>
    {% for emailField in form.emails %}
        <li>
            {{ form_errors(emailField) }}
            {{ form_widget(emailField) }}
        </li>
    {% endfor %}
    </ul>

In both cases, no input fields would render unless your ``emails`` data
array already contained some emails.

In this simple example, it's still impossible to add new addresses or remove
existing addresses. Adding new addresses is possible by using the `allow_add`_
option (and optionally the `prototype`_ option) (see example below). Removing
emails from the ``emails`` array is possible with the `allow_delete`_ option.

Field Options
-------------

allow_add
~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If set to ``true``, then if unrecognized items are submitted to the collection,
they will be added as new items. The ending array will contain the existing
items as well as the new item that was in the submitted data. See the above
example for more details.

The `prototype`_ option can be used to help render a prototype item that
can be used - with JavaScript - to create new form items dynamically on
the client side. For more information, see the above example and
:ref:`form-collections-new-prototype`.

.. caution::

    If you're embedding entire other forms to reflect a one-to-many database
    relationship, you may need to manually ensure that the foreign key of
    these new objects is set correctly. If you're using Doctrine, this won't
    happen automatically. See the above link for more details.

allow_delete
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If set to ``true``, then if an existing item is not contained in the submitted
data, it will be correctly absent from the final array of items. This means
that you can implement a "delete" button via JavaScript which removes a form
element from the DOM. When the user submits the form, its absence from the
submitted data will mean that it's removed from the final array.

For more information, see :ref:`form-collections-remove`.

.. caution::

    Be careful when using this option when you're embedding a collection
    of objects. In this case, if any embedded forms are removed, they *will*
    correctly be missing from the final array of objects. However, depending
    on your application logic, when one of those objects is removed, you
    may want to delete it or at least remove its foreign key reference to
    the main object. None of this is handled automatically. For more
    information, see :ref:`form-collections-remove`.

delete_empty
~~~~~~~~~~~~

**type**: ``Boolean`` or ``callable`` **default**: ``false``

If you want to explicitly remove entirely empty collection entries from your
form you have to set this option to ``true``. However, existing collection entries
will only be deleted if you have the allow_delete_ option enabled. Otherwise
the empty values will be kept.

.. caution::

    The ``delete_empty`` option only removes items when the normalized value is
    ``null``. If the nested `entry_type`_ is a compound form type, you must
    either set the ``required`` option to ``false`` or set the ``empty_data``
    option to ``null``. Both of these options can be set inside `entry_options`_.
    Read about the :ref:`form's empty_data option <reference-form-option-empty-data>`
    to learn why this is necessary.

A value is deleted from the collection only if the normalized value is ``null``.
However, you can also set the option value to a callable, which will be executed
for each value in the submitted collection. If the callable returns ``true``,
the value is removed from the collection. For example::

    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    // ...

    $builder->add('users', CollectionType::class, [
        // ...
        'delete_empty' => function (User $user = null) {
            return null === $user || empty($user->getFirstName());
        },
    ]);

Using a callable is particularly useful in case of compound form types, which
may define complex conditions for considering them empty.

entry_options
~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

This is the array that's passed to the form type specified in the `entry_type`_
option. For example, if you used the :doc:`ChoiceType </reference/forms/types/choice>`
as your `entry_type`_ option (e.g. for a collection of drop-down menus),
then you'd need to at least pass the ``choices`` option to the underlying
type::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    // ...

    $builder->add('favoriteCities', CollectionType::class, [
        'entry_type'   => ChoiceType::class,
        'entry_options'  => [
            'choices'  => [
                'Nashville' => 'nashville',
                'Paris'     => 'paris',
                'Berlin'    => 'berlin',
                'London'    => 'london',
            ],
        ],
    ]);

entry_type
~~~~~~~~~~

**type**: ``string`` **default**: ``'Symfony\Component\Form\Extension\Core\Type\TextType'``

This is the field type for each item in this collection (e.g. ``TextType``,
``ChoiceType``, etc). For example, if you have an array of email addresses,
you'd use the :doc:`EmailType </reference/forms/types/email>`. If you want
to embed a collection of some other form, pass the form type class as this
option (e.g. ``MyFormType::class``).

prototype
~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

This option is useful when using the `allow_add`_ option. If ``true`` (and
if `allow_add`_ is also ``true``), a special "prototype" attribute will
be available so that you can render a "template" example on your page of
what a new element should look like. The ``name`` attribute given to this
element is ``__name__``. This allows you to add a "add another" button via
JavaScript which reads the prototype, replaces ``__name__`` with some unique
name or number and render it inside your form. When submitted, it will
be added to your underlying array due to the `allow_add`_ option.

The prototype field can be rendered via the ``prototype`` variable in the
collection field:

.. code-block:: twig

    {{ form_row(form.emails.vars.prototype) }}

Note that all you really need is the "widget", but depending on how you're
rendering your form, having the entire "form row" may be easier for you.

.. tip::

    If you're rendering the entire collection field at once, then the prototype
    form row is automatically available on the ``data-prototype`` attribute
    of the element (e.g. ``div`` or ``table``) that surrounds your collection.

For details on how to actually use this option, see the above example as
well as :ref:`form-collections-new-prototype`.

prototype_data
~~~~~~~~~~~~~~

**type**: ``mixed`` **default**: ``null``

Allows you to define specific data for the prototype. Each new row added will
initially contain the data set by this option. By default, the data configured
for all entries with the `entry_options`_ option will be used::

    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    // ...

    $builder->add('tags', CollectionType::class, [
        'entry_type' => TextType::class,
        'allow_add' => true,
        'prototype' => true,
        'prototype_data' => 'New Tag Placeholder',
    ]);

prototype_name
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``__name__``

If you have several collections in your form, or worse, nested collections
you may want to change the placeholder so that unrelated placeholders are
not replaced with the same value.

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`.
Not all options are listed here - only the most applicable to this type:

.. include:: /reference/forms/types/options/attr.rst.inc

.. _reference-form-types-by-reference:

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

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Field Variables
---------------

============  ===========  ========================================
Variable      Type         Usage
============  ===========  ========================================
allow_add     ``boolean``  The value of the `allow_add`_ option.
allow_delete  ``boolean``  The value of the `allow_delete`_ option.
============  ===========  ========================================
