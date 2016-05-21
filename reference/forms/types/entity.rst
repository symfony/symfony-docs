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
|             | - `em`_                                                          |
|             | - `group_by`_                                                    |
|             | - `property`_                                                    |
|             | - `query_builder`_                                               |
+-------------+------------------------------------------------------------------+
| Overridden  | - `choice_list`_                                                 |
| options     | - `choices`_                                                     |
|             | - `data_class`_                                                  |
+-------------+------------------------------------------------------------------+
| Inherited   | from the :doc:`choice </reference/forms/types/choice>` type:     |
| options     |                                                                  |
|             | - `empty_value`_                                                 |
|             | - `expanded`_                                                    |
|             | - `multiple`_                                                    |
|             | - `preferred_choices`_                                           |
|             |                                                                  |
|             | from the :doc:`form </reference/forms/types/form>` type:         |
|             |                                                                  |
|             | - `data`_                                                        |
|             | - `disabled`_                                                    |
|             | - `empty_data`_                                                  |
|             | - `error_bubbling`_                                              |
|             | - `error_mapping`_                                               |
|             | - `label`_                                                       |
|             | - `label_attr`_                                                  |
|             | - `mapped`_                                                      |
|             | - `read_only`_                                                   |
|             | - `required`_                                                    |
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
        // query choices from this entity
        'class' => 'AppBundle:User',

        // use the User.username property as the visible option string
        'property' => 'username',

        // used to render a select box, check boxes or radios
        // 'multiple' => true,
        // 'expanded' => true,
    ));

This will build a ``select`` drop-down containing *all* of the ``User`` objects
in the database. To render radio buttons or checkboxes instead, change the
`multiple`_ and `expanded`_ options.

.. _ref-form-entity-query-builder:

Using a Custom Query for the Entities
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to create a custom query to use when fetching the entities
(e.g. you only want to return some entities, or need to order them), use
the `query_builder`_ option::

    use Doctrine\ORM\EntityRepository;
    // ...

    $builder->add('users', 'entity', array(
        'class' => 'AppBundle:User',
        'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('u')
                ->orderBy('u.username', 'ASC');
        },
        'property' => 'username',
    ));

.. _reference-forms-entity-choices:

Using Choices
~~~~~~~~~~~~~

If you already have the exact collection of entities that you want to include
in the choice element, just pass them via the ``choices`` key.

For example, if you have a ``$group`` variable (passed into your form perhaps
as a form option) and ``getUsers`` returns a collection of ``User`` entities,
then you can supply the ``choices`` option directly::

    $builder->add('users', 'entity', array(
        'class' => 'AppBundle:User',
        'choices' => $group->getUsers(),
    ));

.. include:: /reference/forms/types/options/select_how_rendered.rst.inc

Field Options
-------------

class
~~~~~

**type**: ``string`` **required**

The class of your entity (e.g. ``AppBundle:Category``). This can be
a fully-qualified class name (e.g. ``AppBundle\Entity\Category``)
or the short alias name (as shown prior).

em
~~

**type**: ``string`` **default**: the default entity manager

If specified, the specified entity manager will be used to load the choices
instead of the default entity manager.

group_by
~~~~~~~~

**type**: ``string`` **default** ``null``

This is a property path (e.g. ``author.name``) used to organize the
available choices in groups. It only works when rendered as a select tag
and does so by adding ``optgroup`` elements around options. Choices that
do not return a value for this property path are rendered directly under
the select tag, without a surrounding optgroup.

property
~~~~~~~~

**type**: ``string`` **default**: ``null``

This is the property that should be used for displaying the entities
as text in the HTML element. If left blank, the entity object will be
cast into a string and so must have a ``__toString()`` method.

.. note::

    The ``property`` option is the property path used to display the option.
    So you can use anything supported by the
    :doc:`PropertyAccessor component </components/property_access/introduction>`

    For example, if the translations property is actually an associative
    array of objects, each with a name property, then you could do this::

        $builder->add('gender', 'entity', array(
           'class' => 'MyBundle:Gender',
           'property' => 'translations[en].name',
        ));

query_builder
~~~~~~~~~~~~~

**type**: ``Doctrine\ORM\QueryBuilder`` or a Closure **default**: ``null``

Allows you to create a custom query for your choices. See
:ref:`ref-form-entity-query-builder` for an example.

The value of this option can either be a ``QueryBuilder`` object, a Closure or
``null`` (which will load all entities). When using a Closure, you will be
passed the ``EntityRepository`` of the entity as the only argument and should
return a ``QueryBuilder``.

Overridden Options
------------------

choice_list
~~~~~~~~~~~

**default**: :class:`Symfony\\Bridge\\Doctrine\\Form\\ChoiceList\\EntityChoiceList`

The purpose of the ``entity`` type is to create and configure this
``EntityChoiceList`` for you, by using all of the above options. If you need
to override this option, you may just consider using the :doc:`/reference/forms/types/choice`
directly.

choices
~~~~~~~

**type**:  ``array`` | ``\Traversable`` **default**: ``null``

Instead of allowing the `class`_ and `query_builder`_ options to fetch the
entities to include for you, you can pass the ``choices`` option directly.
See :ref:`reference-forms-entity-choices`.

data_class
~~~~~~~~~~

**type**: ``string`` **default**: ``null``

This option is not used in favor of the ``class`` option which is required
to query the entities.

Inherited Options
-----------------

These options inherit from the :doc:`choice </reference/forms/types/choice>`
type:

.. include:: /reference/forms/types/options/empty_value.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. note::

    If you are working with a collection of Doctrine entities, it will be
    helpful to read the documentation for the
    :doc:`/reference/forms/types/collection` as well. In addition, there
    is a complete example in the cookbook article
    :doc:`/cookbook/form/form_collections`.

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. note::

    This option expects an array of entity objects, unlike the ``choice``
    field that requires an array of keys.

These options inherit from the :doc:`form </reference/forms/types/form>`
type:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The actual default value of this option depends on other field options:

* If ``multiple`` is ``false`` and ``expanded`` is ``false``, then ``''``
  (empty string);
* Otherwise ``array()`` (empty array).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc
