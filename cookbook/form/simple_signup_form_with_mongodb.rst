How to implement a simple Registration Form with MongoDB
========================================================

Some forms have extra fields whose values don't need to be stored in the
database. In this example, we'll create a registration form with some extra
fields and (like a "terms accepted" checkbox field) and embed the form that
actually stores the account information. We'll use MongoDB for storing the data.

.. tip::

    If you are not familiar with Doctrine's MongoDB library, read
    ":doc:`/cookbook/doctrine/mongodb`" cookbook entry first to learn
    how to setup and work with MongoDB inside Symfony.

The simple User model
---------------------

So, in this tutorial we begin with the model for a ``User`` document::

    // src/Acme/AccountBundle/Document/User.php
    namespace Acme\AccountBundle\Document;

    use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Bundle\DoctrineMongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

    /**
     * @MongoDB\Document(collection="users")
     * @MongoDBUnique(path="email")
     */
    class User
    {
        /**
         * @MongoDB\Id
         */
        protected $id;

        /**
         * @MongoDB\Field(type="string")
         * @Assert\NotBlank()
         * @Assert\Email()
         */
        protected $email;

        /**
         * @MongoDB\Field(type="string")
         * @Assert\NotBlank()
         */
        protected $password;

        public function getId()
        {
            return $this->id;
        }

        public function getEmail()
        {
            return $this->email;
        }

        public function setEmail($email)
        {
            $this->email = $email;
        }

        public function getPassword()
        {
            return $this->password;
        }

        // stupid simple encryption (please don't copy it!)
        public function setPassword($password)
        {
            $this->password = sha1($password);
        }
    }

This ``User`` document contains three fields and two of them (email and
password) should display on the form. The email property must be unique
on the database, so we've added this validation at the top of the class.

.. note::

    If you want to integrate this User within the security system,you need
    to implement the :ref:`UserInterface<book-security-user-entity>` of the
    security component .

Create a Form for the Model
---------------------------

Next, create the form for the ``User`` model::

    // src/Acme/AccountBundle/Form/Type/UserType.php
    namespace Acme\AccountBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
    use Symfony\Component\Form\FormBuilder;

    class UserType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('email', 'email');
            $builder->add('password', 'repeated', array(
               'first_name' => 'password',
               'second_name' => 'confirm',
               'type' => 'password'
            ));
        }

        public function getDefaultOptions(array $options)
        {
            return array('data_class' => 'Acme\AccountBundle\Document\User');
        }

        public function getName()
        {
            return 'user';
        }
    }

We just added two fields: email and password (repeated to confirm the entered
password). The ``data_class`` option tells the form the name of data class
(i.e. your ``User`` document).

.. tip::

    To explore more things about form component, read this documentation :doc:`file</book/forms>`.

Embedding the User form into a Registration Form
------------------------------------------------

The form that you'll use for the registration page is not the same as the
form for used to simply modify the ``User`` (i.e. ``UserType``). The registration
form will contain further fields like "accept the terms", whose value is
won't be stored into database.

In other words, create a second form for registration, which embeds the ``User``
form and adds the extra field needed. Start by creating a simple class which
represents the "registration"::

    // src/Acme/AccountBundle/Form/Model/Registration.php
    namespace Acme\AccountBundle\Form\Model;

    use Symfony\Component\Validator\Constraints as Assert;

    use Acme\AccountBundle\Document\User;

    class Registration
    {
        /**
         * @Assert\Type(type="Acme\AccountBundle\Document\User")
         */
        protected $user;

        /**
         * @Assert\NotBlank()
         * @Assert\True()
         */
        protected $termsAccepted;

        public function setUser(User $user)
        {
            $this->user = $user;
        }

        public function getUser()
        {
            return $this->user;
        }

        public function getTermsAccepted()
        {
            return $this->termsAccepted;
        }

        public function setTermsAccepted($termsAccepted)
        {
            $this->termsAccepted = (boolean)$termsAccepted;
        }
    }

Next, create the form for this ``Registration`` model::

    // src/Acme/AccountBundle/Form/Type/RegistrationType.php
    namespace Acme\AccountBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
    use Symfony\Component\Form\FormBuilder;

    class RegistrationType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('user', new UserType());
            $builder->add('terms', 'checkbox', array('property_path' => 'termsAccepted'));
        }

        public function getName()
        {
            return 'registration';
        }
    }

You don't need to use special method for embedding the ``UserType`` form.
A form is a field, too - so you can add this like any other field, with the
expectation that the corresponding ``user`` property will hold an instance
of the class ``UserType``.

Handling the Form Submission
----------------------------

Next, you need a controller to handle the form. Start by creating a simple
controller for displaying the registration form::

    // src/Acme/AccountBundle/Controller/AccountController.php
    namespace Acme\AccountBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;

    use Acme\AccountBundle\Form\Type\RegistrationType;
    use Acme\AccountBundle\Form\Model\Registration;

    class AccountController extends Controller
    {
        public function registerAction()
        {
            $form = $this->createForm(new RegistrationType(), new Registration());

            return $this->render('AcmeAccountBundle:Account:register.html.twig', array('form' => $form->createView()));
        }
    }

and its template:

.. code-block:: html+jinja

    {# src/Acme/AccountBundle/Resources/views/Account/register.html.twig #}

    <form action="{{ path('create')}}" method="post" {{ form_enctype(form) }}>
        {{ form_widget(form) }}

        <input type="submit" />
    </form>

Finally, create the controller which handles the form submission.  This performs
the validation and saves the data into MongoDB::

    public function createAction()
    {
        $dm = $this->get('doctrine.odm.mongodb.default_document_manager');

        $form = $this->createForm(new RegistrationType(), new Registration());

        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $registration = $form->getData();

            $dm->persist($registration->getUser());
            $dm->flush();

            return $this->redirect(...);
        }

        return $this->render('AcmeAccountBundle:Account:register.html.twig', array('form' => $form->createView()));
    }

That's it! Your form now validates, and allows you to save the ``User``
object to MongoDB.
