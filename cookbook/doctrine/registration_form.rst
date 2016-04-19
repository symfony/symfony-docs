.. index::
   single: Doctrine; Simple Registration Form
   single: Form; Simple Registration Form
   single: Security; Simple Registration Form

How to Implement a Simple Registration Form
===========================================

Creating a registration form is pretty easy - it *really* means just creating
a form that will update some ``User`` model object (a Doctrine entity in this
example) and then save it.

.. tip::

    The popular `FOSUserBundle`_ provides a registration form, reset password
    form and other user management functionality.

If you don't already have a ``User`` entity and a working login system,
first start with :doc:`/cookbook/security/entity_provider`.

Your ``User`` entity will probably at least have the following fields:

``username``
    This will be used for logging in, unless you instead want your user to
    :ref:`login via email <registration-form-via-email>` (in that case, this
    field is unnecessary).

``email``
    A nice piece of information to collect. You can also allow users to
    :ref:`login via email <registration-form-via-email>`.

``password``
    The encoded password.

``plainPassword``
    This field is *not* persisted: (notice no ``@ORM\Column`` above it). It
    temporarily stores the plain password from the registration form. This field
    can be validated and is then used to populate the ``password`` field.

With some validation added, your class may look something like this::

    // src/AppBundle/Entity/User.php
    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * @ORM\Entity
     * @UniqueEntity(fields="email", message="Email already taken")
     * @UniqueEntity(fields="username", message="Username already taken")
     */
    class User implements UserInterface
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @ORM\Column(type="string", length=255, unique=true)
         * @Assert\NotBlank()
         * @Assert\Email()
         */
        private $email;

        /**
         * @ORM\Column(type="string", length=255, unique=true)
         * @Assert\NotBlank()
         */
        private $username;

        /**
         * @Assert\NotBlank()
         * @Assert\Length(max=4096)
         */
        private $plainPassword;

        /**
         * The below length depends on the "algorithm" you use for encoding
         * the password, but this works well with bcrypt.
         *
         * @ORM\Column(type="string", length=64)
         */
        private $password;

        // other properties and methods

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

        public function getSalt()
        {
            // The bcrypt algorithm doesn't require a separate salt.
            // You *may* need a real salt if you choose a different encoder.
            return null;
        }

        // other methods, including security methods like getRoles()
    }

The :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface` requires
a few other methods and your ``security.yml`` file needs to be configured
properly to work with the ``User`` entity. For a more complete example, see
the :ref:`Entity Provider <security-crete-user-entity>` article.

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

.. _create-a-form-for-the-model:

Create a Form for the Entity
----------------------------

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
                ->add('email', 'email')
                ->add('username', 'text')
                ->add('plainPassword', 'repeated', array(
                    'type' => 'password',
                    'first_options'  => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat Password'),
                )
            );
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'AppBundle\Entity\User',
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

    To explore more things about the Form component, read the
    :doc:`chapter about forms </book/forms>` in the book.

Handling the Form Submission
----------------------------

Next, you need a controller to handle the form rendering and submission. If the
form is submitted, the controller performs the validation and saves the data
into the database::

    // src/AppBundle/Controller/RegistrationController.php
    namespace AppBundle\Controller;

    use AppBundle\Form\UserType;
    use AppBundle\Entity\User;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;

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
            if ($form->isSubmitted() && $form->isValid()) {

                // 3) Encode the password (you could also do this via Doctrine listener)
                $encoder = $this->get('security.encoder_factory')
                    ->getEncoder($user);
                $password = $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
                $user->setPassword($password);

                // 4) save the User!
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                // ... do any other work - like sending them an email, etc
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

To define the algorithm used to encode the password in step 3 configure the
encoder in the security configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            encoders:
                AppBundle\Entity\User: bcrypt

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <encoder class="AppBundle\Entity\User">bcrypt</encoder>
            </config>
        </srv:container>
        
    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'encoders' => array(
                'AppBundle\Entity\User' => 'bcrypt',
            ),
        ));

In this case the recommended ``bcrypt`` algorithm is used. To learn more
about how to encode the users password have a look into the
:ref:`security chapter <book-security-encoding-user-password>`.

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

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/registration/register.html.twig #}

        {{ form_start(form) }}
            {{ form_row(form.username) }}
            {{ form_row(form.email) }}
            {{ form_row(form.plainPassword.first) }}
            {{ form_row(form.plainPassword.second) }}

            <button type="submit">Register!</button>
        {{ form_end(form) }}

    .. code-block:: html+php

        <!-- app/Resources/views/registration/register.html.php -->

        <?php echo $view['form']->start($form) ?>
            <?php echo $view['form']->row($form['username']) ?>
            <?php echo $view['form']->row($form['email']) ?>

            <?php echo $view['form']->row($form['plainPassword']['first']) ?>
            <?php echo $view['form']->row($form['plainPassword']['second']) ?>

            <button type="submit">Register!</button>
        <?php echo $view['form']->end($form) ?>

See :doc:`/cookbook/form/form_customization` for more details.

Update your Database Schema
---------------------------

If you've updated the ``User`` entity during this tutorial, you have to update
your database schema using this command:

.. code-block:: bash

   $ php app/console doctrine:schema:update --force

That's it! Head to ``/register`` to try things out!

.. _registration-form-via-email:

Having a Registration form with only Email (no Username)
--------------------------------------------------------

If you want your users to login via email and you don't need a username, then you
can remove it from your ``User`` entity entirely. Instead, make ``getUsername()``
return the ``email`` property::

    // src/AppBundle/Entity/User.php
    // ...

    class User implements UserInterface
    {
        // ...

        public function getUsername()
        {
            return $this->email;
        }

        // ...
    }

Next, just update the ``providers`` section of your ``security.yml`` file
so that Symfony knows how to load your users via the ``email`` property on
login. See :ref:`authenticating-someone-with-a-custom-entity-provider`.

Adding a "accept terms" Checkbox
--------------------------------

Sometimes, you want a "Do you accept the terms and conditions" checkbox on your
registration form. The only trick is that you want to add this field to your form
without adding an unnecessary new ``termsAccepted`` property to your ``User`` entity
that you'll never need.

To do this, add a ``termsAccepted`` field to your form, but set its
:ref:`mapped <reference-form-option-mapped>` option to ``false``::

    // src/AppBundle/Form/UserType.php
    // ...
    use Symfony\Component\Validator\Constraints\IsTrue;

    class UserType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('email', 'email');
                // ...
                ->add('termsAccepted', 'checkbox', array(
                    'mapped' => false,
                    'constraints' => new IsTrue(),
                ))
            );
        }
    }

The :ref:`constraints <form-option-constraints>` option is also used, which allows
us to add validation, even though there is no ``termsAccepted`` property on ``User``.

.. _`CVE-2013-5750`: https://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
.. _`FOSUserBundle`: https://github.com/FriendsOfSymfony/FOSUserBundle
