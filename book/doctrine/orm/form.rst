Form Integration
================

There is a tight integration between Doctrine ORM and the Symfony2 Form
component. Since Doctrine Entities are plain old php objects they nicely
integrate into the Form component by default, at least for the primitive data
types such as strings, integers and fields. However you can also integrate
them nicely with associations.

This is done by the help of a dedicated field:
:class:`Symfony\\Component\\Form\\EntityChoiceField`. It provides a list of
choices from which an entity can be selected.

.. code-block:: php

    use Symfony\Component\Form\EntityChoiceField;

    $field = new EntityChoiceField('users', array(
        'em' => $em,
        'class' => 'Sensio\\HelloBundle\\Entity\\User',
        'property' => 'username',
        'query_builder' => $qb,
    ));

    $form->addField($field);

The 'em' option expects the EntityManager, the 'class' option expects the Entity
class name as an argument. The optional 'property' option allows you to choose
the property used to display the entity (``__toString`` will be used if not
set). The optional 'query_builder' option expects a ``QueryBuilder`` instance or
a closure receiving the repository as an argument and returning the QueryBuilder
used to get the choices. If not set all entities will be used.

.. tip::

    ``EntityChoiceField`` extends :class:`Symfony\\Component\\Form\\ChoiceField`
    so you can also give the array of choices with the 'choices' option instead
    of using a QueryBuilder.
