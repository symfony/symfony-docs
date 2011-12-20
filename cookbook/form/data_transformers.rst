Using Data Transformers
=======================

You'll often find the need to transform the data the user entered in a form into
something else for use in your program. You could easily do this manually in your
controller, but what if you want to use this specific form in different places?

Say you have a one-to-one relation of Task to Issue, e.g. a Task optionally has an
issue linked to it. Adding a listbox with all possible issues can eventually lead to
a really long listbox in which it is impossible to find something. You'll rather want
to add a textbox, in which the user can simply enter the number of the issue. In the
controller you can convert this issue number to an actual task, and eventually add
errors to the form if it was not found, but of course this is not really clean.

It would be better if this issue was automatically looked up and converted to an
Issue object, for use in your action. This is where Data Transformers come into play.

First, create a custom form type which has a Data Transformer attached to it, which
returns the Issue by number: the issue selector type. Eventually this will simply be 
a text field, as we configure the fields' parent to be a "text" field, in which you
will enter the issue number. The field will display an error if a non existing number
was entered::

    // src/Acme/TaskBundle/Form/IssueSelectorType.php
    namespace Acme\TaskBundle\Form\Type;
    
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;
    use Acme\TaskBundle\Form\DataTransformer\IssueToNumberTransformer;
    use Doctrine\Common\Persistence\ObjectManager;

    class IssueSelectorType extends AbstractType
    {
        private $om;
    
        public function __construct(ObjectManager $om)
        {
            $this->om = $om;
        }
    
        public function buildForm(FormBuilder $builder, array $options)
        {
            $transformer = new IssueToNumberTransformer($this->om);
            $builder->appendClientTransformer($transformer);
        }
    
        public function getDefaultOptions(array $options)
        {
            return array(
                'invalid_message'=>'The selected issue does not exist'
            );
        }
    
        public function getParent(array $options)
        {
            return 'text';
        }
    
        public function getName()
        {
            return 'issue_selector';
        }
    }

.. tip::

    You can also use transformers without creating a new custom form type
    by calling ``appendClientTransformer`` on any field builder::

        use Acme\TaskBundle\Form\DataTransformer\IssueToNumberTransformer;

        class TaskType extends AbstractType
        {
            public function buildForm(FormBuilder $builder, array $options)
            {
                // ...
            
                // this assumes that the entity manager was passed in as an option
                $entityManager = $options['em'];
                $transformer = new IssueToNumberTransformer($entityManager);

                // use a normal text field, but transform the text into an issue object
                $builder
                    ->add('issue', 'text')
                    ->appendClientTransformer($transformer)
                ;
            }
            
            // ...
        }

Next, we create the data transformer, which does the actual conversion::

    // src/Acme/TaskBundle/Form/DataTransformer/IssueToNumberTransformer.php
    namespace Acme\TaskBundle\Form\DataTransformer;
    
    use Symfony\Component\Form\Exception\TransformationFailedException;
    use Symfony\Component\Form\DataTransformerInterface;
    use Doctrine\Common\Persistence\ObjectManager;
    
    class IssueToNumberTransformer implements DataTransformerInterface
    {
        private $om;

        public function __construct(ObjectManager $om)
        {
            $this->om = $om;
        }

        // transforms the Issue object to a string
        public function transform($val)
        {
            if (null === $val) {
                return '';
            }

            return $val->getNumber();
        }

        // transforms the issue number into an Issue object
        public function reverseTransform($val)
        {
            if (!$val) {
                return null;
            }

            $issue = $this->om->getRepository('AcmeTaskBundle:Issue')->findOneBy(array('number' => $val));

            if (null === $issue) {
                throw new TransformationFailedException(sprintf('An issue with number %s does not exist!', $val));
            }

            return $issue;
        }
    }

Finally, since we've decided to create a custom form type that uses the data
transformer, register the Type in the service container, so that the entity
manager can be automatically injected:

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_demo.type.issue_selector:
                class: Acme\TaskBundle\Form\IssueSelectorType
                arguments: ["@doctrine.orm.entity_manager"]
                tags:
                    - { name: form.type, alias: issue_selector }

    .. code-block:: xml
    
        <service id="acme_demo.type.issue_selector" class="Acme\TaskBundle\Form\IssueSelectorType">
            <argument type="service" id="doctrine.orm.entity_manager"/>
            <tag name="form.type" alias="issue_selector" />
        </service>

You can now add the type to your form by its alias as follows::

    // src/Acme/TaskBundle/Form/Type/TaskType.php
    
    namespace Acme\TaskBundle\Form\Type;
    
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;
    
    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('task');
            $builder->add('dueDate', null, array('widget' => 'single_text'));
            $builder->add('issue', 'issue_selector');
        }
    
        public function getName()
        {
            return 'task';
        }
    }

Now it will be very easy at any random place in your application to use this
selector type to select an issue by number. No logic has to be added to your 
Controller at all.

If you want a new issue to be created when an unknown number is entered, you
can instantiate it rather than throwing the TransformationFailedException, and
even persist it to your entity manager if the task has no cascading options
for the issue.
