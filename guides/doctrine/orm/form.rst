Form Integration
================

There is a tight integration between Doctrine ORM and the Symfony2 Form
component. Since Doctrine Entities are plain old php objects they nicely
integrate into the Form component by default, at least for the primitive data
types such as strings, integers and fields. However you can also integrate
them nicely with associations.

This is done by the help of ValueTransformers, which are form field extension
points. There are currently three transformers that allow you to transform
Doctrine ORM Collections and Entities into their identifier values that can be
used with the Form component. Furthermore they translate form values back to
the Doctrine representation in the most efficient way possible, issuing as few
queries as possible.

CollectionToChoiceTransformer
-----------------------------

This transformer allows you to transform a Collection of Entities into an
array of ids. This transformer should be used with the ChoiceField or any
compatible field that handles arrays of values::

    use Symfony\Component\Form\ChoiceField;
    use Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\CollectionToChoiceTransformer;

    $productTransformer = new CollectionToChoiceTransformer(array(
        'em' => $em,
        'className' => 'Product',
    ));

    $field = new ChoiceField('products', array(
        'choices' => $productChoices,
        'multiple' => true,
        'expanded' => true,
        'value_transformer' => $productTransformer,
    ));

    $form->addField($field);

The 'em' property expects the EntityManager, the 'className' property expects
the Entity Class name as an argument.

CollectionToStringTransformer
-----------------------------

This transformer allows you to transform a Collection of Entities into a
string separated by a separator. This is useful for lists of tags, usernames
or similar unique fields of your Entities.

EntityToIDTransformer
---------------------

This transformer converts an Entity into its ID and back to allow to select
many-to-one or one-to-one entities in choice fields. See this extended example
on how it works. In this case a list of all users is used in a Choice field to
be chosen from::

    use Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\EntityToIDTransformer;
    use Symfony\Component\Form\ChoiceField;

    $userChoices = array();
    $users = $em->getRepository('User')->findAll();
    foreach ($users AS $user) {
        $userChoices[$user->id] = $user->name;
    }

    $userTransformer = new EntityToIDTransformer(array(
        'em' => $em,
        'className' => 'User',
    ));
    $engineerField = new ChoiceField('engineer', array(
        'choices' => $userChoices,
        'value_transformer' => $userTransformer,
    ));
    $reporterField = new ChoiceField('reporter', array(
        'choices' => $userChoices,
        'value_transformer' => $userTransformer,
    ));

    $form->add($engineerField);
    $form->add($reporterField);