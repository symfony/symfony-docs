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
    to implement the :ref:`UserInterface<book-security-user-entity>` of the
    security component.

Create a Form for the Model
---------------------------

Next, create the form for the ``User`` model::

    // src/Acme/AccountBundle/Form/Type/UserType.php
    namespace Acme\AccountBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class UserType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('email', 'email');
            $builder->add('plainPassword', 'repeated', array(
               'first_name' => 'password',
               'second_name' => 'confirm',
               'type' => 'password',
            ));
        }

        public function getDefaultOptions(array $options)
        {
            return array('data_class' => 'Acme\AccountBundle\Entity\User');
        }

        public function getName()
        {
            return 'user';
        }
    }

There are just two fields: ``email`` and ``plainPassword`` (repeated to confirm
the entered password). The ``data_class`` option tells the form the name of
data class (i.e. your ``User`` entity).

.. tip::

    To explore more things about the form component, read :doc:`/book/forms`.

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
expectation that the ``Registration.user`` property will hold an instance
of the ``User`` class.

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
the validation and saves the data into the database::

    public function createAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $form = $this->createForm(new RegistrationType(), new Registration());

        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $registration = $form->getData();

            $em->persist($registration->getUser());
            $em->flush();

            return $this->redirect(...);
        }

        return $this->render('AcmeAccountBundle:Account:register.html.twig', array('form' => $form->createView()));
    }

That's it! Your form now validates, and allows you to save the ``User``
object to the database. The extra ``terms`` checkbox on the ``Registration``
model class is used during validation, but not actually used afterwards when
saving the User to the database.
