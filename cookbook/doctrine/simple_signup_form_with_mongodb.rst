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

So, in this tutorial we begin with the model for the ``User``::

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

    If you want to integrate this User entity with the security system, 
    you need to implement the UserInterface of security component 
    :doc:`file</book/security>`.

Create form for the model
-------------------------

Now, you need to create form for this ``User`` model::

    // src/Acme/AccountBundle/Form/UserType.php
    namespace Acme\AccountBundle\Form; 

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
    }

We just added two fields: email and password (repeated to confirm the entered 
password). The ``data_class`` option tells the form the name of data class and 
this is your ``User`` document and the form is able to create the data model. 

.. tip::

    To explore more things about form component, 
    read this documentation :doc:`file</book/forms>`. 

Embedding User form into Signup form
------------------------------------

The form for sign up is not the same as the form for User. 
It contains further fields like accepting the terms which value is not needed 
to be stored into database. So, now we need to create own form for this purpose 
and embed the existing ``User`` form. For validation and creation of User 
data we need simple domain model for the sign up form::

    // src/Acme/AccountBundle/Form/Signup.php
    namespace Acme\AccountBundle\Form;

    use Symfony\Component\Validator\Constraints as Assert;

    use Acme\AccountBundle\Document\User;

    class Signup
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

And the form for this ``Signup`` model::

    // src/Acme/AccountBundle/Form/SignupType.php
    namespace Acme\AccountBundle\Form; 

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\RepeatedType; 
    use Symfony\Component\Form\FormBuilder; 

    class SignupType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('user', new UserType());
            $builder->add('terms', 'checkbox', array('property_path' => 'termsAccepted'));
        }
    }

We added two fields into the form. You don't need to use special method 
for embedding form. A form is a field, too - so you can add this like the fields, 
with the expectation that you need to instance the class ``UserType``.

Handling the Form Submission
----------------------------

Now we need controller to handle the form actions, first we create 
simple controller for displaying the sign up form::

    namespace Acme\AccountBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response; 

    use Acme\AccountBundle\Form; 

    class AccountController extends Controller
    {
        public function signupAction()
        {
            $form = $this->createForm(new Form\SignupType(), new Form\Signup());
        
            return $this->render('AcmeAccountBundle:Account:signup.html.twig', array('form' => $form->createView()));
        }
    }

and it's template:: 

    <form action="{{ path('create')}}" method="post" {{ form_enctype(form) }}>
        {{ form_widget(form) }}

        <input type="submit" />
    </form>        

At least we need the controller which handles the form submission. 
This performs the validation and saves the data into the database::

    public function createAction()
    {
        $dm = $this->get('doctrine.odm.mongodb.default_document_manager');
    
        $form = $this->createForm(new Form\SignupType(), new Form\Signup());
    
        $form->bindRequest($this->get('request')); 
    
        if ($form->isValid()) {
            $signup = $form->getData();
        
            $dm->persist($signup->getUser()); 
            $dm->flush();
        
            return $this->redirect($this->generateUrl('welcome', array('id' => $signup->getUser()->getId())));
        }
    
        return $this->render('AcmeAccountBundle:Account:signup.html.twig', array('form' => $form->createView()));
    }
