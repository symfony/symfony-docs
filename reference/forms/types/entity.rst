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
| Options     | - `choice_label`_                                                |
|             | - `class`_                                                       |
|             | - `em`_                                                          |
|             | - `query_builder`_                                               |
+-------------+------------------------------------------------------------------+
| Overridden  | - `choices`_                                                     |
| options     | - `data_class`_                                                  |
+-------------+------------------------------------------------------------------+
| Inherited   | from the :doc:`choice </reference/forms/types/choice>` type:     |
| options     |                                                                  |
|             | - `choice_attr`_                                                 |
|             | - `choice_name`_                                                 |
|             | - `choice_translation_domain`_                                   |
|             | - `choice_value`_                                                |
|             | - `expanded`_                                                    |
|             | - `group_by`_                                                    |
|             | - `multiple`_                                                    |
|             | - `placeholder`_                                                 |
|             | - `preferred_choices`_                                           |
|             | - `translation_domain`_                                          |
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
|             | - `label_format`_                                                |
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
        'class' => 'AcmeHelloBundle:User',
        'choice_label' => 'username',
    ));

In this case, all ``User`` objects will be loaded from the database and
rendered as either a ``select`` tag, a set or radio buttons or a series
of checkboxes (this depends on the ``multiple`` and ``expanded`` values).
If the entity object does not have a ``__toString()`` method the ``choice_label``
option is needed.

Using a Custom Query for the Entities
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to specify a custom query to use when fetching the entities
(e.g. you only want to return some entities, or need to order them), use
the ``query_builder`` option. The easiest way to use the option is as follows::

    use Doctrine\ORM\EntityRepository;
    // ...

    $builder->add('users', 'entity', array(
        'class' => 'AcmeHelloBundle:User',
        'query_builder' => function (EntityRepository $er) {
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

choice_label
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_label`` option was introduced in Symfony 2.7. Prior to Symfony
    2.7, it was called ``property`` (which has the same functionality).

**type**: ``string`` or ``callable``

This is the property that should be used for displaying the entities as text in
the HTML element::

    $builder->add('category', 'entity', array(
        'class' => 'AppBundle:Category',
        'choice_label' => 'displayName',
    ));

If left blank, the entity object will be cast to a string and so must have a ``__toString()``
method. You can also pass a callback function for more control::

    $builder->add('category', 'entity', array(
        'class' => 'AppBundle:Category',
        'choice_label' => function ($category) {
            return $category->getDisplayName();
        }
    ));

The method is called for each entity in the list and passed to the function. For
more detais, see the main :ref:`choice_label <reference-form-choice-label>` documentation.

.. note::

    When passing a string, the ``choice_label`` option is a property path. So you
    can use anything supported by the
    :doc:`PropertyAccessor component </components/property_access/introduction>`

    For example, if the translations property is actually an associative
    array of objects, each with a name property, then you could do this::

        $builder->add('gender', 'entity', array(
           'class' => 'MyBundle:Gender',
           'choice_label' => 'translations[en].name',
        ));

class
~~~~~

**type**: ``string`` **required**

The class of your entity (e.g. ``AcmeStoreBundle:Category``). This can be
a fully-qualified class name (e.g. ``Acme\StoreBundle\Entity\Category``)
or the short alias name (as shown prior).

em
~~

**type**: ``string`` | ``Doctrine\Common\Persistence\ObjectManager`` **default**: the default entity manager

If specified, this entity manager will be used to load the choices
instead of the ``default`` entity manager.

query_builder
~~~~~~~~~~~~~

**type**: ``Doctrine\ORM\QueryBuilder`` or a Closure

If specified, this is used to query the subset of options (and their
order) that should be used for the field. The value of this option can
either be a ``QueryBuilder`` object or a Closure. If using a Closure,
it should take a single argument, which is the ``EntityRepository`` of
the entity and return an instance of ``QueryBuilder``.

Overridden Options
------------------

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

.. include:: /reference/forms/types/options/choice_attr.rst.inc

.. include:: /reference/forms/types/options/choice_name.rst.inc

.. include:: /reference/forms/types/options/choice_translation_domain.rst.inc

.. include:: /reference/forms/types/options/choice_value.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/group_by.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. note::

    If you are working with a collection of Doctrine entities, it will be
    helpful to read the documentation for the
    :doc:`/reference/forms/types/collection` as well. In addition, there
    is a complete example in the cookbook article
    :doc:`/cookbook/form/form_collections`.

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. note::

    This option expects an array of entity objects, unlike the ``choice``
    field that requires an array of keys.

.. include:: /reference/forms/types/options/choice_type_translation_domain.rst.inc

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

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc
