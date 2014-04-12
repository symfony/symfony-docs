.. index::
   single: Forms; Fields; choice

entity Field Type
=================

A special ``choice`` field that's designed to load options from a Doctrine
entity. For example, if you have a ``Category`` entity, you could use this
field to display a ``select`` field of all, or some, of the ``Category``
objects from the database.

+-------------+------------------------------------------------------------------+
| Rendered as | can be various tags (see :ref:`forms-reference-choice-tags`)     |
+-------------+------------------------------------------------------------------+
| Options     | - `class`_                                                       |
|             | - `data_class`_                                                  |
|             | - `property`_                                                    |
|             | - `group_by`_                                                    |
|             | - `query_builder`_                                               |
|             | - `em`_                                                          |
+-------------+------------------------------------------------------------------+
| Overridden  | - `choices`_                                                     |
| Options     | - `choice_list`_                                                 |
+-------------+------------------------------------------------------------------+
| Inherited   | - `multiple`_                                                    |
| options     | - `expanded`_                                                    |
|             | - `preferred_choices`_                                           |
|             | - `empty_value`_                                                 |
|             | - `empty_data`_                                                  |
|             | - `required`_                                                    |
|             | - `label`_                                                       |
|             | - `label_attr`_                                                  |
|             | - `data`_                                                        |
|             | - `read_only`_                                                   |
|             | - `disabled`_                                                    |
|             | - `error_bubbling`_                                              |
|             | - `error_mapping`_                                               |
|             | - `mapped`_                                                      |
+-------------+------------------------------------------------------------------+
| Parent type | :doc:`choice </reference/forms/types/choice>`                    |
+-------------+------------------------------------------------------------------+
| Class       | :class:`Symfony\\Bridge\\Doctrine\\Form\\Type\\EntityType`       |
+-------------+------------------------------------------------------------------+

Basic Usage
-----------

The ``entity`` type has just one required option: the entity which should
be listed inside the choice field::

    $builder->add('users', 'entity', array(
        'class' => 'AcmeHelloBundle:User',
        'property' => 'username',
    ));

In this case, all ``User`` objects will be loaded from the database and rendered
as either a ``select`` tag, a set or radio buttons or a series of checkboxes
(this depends on the ``multiple`` and ``expanded`` values).
If the entity object does not have a ``__toString()`` method the ``property`` option
is needed.

Using a Custom Query for the Entities
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to specify a custom query to use when fetching the entities (e.g.
you only want to return some entities, or need to order them), use the ``query_builder``
option. The easiest way to use the option is as follows::

    use Doctrine\ORM\EntityRepository;
    // ...

    $builder->add('users', 'entity', array(
        'class' => 'AcmeHelloBundle:User',
        'query_builder' => function(EntityRepository $er) {
            return $er->createQueryBuilder('u')
                ->orderBy('u.username', 'ASC');
        },
    ));

.. _reference-forms-entity-choices:

Using Choices
~~~~~~~~~~~~~

If you already have the exact collection of entities that you want included
in the choice element, you can simply pass them via the ``choices`` key.
For example, if you have a ``$group`` variable (passed into your form perhaps
as a form option) and ``getUsers`` returns a collection of ``User`` entities,
then you can supply the ``choices`` option directly::

    $builder->add('users', 'entity', array(
        'class' => 'AcmeHelloBundle:User',
        'choices' => $group->getUsers(),
    ));

.. include:: /reference/forms/types/options/select_how_rendered.rst.inc

Field Options
-------------

class
~~~~~

**type**: ``string`` **required**

The class of your entity (e.g. ``AcmeStoreBundle:Category``). This can be
a fully-qualified class name (e.g. ``Acme\StoreBundle\Entity\Category``)
or the short alias name (as shown prior).

.. include:: /reference/forms/types/options/data_class.rst.inc

property
~~~~~~~~

**type**: ``string``

This is the property that should be used for displaying the entities
as text in the HTML element. If left blank, the entity object will be
cast into a string and so must have a ``__toString()`` method.

.. note::

    The ``property`` option is the property path used to display the option. So you
    can use anything supported by the
    :doc:`PropertyAccessor component </components/property_access/introduction>`

    For example, if the translations property is actually an associative array of
    objects, each with a name property, then you could do this::

        $builder->add('gender', 'entity', array(
           'class' => 'MyBundle:Gender',
           'property' => 'translations[en].name',
        ));

group_by
~~~~~~~~

**type**: ``string``

This is a property path (e.g. ``author.name``) used to organize the
available choices in groups. It only works when rendered as a select tag
and does so by adding ``optgroup`` elements around options. Choices that do not
return a value for this property path are rendered directly under the
select tag, without a surrounding optgroup.

query_builder
~~~~~~~~~~~~~

**type**: ``Doctrine\ORM\QueryBuilder`` or a Closure

If specified, this is used to query the subset of options (and their
order) that should be used for the field. The value of this option can
either be a ``QueryBuilder`` object or a Closure. If using a Closure,
it should take a single argument, which is the ``EntityRepository`` of
the entity.

em
~~

**type**: ``string`` **default**: the default entity manager

If specified, the specified entity manager will be used to load the choices
instead of the default entity manager.

Overridden Options
------------------

choices
~~~~~~~

**type**:  array || ``\Traversable`` **default**: ``null``

Instead of allowing the `class`_ and `query_builder`_ options to fetch the
entities to include for you, you can pass the ``choices`` option directly.
See :ref:`reference-forms-entity-choices`.

choice_list
~~~~~~~~~~~

**default**: :class:`Symfony\\Bridge\\Doctrine\\Form\\ChoiceList\\EntityChoiceList`

The purpose of the ``entity`` type is to create and configure this ``EntityChoiceList``
for you, by using all of the above options. If you need to override this
option, you may just consider using the :doc:`/reference/forms/types/choice`
directly.

Inherited Options
-----------------

These options inherit from the :doc:`choice </reference/forms/types/choice>` type:

.. include:: /reference/forms/types/options/multiple.rst.inc

.. note::

    If you are working with a collection of Doctrine entities, it will be helpful
    to read the documentation for the :doc:`/reference/forms/types/collection`
    as well. In addition, there is a complete example in the cookbook article
    :doc:`/cookbook/form/form_collections`.

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. note::

    This option expects an array of entity objects, unlike the ``choice`` field
    that requires an array of keys.

.. include:: /reference/forms/types/options/empty_value.rst.inc

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/empty_data.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc
