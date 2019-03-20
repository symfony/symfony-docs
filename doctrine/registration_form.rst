.. index::
   single: Doctrine; Simple Registration Form
   single: Form; Simple Registration Form
   single: Security; Simple Registration Form

How to Implement a Registration Form
====================================

The basics of creating a registration form are the same as any normal form. After
all, you are creating an object with it (a user). However, since this is related
to security, there are some additional aspects. This article explains it all.

Before you get Started
----------------------

To create the registration form, make sure you have these 3 things ready:

**1) Install MakerBundle**

Make sure MakerBundle is installed:

.. code-block:: terminal

    $ composer require --dev symfony/maker-bundle

If you need any other dependencies, MakerBundle will tell you when you run each
command.

**2) Create a User Class**

If you already have a :ref:`User class <create-user-class>`, great! If not, you
can generate one by running:

.. code-block:: terminal

    $ php bin/console make:user

For more info, see :ref:`create-user-class`.

**3) (Optional) Create a Guard Authenticator**

If you want to automatically authenticate your user after registration, create
a Guard authenticator before generating your registration form. For details, see
the :ref:`firewalls-authentication` section on the main security page.

Adding the Registration System
------------------------------

To easiest way to build your registration form is by using the ``make:registration-form``
command:

.. versionadded:: 1.11

    The ``make:registration-form`` was introduced in MakerBundle 1.11.0.

.. code-block:: terminal

    $ php bin/console make:registration-form

This command needs to know several things - like your ``User`` class and information
about the properties on that class. The questions will vary based on your setup,
because the command will guess as much as possible.

When the command is done, congratulations! You have a functional registration form
system that's ready for you to customize. The generated files will look something
like what you see below.

RegistrationFormType
~~~~~~~~~~~~~~~~~~~~

The form class for the registration form will look something like this::

    namespace App\Form;

    use App\Entity\User;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\PasswordType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Validator\Constraints\NotBlank;
    use Symfony\Component\Validator\Constraints\Length;

    class RegistrationFormType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('email')
                ->add('plainPassword', PasswordType::class, [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 4096,
                        ]),
                    ],
                ])
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => User::class,
            ]);
        }
    }

.. _registration-password-max:

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

RegistrationController
~~~~~~~~~~~~~~~~~~~~~~

The controller builds the form and, on submit, encodes the plain password and
saves the user::

    namespace App\Controller;

    use App\Entity\User;
    use App\Form\RegistrationFormType;
    use App\Security\StubAuthenticator;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
    use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

    class RegistrationController extends AbstractController
    {
        /**
         * @Route("/register", name="app_register")
         */
        public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
        {
            $user = new User();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // do anything else you need here, like send an email

                return $this->redirectToRoute('app_homepage');
            }

            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }
    }

register.html.twig
~~~~~~~~~~~~~~~~~~

The template renders the form:

.. code-block:: twig

    {% extends 'base.html.twig' %}

    {% block title %}Register{% endblock %}

    {% block body %}
        <h1>Register</h1>

        {{ form_start(registrationForm) }}
            {{ form_row(registrationForm.email) }}
            {{ form_row(registrationForm.plainPassword) }}

            <button class="btn">Register</button>
        {{ form_end(registrationForm) }}
    {% endblock %}

Adding a "accept terms" Checkbox
--------------------------------

Sometimes, you want a "Do you accept the terms and conditions" checkbox on your
registration form. The only trick is that you want to add this field to your form
without adding an unnecessary new ``termsAccepted`` property to your ``User`` entity
that you'll never need.

To do this, add a ``termsAccepted`` field to your form, but set its
:ref:`mapped <reference-form-option-mapped>` option to ``false``::

    // src/Form/UserType.php
    // ...
    use Symfony\Component\Validator\Constraints\IsTrue;
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;

    class UserType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('email', EmailType::class)
                // ...
                ->add('termsAccepted', CheckboxType::class, [
                    'mapped' => false,
                    'constraints' => new IsTrue(),
                ])
            ;
        }
    }

The :ref:`constraints <form-option-constraints>` option is also used, which allows
us to add validation, even though there is no ``termsAccepted`` property on ``User``.

Manually Authenticating after Success
-------------------------------------

If you're using Guard authentication, you can :ref:`automatically authenticate <guard-manual-auth>`
after registration is successful. The generator may have already configured your
controller to take advantage of this.

.. _`CVE-2013-5750`: https://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
