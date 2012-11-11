.. index::
    single: Forms
    single: Components; Form

The Form Component
==================

    The Form component allows you to easily create, process and reuse HTML
    forms.

The form component is a tool to help you solve the problem of allowing end-users
to interact with the data and modify the data in your application. And thought
traditionally this has been through HTML forms, the component focuses on
processing data to and from your client and application, whether that data
be from a normal form post or from an API.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Form);
* Install it via Composer (``symfony/form`` on `Packagist`_).

Configuration
-------------

.. tip::

    If you are working with the full-stack Symfony framework, the Form component
    is already configured for you. In this case, skip to :ref:`component-form-intro-create-simple-form`.

In Symfony2, forms are represented by objects and these objects are built
by using a *form factory*. Building a form factory is simple::

    use Symfony\Component\Form\Forms;

    $formFactory = Forms::createFormFactory();

This factory can already be used to create basic forms, but it is lacking
support for very important features:

* **Request Handling:** Support for request handling and file uploads;
* **CSRF Protection:** Support for protection against Cross-Site-Request-Forgery
  (CSRF) attacks;
* **Templating:** Integration with a templating layer that allows you to reuse
  HTML fragments when rendering a form;
* **Translation:** Support for translating error messages, field labels and
  other strings;
* **Validation:** Integration with a validation library to generate error
  messages for submitted data.

The Symfony2 Form component relies on other libraries to solve these problems.
Most of the time you will use Twig and the Symfony :doc:`HttpFoundation</components/http_foundation/introduction>`,
Translation and Validator components, but you can replace any of these with
a different library of your choice.

The following sections explain how to plug these libraries into the form
factory.

.. tip::

    For a working example, see https://github.com/bschussek/standalone-forms

Request Handling
~~~~~~~~~~~~~~~~

To process form data, you'll need to grab information off of the request (typically
``$_POST`` data) and pass the array of submitted data to :method:`Symfony\\Component\\Form\\Form::bind`.
The Form component optionally integrates with Symfony's :doc:`HttpFoundation</components/http_foundation/introduction>`
component to make this even easier.

To integrate the HttpFoundation component, add the
:class:`Symfony\\Component\\Form\\Extension\\HttpFoundation\HttpFoundationExtension`
to your form factory::

    use Symfony\Component\Form\Forms;
    use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

    $formFactory = Forms::createFormFactoryBuilder()
        ->addExtension(new HttpFoundationExtension())
        ->getFormFactory();

Now, when you process a form, you can pass the :class:`Symfony\\Component\\HttpFoundation\\Request``
object to :method:`Symfony\\Component\\Form\\Form::bind` instead of the raw
array of submitted values.

.. note::

    For more information about the ``HttpFoundation`` component or how to
    install it, see :doc:`/components/http_foundation/introduction`.

CSRF Protection
~~~~~~~~~~~~~~~

Protection against CSRF attacks is built into the Form component, but you need
to explicitly enable it or replace it with a custom solution. The following
snippet adds CSRF protection to the form factory::

    use Symfony\Component\Form\Forms;
    use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
    use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
    use Symfony\Component\HttpFoundation\Session\Session;

    // generate a CSRF secret, which you *may* want to set as a constant
    define('CSRF_SECRET', '<generated token>');

    // create a Session object from the HttpFoundation component
    $session = new Session();

    $csrfProvider = new SessionCsrfProvider($session, CSRF_SECRET);

    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->addExtension(new CsrfExtension($csrfProvider))
        ->getFormFactory();

To secure your application against CSRF attacks, you need to define a CSRF
secret. Generate a random string with at least 32 characters, insert it in the
above snippet and make sure that nobody except your web server can access
the secret.

Internally, this extension will automatically add a hidden field to every
form (called ``__token`` by default) whose value is automatically generated
and validated when binding the form.

.. tip::

    If you're not using the HttpFoundation component, load use
    :class:`Symfony\\Component\\Form\\Extension\\Csrf\\CsrfProvider\\DefaultCsrfProvider`
    instead, which relies on PHP's native session handling::

        use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;

        $csrfProvider = new DefaultCsrfProvider(CSRF_SECRET);

Twig Templating
~~~~~~~~~~~~~~~

If you're using the Form component to process HTML forms, you'll need a way
to easily render your form as HTML form fields (complete with field values,
errors, and labels). If you use `Twig`_ as your template engine, the Form
component offers a rich integration.

To use the integration, you'll need the ``TwigBridge``, which provides integration
between Twig and several Symfony2 components. If you're using Composer, you
could install the latest 2.1 version by adding the following ``require``
line to your ``composer.json`` file:

.. code-block:: json

    {
        "require": {
            "symfony/twig-bridge": "2.1.*"
        }
    }

The TwigBridge integration provides you with several :doc:`Twig Functions</reference/forms/twig_reference`
that help you render each the HTML widget, label and error for each field
(as well as a few other things). To configure the integration, you'll need
to bootstrap or access Twig and add the :class:`Symfony\\Bridge\\Twig\\Extension\\FormExtension`::

    use Symfony\Component\Form\Forms;
    use Symfony\Bridge\Twig\Extension\FormExtension;
    use Symfony\Bridge\Twig\Form\TwigRenderer;
    use Symfony\Bridge\Twig\Form\TwigRendererEngine;

    // the Twig file that holds all the default markup for rendering forms
    // this file comes with TwigBridge
    define('DEFAULT_FORM_THEME', 'form_div_layout.html.twig');

    define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
    // the path to TwigBridge so Twig can locate the form_div_layout.html.twig file
    define('VENDOR_TWIG_BRIDGE_DIR', VENDOR_DIR . '/symfony/twig-bridge/Symfony/Bridge/Twig');
    // the path to your other templates
    define('VIEWS_DIR', realpath(__DIR__ . '/../views'));

    $twig = new Twig_Environment(new Twig_Loader_Filesystem(array(
        VIEWS_DIR,
        VENDOR_TWIG_BRIDGE_DIR . '/Resources/views/Form',
    )));
    $formEngine = new TwigRendererEngine(array(DEFAULT_FORM_THEME));
    $formEngine->setEnvironment($twig);
    // add the FormExtension to Twig
    $twig->addExtension(new FormExtension(new TwigRenderer($formEngine, $csrfProvider)));

    // create your form factory as normal
    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->getFormFactory();

The exact details of your `Twig Configuration`_ will vary, but the goal is
always to add the :class:`Symfony\\Bridge\\Twig\\Extension\\FormExtension`
to Twig, which gives you access to the Twig functions for rendering forms.
To do this, you first need to create a :class:`Symfony\\Bridge\\Twig\\Form\\TwigRendererEngine`,
where you define your :ref:`form themes<cookbook-form-customization-form-themes>`
(i.e. resources/files that define form HTML markup).

For general details on rendering forms, see :doc:`/cookbook/form/form_customization`.

.. note::

    If you use the Twig integration, read ":ref:`component-form-intro-install-translation`"
    below for details on the needed translation filters.

.. _component-form-intro-install-translation:

Translation
~~~~~~~~~~~

If you're using the Twig integration with one of the default form theme files
(e.g. ``form_div_layout.html.twig``), there are 2 Twig filters (``trans``
and ``transChoice``) that are used for translating form labels, errors, option
text and other strings.

To add these Twig filters, you can either use the built-in
:class:`Symfony\\Bridge\\Twig\\Extension\\TranslationExtension` that integrates
with Symfony's ``Translation`` component, or add the 2 Twig filters yourself,
via your own Twig extension.

To use the built-in integration, be sure that your project has Symfony's
``Translation`` and :doc:`Config</components/config/introduction>` components
installed. If you're using Composer, you could get the latest 2.1 version
of each of these by adding the following to your ``composer.json`` file:

.. code-block:: json

    {
        "require": {
            "symfony/translation": "2.1.*",
            "symfony/config": "2.1.*"
        }
    }

Next, add the :class:`Symfony\\Bridge\\Twig\\Extension\\TranslationExtension`
to your ``Twig_Environment`` instance::

    use Symfony\Component\Form\Forms;
    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\Loader\XliffFileLoader;
    use Symfony\Bridge\Twig\Extension\TranslationExtension;

    // create the Translator
    $translator = new Translator('en');
    // somehow load some translations into it
    $translator->addLoader('xlf', new XliffFileLoader());
    $translator->addResource(
        'xlf',
        __DIR__.'/path/to/translations/messages.en.xlf',
        'en'
    );

    // add the TranslationExtension (gives us trans and transChoice filters)
    $twig->addExtension(new TranslationExtension($translator));

    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->getFormFactory();

Depending on how your translations are being loaded, you can now add string
keys, such as field labels, and their translations to your translation files.

For more details on translations, see :doc:`/book/translation`.

Validation
~~~~~~~~~~

The Form component comes with tight (but optional) integration with Symfony's
Validator component. If you're using a different solution for validation,
no problem! Simply take the bound data of your form (which is an array or
object) and pass it through your own validation system.

To use the integration with Symfony's Validator component, first make sure
it's installed in your application. If you're using Composer and want to
install the latest 2.1 version, add this to your ``composer.json``:

.. code-block:: json

    {
        "require": {
            "symfony/validator": "2.1.*"
        }
    }

If you're not familiar with Symfony's Validator component, read more about
it: :doc:`/book/validation`. The Form component comes with a
:class:`Symfony\\Component\\Form\\Extension\\Validator\\ValidatorExtension`
class, which automatically applies validation to your data on bind. These
errors are then mapped to the correct field and rendered.

Your integration with the Validation component will look something like this::

    use Symfony\Component\Form\Forms;
    use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
    use Symfony\Component\Validator\Validation;

    define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
    define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form/Symfony/Component/Form');
    define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/validator/Symfony/Component/Validator');

    // create the validator - details will vary
    $validator = Validation::createValidator();

    // there are built-in translations for the core error messages
    $translator->addResource('xlf', VENDOR_FORM_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');
    $translator->addResource('xlf', VENDOR_VALIDATOR_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');

    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->addExtension(new ValidatorExtension($validator))
        ->getFormFactory();

To learn more, skip down to the :ref:`component-form-intro-validation` section.

Accessing the Form Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~

Your application only needs one form factory, and that one factory object
should be used to create any and all form objects in your application. This
means that you should create it in some central, bootstrap part of your application
and then access it whenever you need to build a form.

Exactly how you gain access to your one form factory is up to you. If you're
using a :term`Service Container`, then you should add the form factory to
your container and grab it out whenever you need to. If your application
uses global or static variables (not usually a good idea), then you can store
the object on some static class or do something similar.

Regardless of how you architect your application, just remember that you
should only have one form factory and that you'll need to be able to access
it throughout your application.

.. _component-form-intro-create-simple-form:

Creating a Simple Form
----------------------

.. tip::

    If you're using the Symfony2 framework, then the form factory is available
    automatically as a service called ``form.factory``. Also, the default
    base controller class has a :method:`Symfony\\Bundle\\FrameworkBundle\\Controller::createFormBuilder`
    method, which is a shortcut to fetch the form factory and call ``createBuilder``
    on it.

Creating a form is done via a :class:`Symfony\\Component\\Form\\FormBuilder`
object, where you build and configure different fields. The form builder
is created from the form factory.

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
                // createFormBuilder is a shortcut to get the "form factory"
                // and then call "createBuilder" on it
                $form = $this->createFormBuilder()
                    ->add('task', 'text')
                    ->add('dueDate', 'date')
                    ->getForm();

                return $this->render('AcmeTaskBundle:Default:new.html.twig', array(
                    'form' => $form->createView(),
                ));
            }
        }

As you can see, creating a form is like writing a recipe: you call ``add``
for each new field you want to create. The first argument to ``add`` is the
name of your field, and the second is the field "type". The Form component
comes with a lot of :ref:`built-in types</reference/forms/types>`.

Now that you've built your form, learn how to :ref:`render<component-form-intro-rendering-form>`
it and :ref:`process the form submission<component-form-intro-handling-submission>`.

Setting Default Values
~~~~~~~~~~~~~~~~~~~~~~

If you need you form to load with some default values (or you're building
an "edit" form), simply pass in the default data when creating your form
builder:

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

.. tip::

    In this example, the default data is an array. Later, when you use the
    :ref:`data_class<book-forms-data-class>` option to bind data directly
    to objects, your default data will be an instance of that object.

.. _component-form-intro-rendering-form:

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

.. _component-form-intro-handling-submission:

Handling Form Submissions
~~~~~~~~~~~~~~~~~~~~~~~~~

To handle form submissions, use the :method:`Symfony\\Component\\Form\\Form::bind`
method:

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

This defines a common form "workflow", which looks like this:

1) Build your form;
2) If POST, process the form by calling ``bind``;
3a) If the form is valid, perform some action and redirect;
3b) If the form is invalid, re-render the form (which will now contain errors)

.. note::

    If you're not using HttpFoundation, just pass the POST'ed data directly
    to ``bind``:

        if (isset($_POST[$form->getName()])) {
            $form->bind($_POST[$form->getName())

            // ...
        }

    If you're uploading files, you'll need to do a little bit more work by
    merging the ``$_POST`` array with the ``$_FILES`` array before passing
    it into ``bind``.

.. _component-form-intro-validation:

Form Validation
~~~~~~~~~~~~~~~

The easiest way to add validation to your form is via the ``constraints``
option when building each field:

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

When the form is bound, these validation constraints will be applied automatically
and the errors will display next to the fields on error.

.. note::

    For a list of all of the built-in validation constraints, see
    :doc:`/reference/constraints`.

.. _Packagist: https://packagist.org/packages/symfony/form
.. _Twig:      http://twig.sensiolabs.org
