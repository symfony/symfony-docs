.. index::
   single: Forms; Fields; choice

entity Field Type
=================

A special ``choice`` field that's designed to load options from a Doctrine
entity. For example, if you have a ``Category`` entity, you could use this
field to display a ``select`` field of all, or some, of the ``Category``
objects from the database.

+----------------------+------------------------------------------------------------------+
| Underlying Data Type | An array of entity "identifiers" (e.g. an array of selected ids) |
+----------------------+------------------------------------------------------------------+
| Rendered as          | can be various tags (see :ref:`forms-reference-choice-tags`)     |
+----------------------+------------------------------------------------------------------+
| Options              | - ``class``                                                      |
|                      | - ``property``                                                   |
|                      | - ``query_builder``                                              |
|                      | - ``multiple``                                                   |
|                      | - ``expanded``                                                   |
|                      | - ``preferred_choices``                                          |
|                      | - ``required``                                                   |
|                      | - ``label``                                                      |
|                      | - ``read_only``                                                  |
|                      | - ``error_bubbling``                                             |
+----------------------+------------------------------------------------------------------+
| Parent type          | :doc:`choice</reference/forms/types/choice>`                     |
+----------------------+------------------------------------------------------------------+
| Class                | :class:`Symfony\Bridge\Doctrine\Form\Type\\EntityType`           |
+----------------------+------------------------------------------------------------------+

Basic Usage
-----------

The ``entity`` type has just one required option: the entity which should
be listed inside the choice field::

    $builder->add('users', 'entity', array(
        'class' => 'Acme\\HelloBundle\\Entity\\User',
    ));

In this case, all ``User`` objects will be loaded from the database and rendered
as either a ``select`` tag, a set or radio buttons or a series of checkboxes
(this depends on the ``multiple`` and ``expanded`` values).

Using a Custom Query for the Entities
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to specify a custom query to use when fetching the entities (e.g.
you only want to return some entities, or need to order them), use the ``query_builder``
option. The easiest way to use the option is as follows::

    use Doctrine\ORM\EntityRepository;
    // ...

    $builder->add('users', 'entity', array(
        'class' => 'Acme\\HelloBundle\\Entity\\User',
        'query_builder' => function(EntityRepository $er) {
            return $er->createQueryBuilder('u')
                ->orderBy('u.username', 'ASC');
        },
    ));

.. include:: /reference/forms/types/options/select_how_rendered.rst.inc

.. include:: /reference/forms/types/options/empty_value.rst.inc

Options
-------

*   ``class`` **required** [type: string]
    The class of your entity (e.g. ``Acme\StoreBundle\Entity\Category``).

*   ``property`` [type: string]
    This is the property that should be used for displaying the entities
    as text in the HTML element. If left blank, the entity object will be
    cast into a string and so must have a ``__toString()`` method.

*   ``query_builder`` [type: ``Doctrine\ORM\QueryBuilder`` or a Closure]
    If specified, this is used to query the subset of options (and their
    order) that should be used for the field. The value of this option can
    either be a ``QueryBuilder`` object or a Closure. If using a Closure,
    it should take a single argument, which is the ``EntityRepository`` of
    the entity.

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
