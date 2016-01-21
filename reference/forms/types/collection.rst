.. index::
   single: Forms; Fields; CollectionType

CollectionType Field
====================

This field type is used to render a "collection" of some field or form.
In the easiest sense, it could be an array of ``TextType`` fields that populate
an array ``emails`` values. In more complex examples, you can embed entire
forms, which is useful when creating forms that expose one-to-many
relationships (e.g. a product from where you can manage many related product
photos).

+-------------+-----------------------------------------------------------------------------+
| Rendered as | depends on the `entry_type`_ option                                         |
+-------------+-----------------------------------------------------------------------------+
| Options     | - `allow_add`_                                                              |
|             | - `allow_delete`_                                                           |
|             | - `delete_empty`_                                                           |
|             | - `entry_options`_                                                          |
|             | - `entry_type`_                                                             |
|             | - `prototype`_                                                              |
|             | - `prototype_name`_                                                         |
+-------------+-----------------------------------------------------------------------------+
| Inherited   | - `by_reference`_                                                           |
| options     | - `cascade_validation`_                                                     |
|             | - `empty_data`_                                                             |
|             | - `error_bubbling`_                                                         |
|             | - `error_mapping`_                                                          |
|             | - `label`_                                                                  |
|             | - `label_attr`_                                                             |
|             | - `mapped`_                                                                 |
|             | - `required`_                                                               |
+-------------+-----------------------------------------------------------------------------+
| Parent type | :doc:`FormType </reference/forms/types/form>`                               |
+-------------+-----------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CollectionType`    |
+-------------+-----------------------------------------------------------------------------+

.. note::

    If you are working with a collection of Doctrine entities, pay special
    attention to the `allow_add`_, `allow_delete`_ and `by_reference`_ options.
    You can also see a complete example in the cookbook article
    :doc:`/cookbook/form/form_collections`.

Basic Usage
-----------

This type is used when you want to manage a collection of similar items
in a form. For example, suppose you have an ``emails`` field that corresponds
to an array of email addresses. In the form, you want to expose each email
address as its own input text box::

    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    // ...

    $builder->add('emails', CollectionType::class, array(
        // each entry in the array will be an "email" field
        'entry_type'   => EmailType::class,
        // these options are passed to each "email" type
        'entry_options'  => array(
            'required'  => false,
            'attr'      => array('class' => 'email-box')
        ),
    ));

The simplest way to render this is all at once:

.. configuration-block::

    .. code-block:: twig

        {{ form_row(form.emails) }}

    .. code-block:: php

        <?php echo $view['form']->row($form['emails']) ?>

A much more flexible method would look like this:

.. configuration-block::

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

    .. code-block:: html+php

        <?php echo $view['form']->label($form['emails']) ?>
        <?php echo $view['form']->errors($form['emails']) ?>

        <ul>
        <?php foreach ($form['emails'] as $emailField): ?>
            <li>
                <?php echo $view['form']->errors($emailField) ?>
                <?php echo $view['form']->widget($emailField) ?>
            </li>
        <?php endforeach ?>
        </ul>

In both cases, no input fields would render unless your ``emails`` data
array already contained some emails.

In this simple example, it's still impossible to add new addresses or remove
existing addresses. Adding new addresses is possible by using the `allow_add`_
option (and optionally the `prototype`_ option) (see example below). Removing
emails from the ``emails`` array is possible with the `allow_delete`_ option.

Adding and Removing Items
~~~~~~~~~~~~~~~~~~~~~~~~~

If `allow_add`_ is set to ``true``, then if any unrecognized items are submitted,
they'll be added seamlessly to the array of items. This is great in theory,
but takes a little bit more effort in practice to get the client-side JavaScript
correct.

Following along with the previous example, suppose you start with two
emails in the ``emails`` data array. In that case, two input fields will
be rendered that will look something like this (depending on the name of
your form):

.. code-block:: html

    <input type="email" id="form_emails_0" name="form[emails][0]" value="foo@foo.com" />
    <input type="email" id="form_emails_1" name="form[emails][1]" value="bar@bar.com" />

To allow your user to add another email, just set `allow_add`_ to ``true``
and - via JavaScript - render another field with the name ``form[emails][2]``
(and so on for more and more fields).

To help make this easier, setting the `prototype`_ option to ``true`` allows
you to render a "template" field, which you can then use in your JavaScript
to help you dynamically create these new fields. A rendered prototype field
will look like this:

.. code-block:: html

    <input type="email"
        id="form_emails___name__"
        name="form[emails][__name__]"
        value=""
    />

By replacing ``__name__`` with some unique value (e.g. ``2``),
you can build and insert new HTML fields into your form.

Using jQuery, a simple example might look like this. If you're rendering
your collection fields all at once (e.g. ``form_row(form.emails)``), then
things are even easier because the ``data-prototype`` attribute is rendered
automatically for you (with a slight difference - see note below) and all
you need is the JavaScript:

.. configuration-block::

    .. code-block:: html+twig

        {{ form_start(form) }}
            {# ... #}

            {# store the prototype on the data-prototype attribute #}
            <ul id="email-fields-list"
                data-prototype="{{ form_widget(form.emails.vars.prototype)|e }}">
            {% for emailField in form.emails %}
                <li>
                    {{ form_errors(emailField) }}
                    {{ form_widget(emailField) }}
                </li>
            {% endfor %}
            </ul>

            <a href="#" id="add-another-email">Add another email</a>

            {# ... #}
        {{ form_end(form) }}

        <script type="text/javascript">
            // keep track of how many email fields have been rendered
            var emailCount = '{{ form.emails|length }}';

            jQuery(document).ready(function() {
                jQuery('#add-another-email').click(function(e) {
                    e.preventDefault();

                    var emailList = jQuery('#email-fields-list');

                    // grab the prototype template
                    var newWidget = emailList.attr('data-prototype');
                    // replace the "__name__" used in the id and name of the prototype
                    // with a number that's unique to your emails
                    // end name attribute looks like name="contact[emails][2]"
                    newWidget = newWidget.replace(/__name__/g, emailCount);
                    emailCount++;

                    // create a new list element and add it to the list
                    var newLi = jQuery('<li></li>').html(newWidget);
                    newLi.appendTo(emailList);
                });
            })
        </script>

.. tip::

    If you're rendering the entire collection at once, then the prototype
    is automatically available on the ``data-prototype`` attribute of the
    element (e.g. ``div`` or ``table``) that surrounds your collection.
    The only difference is that the entire "form row" is rendered for you,
    meaning you wouldn't have to wrap it in any container element as it
    was done above.

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
:ref:`cookbook-form-collections-new-prototype`.

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
that you can implement a "delete" button via JavaScript which simply removes
a form element from the DOM. When the user submits the form, its absence
from the submitted data will mean that it's removed from the final array.

For more information, see :ref:`cookbook-form-collections-remove`.

.. caution::

    Be careful when using this option when you're embedding a collection
    of objects. In this case, if any embedded forms are removed, they *will*
    correctly be missing from the final array of objects. However, depending
    on your application logic, when one of those objects is removed, you
    may want to delete it or at least remove its foreign key reference to
    the main object. None of this is handled automatically. For more
    information, see :ref:`cookbook-form-collections-remove`.

delete_empty
~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

If you want to explicitly remove entirely empty collection entries from your
form you have to set this option to true. However, existing collection entries
will only be deleted if you have the allow_delete_ option enabled. Otherwise
the empty values will be kept.

entry_options
~~~~~~~~~~~~~

.. versionadded:: 2.8
    The ``entry_options`` option was introduced in Symfony 2.8 in favor of
    ``options``, which is available prior to 2.8.

**type**: ``array`` **default**: ``array()``

This is the array that's passed to the form type specified in the `entry_type`_
option. For example, if you used the :doc:`ChoiceType </reference/forms/types/choice>`
as your `entry_type`_ option (e.g. for a collection of drop-down menus),
then you'd need to at least pass the ``choices`` option to the underlying
type::

    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('favorite_cities', CollectionType::class, array(
        'entry_type'   => ChoiceType::class,
        'entry_options'  => array(
            'choices'  => array(
                'nashville' => 'Nashville',
                'paris'     => 'Paris',
                'berlin'    => 'Berlin',
                'london'    => 'London',
            ),
        ),
    ));

entry_type
~~~~~~~~~~

.. versionadded:: 2.8
    The ``entry_type`` option was introduced in Symfony 2.8 and replaces
    ``type``, which is available prior to 2.8.

**type**: ``string`` or :class:`Symfony\\Component\\Form\\FormTypeInterface` **required**

This is the field type for each item in this collection (e.g. ``TextType``,
``ChoiceType``, etc). For example, if you have an array of email addresses,
you'd use the :doc:`EmailType </reference/forms/types/email>`. If you want
to embed a collection of some other form, create a new instance of your
form type and pass it as this option.

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

.. configuration-block::

    .. code-block:: twig

        {{ form_row(form.emails.vars.prototype) }}

    .. code-block:: php

        <?php echo $view['form']->row($form['emails']->vars['prototype']) ?>

Note that all you really need is the "widget", but depending on how you're
rendering your form, having the entire "form row" may be easier for you.

.. tip::

    If you're rendering the entire collection field at once, then the prototype
    form row is automatically available on the ``data-prototype`` attribute
    of the element (e.g. ``div`` or ``table``) that surrounds your collection.

For details on how to actually use this option, see the above example as
well as :ref:`cookbook-form-collections-new-prototype`.

prototype_name
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``__name__``

If you have several collections in your form, or worse, nested collections
you may want to change the placeholder so that unrelated placeholders are
not replaced with the same value.

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`.
Not all options are listed here - only the most applicable to this type:

.. _reference-form-types-by-reference:

.. include:: /reference/forms/types/options/by_reference.rst.inc

.. include:: /reference/forms/types/options/cascade_validation.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``array()`` (empty array).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

error_bubbling
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

.. include:: /reference/forms/types/options/_error_bubbling_body.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

Field Variables
---------------

============  ===========  ========================================
Variable      Type         Usage
============  ===========  ========================================
allow_add     ``boolean``  The value of the `allow_add`_ option.
allow_delete  ``boolean``  The value of the `allow_delete`_ option.
============  ===========  ========================================
