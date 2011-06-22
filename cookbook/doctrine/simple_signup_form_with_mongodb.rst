How to implement simple Sign up Form with MongoDB
=================================================

Some forms have extra fields which value are not needed to be stored into 
database. In this example we create sign up form with some extra fields and 
embed the form for storing the account information. We use MongoDB for 
storing data. 

This explains how to integrate two types of domain model into the form, too.

.. tip::

    If you are not familiar with Doctrine MongoDB Bundle, you should read 
    this :doc:`file</cookbook/doctrine/mongodb>` recipe first to learn 
    how to setup the MongoDB Bundle to be able to work with MongoDB.

The simple Account model
------------------------

So, in this tutorial we begin with the model for the ``Account``::

    // src/Acme/AccountBundle/Document/Account.php
    namespace Acme\AccountBundle\Document;
    use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Bundle\DoctrineMongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

    /**
     * @MongoDB\Document(collection="accounts")
     * @MongoDBUnique(path="email")
     */
    class Account
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

This ``Account`` document contains three fields and two of them (email and
password) should display on the form. The email property must be unique 
on the database, so we've added this validation at the top of the class. 

Create form for the model
-------------------------

Now, you need to create form for this ``Account`` model::

    // src/Acme/AccountBundle/Form/Account.php
    namespace Acme\AccountBundle\Form; 

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\RepeatedType; 
    use Symfony\Component\Form\FormBuilder; 

    class AccountType extends AbstractType
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
            return array('data_class' => 'Acme\AccountBundle\Document\Account');
        }
    }

We just added two fields: email and password (repeated to confirm the entered 
password). The ``data_class`` option tells the form the name of data class and 
this is your ``Account`` document and the form is able to create the data model. 

.. tip::

    To explore more things about form component, 
    read this documentation :doc:`file</book/forms>`. 

Embedding Account form into Signup form
---------------------------------------

The form for sign up is not the same as the form for Account. 
It contains further fields like accepting the terms which value is not needed 
to be stored into database. So, now we need to create own form for this purpose 
and embed the existing ``Account`` form. For validation and creation of Account 
data we need simple domain model for the sign up form::

    // src/Acme/AccountBundle/Form/Signup.php
    namespace Acme\AccountBundle\Form;

    use Symfony\Component\Validator\Constraints as Assert;

    use Acme\AccountBundle\Document\Account;

    class Signup
    {    
        /**
         * @Assert\Type(type="Acme\AccountBundle\Document\Account")
         */
        protected $account; 
    
        /**
         * @Assert\NotBlank()
         * @Assert\True()
         */
        protected $termsAccepted;
    
        public function setAccount(Account $account)
        {
            $this->account = $account; 
        }
    
        public function getAccount()
        {
            return $this->account; 
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
            $builder->add('account', new AccountType());
            $builder->add('terms', 'checkbox', array('property_path' => 'termsAccepted'));
        }
    }

We added two fields into the form. You don't need to use special method 
for embedding form. A form is a field, too - so you can add this like the fields, 
with the expectation that you need to instance the class ``AccountType``.

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
        
            $dm->persist($signup->getAccount()); 
            $dm->flush();
        
            return $this->redirect($this->generateUrl('welcome', array('id' => $signup->getAccount()->getId())));
        }
    
        return $this->render('AcmeAccountBundle:Account:signup.html.twig', array('form' => $form->createView()));
    }
