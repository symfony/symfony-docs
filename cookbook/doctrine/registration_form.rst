.. index::
   single: Doctrine; Simple Registration Form
   single: Form; Simple Registration Form

How to implement a simple Registration Form
===========================================

Some forms have extra fields whose values don't need to be stored in the
database. For example, you may want to create a registration form with some
extra fields (like a "terms accepted" checkbox field) and embed the form
that actually stores the account information.

The simple User model
---------------------

You have a simple ``User`` entity mapped to the database::

    // src/Acme/AccountBundle/Entity/User.php
    namespace Acme\AccountBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

    /**
     * @ORM\Entity
     * @UniqueEntity(fields="email", message="Email already taken")
     */
    class User
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(type="string", length=255)
         * @Assert\NotBlank()
         * @Assert\Email()
         */
        protected $email;

        /**
         * @ORM\Column(type="string", length=255)
         * @Assert\NotBlank()
         * @Assert\Length(max = 4096)
         */
        protected $plainPassword;

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

        public function getPlainPassword()
        {
            return $this->plainPassword;
        }

        public function setPlainPassword($password)
        {
            $this->plainPassword = $password;
        }
    }

This ``User`` entity contains three fields and two of them (``email`` and
``plainPassword``) should display on the form. The email property must be unique
in the database, this is enforced by adding this validation at the top of
the class.

.. note::

    If you want to integrate this User within the security system, you need
    to implement the :ref:`UserInterface <book-security-user-entity>` of the
    Security component.

.. _cookbook-registration-password-max:

.. sidebar:: Why the 4096 Password Limit?

    Notice that the ``plainPassword`` field has a max length of 4096 characters.
    For security purposes (`CVE-2013-5750`_), Symfony limits the plain password
    length to 4096 characters when encoding it. Adding this constraint makes
    sure that your form will give a validation error if anyone tries a super-long
    password.

    You'll need to add this constraint anywhere in your application where
    your user submits a plaintext password (e.g. change password form). The
    only place where you don't need to worry about this is your login form,
    since Symfony's Security component handles this for you.

Create a Form for the Model
---------------------------

Next, create the form for the ``User`` model::

    // src/Acme/AccountBundle/Form/Type/UserType.php
    namespace Acme\AccountBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class UserType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('email', 'email');
            $builder->add('plainPassword', 'repeated', array(
               'first_name'  => 'password',
               'second_name' => 'confirm',
               'type'        => 'password',
            ));
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Acme\AccountBundle\Entity\User'
            ));
        }

        public function getName()
        {
            return 'user';
        }
    }

There are just two fields: ``email`` and ``plainPassword`` (repeated to confirm
the entered password). The ``data_class`` option tells the form the name of the
underlying data class (i.e. your ``User`` entity).

.. tip::

    To explore more things about the Form component, read :doc:`/book/forms`.

Embedding the User form into a Registration Form
------------------------------------------------

The form that you'll use for the registration page is not the same as the
form used to simply modify the ``User`` (i.e. ``UserType``). The registration
form will contain further fields like "accept the terms", whose value won't
be stored in the database.

Start by creating a simple class which represents the "registration"::

    // src/Acme/AccountBundle/Form/Model/Registration.php
    namespace Acme\AccountBundle\Form\Model;

    use Symfony\Component\Validator\Constraints as Assert;

    use Acme\AccountBundle\Entity\User;

    class Registration
    {
        /**
         * @Assert\Type(type="Acme\AccountBundle\Entity\User")
         * @Assert\Valid()
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
            $this->termsAccepted = (Boolean) $termsAccepted;
        }
    }

Next, create the form for this ``Registration`` model::

    // src/Acme/AccountBundle/Form/Type/RegistrationType.php
    namespace Acme\AccountBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class RegistrationType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('user', new UserType());
            $builder->add(
                'terms',
                'checkbox',
                array('property_path' => 'termsAccepted')
            );
            $builder->add('Register', 'submit');
        }

        public function getName()
        {
            return 'registration';
        }
    }

You don't need to use a special method for embedding the ``UserType`` form.
A form is a field, too - so you can add this like any other field, with the
expectation that the ``Registration.user`` property will hold an instance
of the ``User`` class.

Handling the Form Submission
----------------------------

Next, you need a controller to handle the form. Start by creating a simple
controller for displaying the registration form::

    // src/Acme/AccountBundle/Controller/AccountController.php
    namespace Acme\AccountBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    use Acme\AccountBundle\Form\Type\RegistrationType;
    use Acme\AccountBundle\Form\Model\Registration;

    class AccountController extends Controller
    {
        public function registerAction()
        {
            $registration = new Registration();
            $form = $this->createForm(new RegistrationType(), $registration, array(
                'action' => $this->generateUrl('account_create'),
            ));

            return $this->render(
                'AcmeAccountBundle:Account:register.html.twig',
                array('form' => $form->createView())
            );
        }
    }

And its template:

.. code-block:: html+jinja

    {# src/Acme/AccountBundle/Resources/views/Account/register.html.twig #}
    {{ form(form) }}

Next, create the controller which handles the form submission. This performs
the validation and saves the data into the database::

    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new RegistrationType(), new Registration());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $registration = $form->getData();

            $em->persist($registration->getUser());
            $em->flush();

            return $this->redirect(...);
        }

        return $this->render(
            'AcmeAccountBundle:Account:register.html.twig',
            array('form' => $form->createView())
        );
    }

Add New Routes
--------------

Next, update your routes. If you're placing your routes inside your bundle
(as shown here), don't forget to make sure that the routing file is being
:ref:`imported <routing-include-external-resources>`.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AccountBundle/Resources/config/routing.yml
        account_register:
            path:     /register
            defaults: { _controller: AcmeAccountBundle:Account:register }
   
        account_create:
            path:     /register/create
            defaults: { _controller: AcmeAccountBundle:Account:create }

    .. code-block:: xml

        <!-- src/Acme/AccountBundle/Resources/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="account_register" path="/register">
                <default key="_controller">AcmeAccountBundle:Account:register</default>
            </route>

            <route id="account_create" path="/register/create">
                <default key="_controller">AcmeAccountBundle:Account:create</default>
            </route>
        </routes>

    .. code-block:: php

        // src/Acme/AccountBundle/Resources/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('account_register', new Route('/register', array(
            '_controller' => 'AcmeAccountBundle:Account:register',
        )));
        $collection->add('account_create', new Route('/register/create', array(
            '_controller' => 'AcmeAccountBundle:Account:create',
        )));

        return $collection;

Update your Database Schema
---------------------------

Of course, since you've added a ``User`` entity during this tutorial, make
sure that your database schema has been updated properly:

.. code-block:: bash

   $ php app/console doctrine:schema:update --force

That's it! Your form now validates, and allows you to save the ``User``
object to the database. The extra ``terms`` checkbox on the ``Registration``
model class is used during validation, but not actually used afterwards when
saving the User to the database.

.. _`CVE-2013-5750`: http://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
