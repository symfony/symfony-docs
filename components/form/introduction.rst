.. index::
    single: Forms
    single: Components; Form

The Form Component
==================

    The Form component allows you to easily create, process and reuse HTML
    forms.

TODO: introduction

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Form);
* Install it via Composer (``symfony/form`` on `Packagist`_).

Configuration
-------------

.. tip::

    If you are working with the full-stack Symfony framework, the Form component
    is already configured for you. In this case you can skip this section.

In Symfony2, forms are represented by objects. These objects are constructed
with a *form factory*. Building a form factory is simple::

    use Symfony\Component\Form\Forms;

    $formFactory = Forms::createFormFactory();

This factory can already be used to create basic forms, but it is lacking
support for very important features:

* **Request Handling:** Support for request handling and file uploads;
* **CSRF Protection:** You should always protect forms against
  Cross-Site-Request-Forgery (CSRF) attacks;
* **Templating:** Support for a templating layer allows to reuse HTML fragments
  when rendering a form;
* **Translation:** You normally want to translate error messages, field labels
  or similar strings in your forms;
* **Validation:** You would like to use a validation library for validating
  the form inputs.

The Symfony2 Form component relies on other libraries to solve these problems.
Most of the time you will use Twig and the Symfony HttpFoundation, Translation
and Validator components, but you can replace any of these with a different
library of your choice.

The following sections explain how to plug Symfony2's libraries into the form
factory.

.. tip::

    See https://github.com/bschussek/standalone-forms

Request Handling
~~~~~~~~~~~~~~~~

.. code-block:: json

    {
        "require": {
            "symfony/http-foundation": "2.1.*"
        }
    }

.. code-block:: php

    use Symfony\Component\Form\Forms;
    use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

    $formFactory = Forms::createFormFactoryBuilder()
        ->addExtension(new HttpFoundationExtension())
        ->getFormFactory();

TODO: Cross reference the HTTP foundation chapter where the configuration of the
Templating component is explained.

CSRF Protection
~~~~~~~~~~~~~~~

Protection against CSRF attacks is built into the Form component, but you need
to explicitly enable it or replace it with a custom solution. The following
snippet adds CSRF protection to the form factory::

    use Symfony\Component\Form\Forms;
    use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
    use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
    use Symfony\Component\HttpFoundation\Session\Session;

    define('CSRF_SECRET', '<generated token>');

    $session = new Session();

    $csrfProvider = new SessionCsrfProvider($session, CSRF_SECRET);

    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->addExtension(new CsrfExtension($csrfProvider))
        ->getFormFactory();

To secure your application against CSRF attacks, you need to define a CSRF
secret. Generate a random string with at least 32 characters, insert it in the
above snippet and make sure that nobody except for your web server can access
the secret. That is all you need to enable CSRF protection.

.. tip::

    When not using HttpFoundation, load DefaultCsrfProvider instead which relies
    on PHP's native session handling::

        use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;

        $csrfProvider = new DefaultCsrfProvider(CSRF_SECRET);

Twig Templating
~~~~~~~~~~~~~~~

.. code-block:: json

    {
        "require": {
            "symfony/twig-bridge": "2.1.*"
        }
    }

.. code-block:: php

    use Symfony\Component\Form\Forms;
    use Symfony\Bridge\Twig\Extension\FormExtension;
    use Symfony\Bridge\Twig\Form\TwigRenderer;
    use Symfony\Bridge\Twig\Form\TwigRendererEngine;

    define('DEFAULT_FORM_THEME', 'form_div_layout.html.twig');

    define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
    define('VENDOR_TWIG_BRIDGE_DIR', VENDOR_DIR . '/symfony/twig-bridge/Symfony/Bridge/Twig');
    define('VIEWS_DIR', realpath(__DIR__ . '/../views'));

    $twig = new Twig_Environment(new Twig_Loader_Filesystem(array(
        VIEWS_DIR,
        VENDOR_TWIG_BRIDGE_DIR . '/Resources/views/Form',
    )));
    $formEngine = new TwigRendererEngine(array(DEFAULT_FORM_THEME));
    $formEngine->setEnvironment($twig);
    $twig->addExtension(new FormExtension(new TwigRenderer($formEngine, $csrfProvider)));

    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->getFormFactory();

TODO: Cross reference the templating chapter where the configuration of the
Templating component is explained. Also reference the Twig documentation where
more details about Twig and its configuration can be found.

Translation
~~~~~~~~~~~

.. code-block:: json

    {
        "require": {
            "symfony/translation": "2.1.*",
            "symfony/config": "2.1.*"
        }
    }

.. code-block:: php

    use Symfony\Component\Form\Forms;
    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\Loader\XliffFileLoader;
    use Symfony\Bridge\Twig\Extension\TranslationExtension;

    $translator = new Translator('en');
    $translator->addLoader('xlf', new XliffFileLoader());

    $twig->addExtension(new TranslationExtension($translator));

    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->getFormFactory();

TODO: Cross reference the translator docs where the detailed configuration of
the validator is explained.

Validation
~~~~~~~~~~

.. code-block:: json

    {
        "require": {
            "symfony/validator": "2.1.*"
        }
    }

.. code-block:: php

    use Symfony\Component\Form\Forms;
    use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
    use Symfony\Component\Validator\Validation;

    define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
    define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form/Symfony/Component/Form');
    define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/validator/Symfony/Component/Validator');

    $validator = Validation::createValidator();

    $translator->addResource('xlf', VENDOR_FORM_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');
    $translator->addResource('xlf', VENDOR_VALIDATOR_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');

    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->addExtension(new ValidatorExtension($validator))
        ->getFormFactory();

TODO: Cross reference the validator docs where the detailed configuration of
the validator is explained.

Accessing the Form Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~

Either in global variable or by use of DICs.

Creating a Simple Form
----------------------

.. configuration-block::

    .. code-block:: php-standalone

        $form = $formFactory->createBuilder()
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->getForm();

        echo $twig->render('new.html.twig', array(
            'form' => $form->createView(),
        ));

    .. code-block:: php-symfony

        // src/Acme/TaskBundle/Controller/DefaultController.php
        namespace Acme\TaskBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Symfony\Component\HttpFoundation\Request;

        class DefaultController extends Controller
        {
            public function newAction(Request $request)
            {
                $form = $this->createFormBuilder()
                    ->add('task', 'text')
                    ->add('dueDate', 'date')
                    ->getForm();

                return $this->render('AcmeTaskBundle:Default:new.html.twig', array(
                    'form' => $form->createView(),
                ));
            }
        }

Setting Default Values
~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: php-standalone

        $defaults = array(
            'dueDate' => new \DateTime('tomorrow'),
        );

        $form = $formFactory->createBuilder('form', $defaults)
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->getForm();

    .. code-block:: php-symfony

        $defaults = array(
            'dueDate' => new \DateTime('tomorrow'),
        );

        $form = $this->createFormBuilder($defaults)
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->getForm();

Rendering the Form
~~~~~~~~~~~~~~~~~~

Now that the form has been created, the next step is to render it. This is
done by passing a special form "view" object to your template (notice the
``$form->createView()`` in the controller above) and using a set of form
helper functions:

.. code-block:: html+jinja

    <form action="#" method="post" {{ form_enctype(form) }}>
        {{ form_widget(form) }}

        <input type="submit" />
    </form>

.. image:: /images/book/form-simple.png
    :align: center

That's it! By printing ``form_widget(form)``, each field in the form is
rendered, along with a label and error message (if there is one). As easy
as this is, it's not very flexible (yet). Usually, you'll want to render each
form field individually so you can control how the form looks. You'll learn how
to do that in the ":ref:`form-rendering-template`" section.

Handling Form Submissions
~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: php-standalone

        use Symfony\HttpFoundation\Request;
        use Symfony\Component\HttpFoundation\RedirectResponse;

        $form = $formFactory->createBuilder()
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->getForm();

        $request = Request::createFromGlobals();

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                // ... perform some action, such as saving the data to the database

                $response = new RedirectResponse('/task/success');
                $response->prepare($request);

                return $response->send();
            }
        }

        // ...

    .. code-block:: php-symfony

        // ...

        public function newAction(Request $request)
        {
            $form = $this->createFormBuilder()
                ->add('task', 'text')
                ->add('dueDate', 'date')
                ->getForm();

            if ($request->isMethod('POST')) {
                $form->bind($request);

                if ($form->isValid()) {
                    $data = $form->getData();

                    // ... perform some action, such as saving the data to the database

                    return $this->redirect($this->generateUrl('task_success'));
                }
            }

            // ...
        }


.. note::

    When not using HttpFoundation::

        if (isset($_POST[$form->getName()])) {
            $form->bind($_POST[$form->getName())

            // ...
        }

    but! no support for file uploads then


Form Validation
~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: php-standalone

        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\Type;

        $form = $formFactory->createBuilder()
            ->add('task', 'text', array(
                'constraints' => new NotBlank(),
            ))
            ->add('dueDate', 'date', array(
                'constraints' => array(
                    new NotBlank(),
                    new Type('\DateTime'),
                )
            ))
            ->getForm();

    .. code-block:: php-symfony

        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\Type;

        $form = $this->createFormBuilder()
            ->add('task', 'text', array(
                'constraints' => new NotBlank(),
            ))
            ->add('dueDate', 'date', array(
                'constraints' => array(
                    new NotBlank(),
                    new Type('\DateTime'),
                )
            ))
            ->getForm();


That's all you need to know for creating a basic form!

.. _Packagist: https://packagist.org/packages/symfony/form
