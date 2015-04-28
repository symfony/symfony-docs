.. index::
   single: Doctrine; Simple Registration Form
   single: Form; Simple Registration Form

How to Implement a simple Registration Form
===========================================

Creating a registration form is pretty easy - it *really* means just creating
a form that will update some ``User`` object (a Doctrine entity in this example)
and then save it.

If you don't already have a ``User`` entity and a working login system,
first start with :doc:`/cookbook/security/entity_provider`.

Your ``User`` entity will probably at least have the following fields:
* ``email``
* ``username``
* ``password`` (the encoded password)
* ``plainPassword`` (*not* persisted: notice no ``@ORM\Column`` above it)
* anything else you want

With some validation added, it may look something like this::

    // src/AppBundle/Entity/User.php
    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * @ORM\Entity
     * @UniqueEntity(fields="email", message="Email already taken")
     * @UniqueEntity(fields="username", message="Username  already taken")
     */
    class User implements UserInterface
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
        protected $username;

        /**
         * @Assert\NotBlank()
         * @Assert\Length(max = 4096)
         */
        protected $plainPassword;

        /**
         * The below length depends on the "algorithm" you use for encoding
         * the password, but this works well with bcrypt
         *
         * @ORM\Column(type="string", length=64)
         */
        protected $password;

        // other properties

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

        public function getUsername()
        {
            return $this->username;
        }

        public function setUsername($username)
        {
            $this->username = $username;
        }

        public function getPlainPassword()
        {
            return $this->plainPassword;
        }

        public function setPlainPassword($password)
        {
            $this->plainPassword = $password;
        }

        public function setPassword($password)
        {
            $this->password = $password;
        }

        // other methods, including security methods like getRoles()
    }



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

Next, create the form for the ``User`` entity::

    // src/AppBundle/Form/UserType.php
    namespace AppBundle\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class UserType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('email', 'email');
                ->add('username', 'text');
                ->add('plainPassword', 'repeated', array(
                   'type' => 'password',
                )
            );
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'AppBundle\Entity\User'
            ));
        }

        public function getName()
        {
            return 'user';
        }
    }

There are just three fields: ``email``, ``username`` and ``plainPassword``
(repeated to confirm the entered password).

.. tip::

    To explore more things about the Form component, read :doc:`/book/forms`.

Handling the Form Submission
----------------------------

Next, you need a controller to handle the form. Start by creating a simple
controller for displaying the registration form::

    // src/AppBundle/Controller/AccountController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    use AppBundle\Form\UserType;
    use AppBundle\Entity\User;
    use Symfony\Component\HttpFoundation\Request;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    class RegistrationController extends Controller
    {
        /**
         * @Route("/register", name="user_registration")
         */
        public function registerAction(Request $request)
        {
            // 1) build the form
            $user = new User();
            $form = $this->createForm(new UserType(), $user);

            // 2) handle the submit (will only happen on POST)
            $form->handleRequest($request);
            if ($form->isValid()) {
                // save the User!
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                // do any other work - like send them an email, etc
                // maybe set a "flash" success message for the user

                $redirectUrl = $this->generateUrl('replace_with_some_route');

                return $this->redirect($redirectUrl);
            }

            return $this->render(
                'registration/register.html.twig',
                array('form' => $form->createView())
            );
        }
    }

.. note::

    If you decide to NOT use annotation routing (shown above), then you'll
    need to create a route to this controller:
    
    .. configuration-block::

        .. code-block:: yaml

            # app/config/routing.yml
            user_registration:
                path:     /register
                defaults: { _controller: AppBundle:Registration:register }

        .. code-block:: xml

            <!-- app/config/routing.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

                <route id="user_registration" path="/register">
                    <default key="_controller">AppBundle:Registration:register</default>
                </route>
            </routes>

        .. code-block:: php

            // app/config/routing.php
            use Symfony\Component\Routing\RouteCollection;
            use Symfony\Component\Routing\Route;

            $collection = new RouteCollection();
            $collection->add('user_registration', new Route('/register', array(
                '_controller' => 'AppBundle:Registration:register',
            )));

            return $collection;

Next, create the template:

.. code-block:: html+jinja

    {# app/Resources/views/registration/register.html.twig #}
    
    {{ form_start(form) }}
        {{ form_row('form.username') }}
        {{ form_row('form.email') }}

        {{ form_row('form.plainPassword.first', {
            'label': 'Password'
        }) }}
        {{ form_row('form.plainPassword.second', {
            'label': 'Repeat Password'
        }) }}

        <button type="submit">Register!</button>
    {{ form_end(form) }}

Update your Database Schema
---------------------------

If you've updated the ``User`` entity during this tutorial, make sure that
your database schema has been updated properly:

.. code-block:: bash

   $ php app/console doctrine:schema:update --force

That's it! Head to ``/register`` to try things out!

Having a Registration form with only Email (no Username)
--------------------------------------------------------

Todo

Adding a "accept terms" Checkbox
--------------------------------

Todo

.. _`CVE-2013-5750`: https://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
