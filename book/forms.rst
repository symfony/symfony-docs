.. index::
   single: Forms

Forms
=====

Dealing with HTML forms is one of the most common - and challenging - tasks
for a web developer. Symfony2 integrates a Form component that makes dealing
with forms easy. In this chapter, you'll build a complex form from the ground-up,
learning the most important features of the form library along the way.

.. note::

   The Symfony form component is a standalone library that can be used outside
   of Symfony2 projects. For more information, see the `Symfony2 Form Component`_
   on Github.

.. index::
   single: Forms; Create a simple form

Creating a Simple Form
----------------------

Suppose you're building a simple store application that will need to display
products. Because your users will need to edit and create products, you're
going to need to build a form. But before you begin, let's focus on the generic
``Product`` class that represents and stores the data for a single product:

.. code-block:: php

    // src/Acme/StoreBundle/Entity/Product.php
    namespace Acme\StoreBundle\Entity;
    
    class Product
    {
        public $name;
        
        protected $price;
        
        public function getPrice()
        {
            return $this->price;
        }

        public function setPrice($price)
        {
            $this->price = $price;
        }
    }

.. note::

   If you're coding along with this example, be sure to create and enable
   the ``AcmeStoreBundle``. Run the following command and follow the on-screen
   directions:
   
   .. code-block:: text
   
       php app/console init:bundle "Acme\StoreBundle" src/

This type of class is commonly called a "plain-old-PHP-object" (POPO) because,
so far, it has nothing to do with Symfony or any other library. It's quite
simply a normal PHP object that directly solves a problem inside *your* application
(i.e. the need to represent a product in your application). Of course, by
the end of this chapter, you'll be able to submit data to a ``Product`` instance
(via) a form, validate its data, and persist it to a database.

So far, you've not actually done any work related to "forms" - you've simply
created a PHP class that will help you solve a problem. The goal of the form
in this chapter will be to allows your users to interact with the data of
a ``Product`` object.

Building the Form
~~~~~~~~~~~~~~~~~

Now that you've created a ``Product`` class, the next step is to create and
render the actual HTML form. In Symfony2, this is done by building a form
object and then rendering it in a template. This can all be done from inside
a controller:

.. code-block:: php

    // src/Acme/StoreBundle/Controller/DefaultController.php
    namespace Acme\StoreBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Acme\StoreBundle\Entity\Product;

    class DefaultController extends Controller
    {
        public function indexAction()
        {
            $form = $this->get('form.factory')
                ->createBuilder('form')
                ->add('name', 'text')
                ->add('price', 'money', array('currency' => 'USD'))
                ->getForm();

            // create a product and give it some dummy data for this example
            $product = new Product();
            $product->name = 'Test product';
            $product->setPrice('50.00');
            $form->setData($product);

            return $this->render('AcmeStoreBundle:Default:index.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

.. tip::

   If you're ultimately going to persist the ``Product`` object to Doctrine,
   there is an even shorter way to configure the fields of the form. This
   is covered later in the :ref:`book-forms-doctrine-field-guessing`.

Creating a form is short and easy in Symfony2 because form objects are created
via a "form builder". A form builder is an object you can interact with to
help you easily create form objects.

In this example, you've added two fields to your form - ``name`` and ``price`` -
corresponding to the ``name`` and ``price`` properties of the ``Product`` class.
The ``name`` field has a type of ``text``, meaning the user will submit simple
text for this field. The ``price`` field has the type ``money``, which is
special ``text`` field where money can be displayed and submitted in a localized
format. Symfony2 comes with many build-in types that will be discussed shortly
(see :ref:`book-forms-type-reference`).

Now that the form has been created, the next step is to render it in a template.
To render the form, you should pass a special form "view" object to your
template (see the ``$form->createView()`` in the controller above). This
view object knows all about how to render your form:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/StoreBundle/Resources/views/Default/index.html.twig #}
        
        <form action="{{ path('store_product') }}" method="post">
            {{ form_widget(form) }}
            
            <input type="submit" />
        </form>

    .. code-block:: html+php
    
        <?php // src/Acme/StoreBundle/Resources/views/Default/index.html.php ?>
        
        <form action="<?php echo $view['router']->generate('store_product') ?>" method="post">
            <?php echo $view['form']->widget($form) ?>

            <input type="submit" />
        </form>

.. image:: /book/images/forms-simple.png
    :align: center

That's it! By printing ``form_widget(form)``, each field in the form is rendered,
along with a label and any error messages for each field. As easy as this is,
it's not very flexible (yet). Later, you'll learn how to customize the form
output.

Before moving on, notice how the rendered name input field has the value
of the ``name`` property from the ``$product`` object (i.e. "Test product").
This is the first job of a form: to take data from an object and translate
it into a format that's suitable for being rendered in an HTML form.

.. tip::

   The form system is smart enough to access the value of the protected ``price``
   property via the ``getPrice`` and ``setPrice`` methods on ``Product``.
   If a "getter" or "setter" method is available for a property, the form
   component will use the methods instead of accessing the property directly.
   Of course, getter and setter methods are required if a property is protected
   or private.

Handling Form Submissions
~~~~~~~~~~~~~~~~~~~~~~~~~

The second job of a form is to translate user-submitted data back to the
properties of an object. To make this happen, the submitted data from the
user must be bound to the form. Add the following functionality to your
controller:

.. code-block:: php

    public function indexAction()
    {
        // ...

        $product = new Product();
        $form->setData($product);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                // perform some action, such as save the object to the database

                return $this->redirect($this->generateUrl('store_product_success'));
            }
        }
        
        // ...
    }

Now, when submitting the form, the controller binds the submitted data to
the form, which translates that data back to the ``name`` and ``price``
properties of the ``$product`` object. This all happens via the ``bindRequest()``
method.

This controller follows a common pattern for handling forms, and has three
possible paths:

#. When initially loading the form in a browser, the request method is ``GET``,
   meaning that the form is simply created and rendered (but not bound);

#. When the user submits the form, but that submitted data is invalid (validation
   is covered in the next section), the form is bound and then rendered,
   this time displaying all validation errors;

#. When the user submits the form with valid data, the form is bound and
   you have the opportunity to perform some action using the ``$product``
   object (e.g. persisting it to the database) before redirecting the user
   to some other page (e.g. a "thank you" or "success" page).

.. note::

   Redirecting a user after a successful form submission prevents the user
   from being able to hit "refresh" and re-post the data.

.. index::
   single: Forms; Validation

Form Validation
---------------

In the previous section, you learned how a form can be submitted with valid
or invalid data. In Symfony2, validation is applied to the underlying object
(e.g. ``Product``). In other words, the question isn't whether the "form"
is valid, but rather whether or not the ``$product`` object is valid after
the form has applied the submitted data to it. Calling ``$form->isValid()``
is a shortcut that asks the ``$product`` object whether or not it has valid
data.

Validation is done by adding a set of rules (called constraints) to a class.
To see this in action, add validation constraints so that the ``name`` field
cannot be empty and the ``price`` field can't be empty or a non-negative
number:

.. configuration-block::

    .. code-block:: yaml

        # Acme/DemoBundle/Resources/config/validation.yml
        Acme\DemoBundle\Entity\Product:
            properties:
                name:
                    - NotBlank: ~
                price:
                    - NotBlank: ~
                    - Min: 0

    .. code-block:: xml

        <!-- Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\DemoBundle\Entity\Product">
            <property name="name">
                <constraint name="NotBlank" />
            </property>
            <property name="price">
                <constraint name="Min">
                    <value>0</value>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // Acme/StoreBundle/Entity/Product.php
        class Product
        {
            /**
             * @assert:NotBlank()
             */
            public $name;

            /**
             * @assert:NotBlank()
             * @assert:Min(0)
             */
            protected $price;
        }

    .. code-block:: php

        // Acme/StoreBundle/Entity/Product.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\Min;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new NotBlank());
                
                $metadata->addPropertyConstraint('price', new NotBlank());
                $metadata->addPropertyConstraint('price', new Min(0));
            }
        }

That's it! If you re-submit the form with invalid data, you'll see the corresponding
errors printed out with the form.

.. tip::

   If you're 

Validation is a very powerful feature of Symfony2 and has its own
:doc:`dedicated chapter</book/validation>`.

.. index::
   single: Forms; Built-in Field Types

.. _book-forms-type-reference:

Built-in Field Types
--------------------

Symfony comes standard with a large group of field types that cover all of
the common form fields and data types you'll encounter:

.. include:: /reference/forms/types/map.rst.inc

Of course, you can also create your own custom field types. This topic is
covered in the ":doc:`/cookbook/forms/create_custom_fields`" article of the
cookbook.

.. index::
   single: Forms; Field type options

Common Field Type Options
~~~~~~~~~~~~~~~~~~~~~~~~~

You may have noticed that the ``price`` field has been passed an array of
options:

.. code-block:: php

    ->add('price', 'money', array('currency' => 'USD'))

All fields have a number of different options. Many of them are specific
to the field type and details can be found in the documentation for those
types. Some options, however, are shared between most fields:

* ``required`` - The ``required`` option can be used to render an
  `HTML5 required attribute`_. Note that this is independent from validation:
  if you specify the required attribute on the field type but omit any required
  validation, the object will appear to be valid with a blank value. In other
  words, this is a *nice* feature that will add client-side validation for
  browsers that support HTML5. It's not, however, a replacement for true
  server-side validation.

* ``max_length`` - This option is used to add a ``max_length`` attribute,
  which is used by some browsers to limit the amount of text in a field.

.. index::
   single: Forms; Field type guessing

Field Type Guessing
-------------------

Now that you've added validation metadata to the ``Product`` class, Symfony
already knows a bit about your fields. If you allow it, Symfony can "guess"
the type of your field and set it up for you. In this example, Symfony will
guess from the validation that both the ``name`` and ``price`` fields are
normal ``text`` fields. Since it's right about the ``name`` field, you can
modify your code so that Symfony guesses the field for you:

.. code-block:: php

    public function indexAction()
    {
        $form = $this->get('form.factory')
            ->createBuilder('form', 'product', array(
                'data_class' => 'Acme\StoreBundle\Entity\Product',
            ))
            ->add('name')
            ->add('price', 'money', array('currency' => 'USD'))
            ->getForm();
    }

You'll notice two differences immediately. First, a ``data_class`` option
is passed when creating the form. This tells Symfony which class to look
at when guessing the fields. This is required to take advantage of field
guessing. You can now omit the ``text`` type for the ``name`` field as this
field is correctly guessed. The ``money`` type was kept, however, for the
``price`` field as it's more specific than what the system could guess (``text``).

.. note::

    The ``createBuilder()`` method takes up to three arguments (but only
    the first is required):
    
     * the string ``form`` (meaning you're building a form);
     
     * a name for the form (e.g. ``product``), which impacts how the names
       of the fields are rendered (e.g. `<input type="text" name="product[name]" />`);
     
     * an array of options for the form.

This example is pretty trivial, but field guessing can be a major time saver.
As you'll see later, adding Doctrine metadata can further improve the system's
ability to guess field types.

.. index::
   single: Forms; Rendering in a Template

Rendering a Form in a Template
------------------------------

So far, you've seen how an entire field can be rendered with just one line
of code. Of course, you'll usually need much more flexibility when rendering:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/StoreBundle/Resources/views/Default/index.html.twig #}
        
        <form action="{{ path('store_product') }}" method="post">
            {{ form_errors(form) }}

            {{ form_row(form.name) }}
            {{ form_row(form.price) }}

            {{ form_rest(form) }}

            <input type="submit" />
        </form>

    .. code-block:: html+php
    
        <?php // src/Acme/StoreBundle/Resources/views/Default/index.html.php ?>
        
        <form action="<?php echo $view['router']->generate('store_product') ?>" method="post" <?php echo $view['form']->enctype($form) ?>>
            <?php echo $view['form']->errors($form) ?>

            <?php echo $view['form']->row($form['name']) ?>
            <?php echo $view['form']->row($form['price']) ?>

            <?php echo $view['form']->rest($form) ?>

            <input type="submit" />
        </form>

Let's take a look at each part:

* ``form_enctype(form)`` - If at least on field is a file upload field, this
  renders the obligatory ``enctype="multipart/form-data"``;

* ``form_errors(form)`` - This will render any errors global to the whole form
  (field-specific errors are displayed next to each field);

* ``form_row(form.price)`` - Renders the label, any errors, and the HTML
  form widget for the given field (e.g. ``price``);

* ``form_rest(form)`` - This renders any fields that have not yet been rendered
  and is usually a good idea to place at the bottom of each form (in case
  you forgot to output a field or don't want to bother manually rendering
  hidden fields).

The majority of the work is done by the ``form_row`` helper, which renders
the label, errors and HTML form widget of each field inside a ``div`` tag
by default. In the :ref:`forms-customize-blocks` section, you'll learn how
the ``form_row`` output can be customized on many different levels.

Rendering each Field by Hand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``form_row`` helper is great because you can very quickly render your
form and even customize the markup used. But since life isn't always so
simple, you can also render each field entirely by hand:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form_errors(form) }}

        <div>
            {{ form_label(form.name) }}
            {{ form_errors(form.name) }}
            {{ form_widget(form.name) }}
        </div>

        <div>
            {{ form_label(form.price) }}
            {{ form_errors(form.price) }}
            {{ form_widget(form.price) }}
        </div>

        {{ form_rest(form) }}

    .. code-block:: html+php

        <?php echo $view['form']->errors($form) ?>

        <div>
            <?php echo $view['form']->label($form['name']) ?>
            <?php echo $view['form']->errors($form['name']) ?>
            <?php echo $view['form']->widget($form['name']) ?>
        </div>

        <div>
            <?php echo $view['form']->label($form['price']) ?>
            <?php echo $view['form']->errors($form['price']) ?>
            <?php echo $view['form']->widget($form['price']) ?>
        </div>

        <?php echo $view['form']->rest($form) ?>

If the auto-generated label for a field isn't quite right, you can specify
the label manually:

You can also explicitly set the label for a field:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form_label(form.name, 'Product name') }}

    .. code-block:: html+php

        <?php echo $view['form']->label($form['name'], 'Product name') ?>

Finally, some field types have additional rendering options that can be passed
to the widget. These options are documented with each type, but one common
options is ``attr``, which allows you to modify attributes on the form element.
The following would add the ``name_field`` class to the rendered input text
field:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form_widget(form.name, { 'attr': {'class': 'name_field'} }) }}

    .. code-block:: html+php

        <?php echo $view['form']->widget($form['name'], array(
            'attr' => array('class' => 'name_field'),
        )) ?>

.. index::
   single: Forms; Creating form classes

Creating Form Classes
---------------------

As you've seen, a form can be created and used directly in a controller. However,
a better practice is to build the form in a separate, standalone PHP class,
which can then be reused anywhere in your application. Create a new class
that will house the logic for building the product form:

.. code-block:: php

    // src/Acme/StoreBundle/Form/ProductType.php

    namespace Acme\StoreBundle\Form;

    use Symfony\Component\Form\Type\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('name');
            $builder->add('price', 'money', array('currency' => 'USD'));
        }

        public function getDefaultOptions(array $options)
        {
            return array(
                'data_class' => 'Acme\StoreBundle\Entity\Product',
            );
        }
    }

This new class contains all the directions needed to create the product form.
It can be used to quickly build a form object in the controller:

.. code-block:: php

    // src/Acme/StoreBundle/Controller/DefaultController.php

    // add this new use statement at the top of the class
    use Acme\StoreBundle\Form\ProductType;

    public function indexAction()
    {
        $form = $this->get('form.factory')->create(new ProductType());
        
        // ...
    }

Placing the form logic into its own class means that the class can be easily
reused and is properly isolated out of the controller. This is the best way
to create forms, but the choice is ultimately up to you.

.. index::
   single: Forms; Doctrine

Forms and Doctrine
------------------

Commonly, you'll create forms for data that ultimately needs to be persisted
to a database. To make the ``Product`` example more robust, configure it
to be persisted to a database by the Doctrine ORM:



.. index::
   single: Forms; Embedded forms

Embedded Forms
--------------

.. index::
   single: Forms; Customizing rendering

Customizing Form Rendering
--------------------------

.. index::
   single: Forms; CSRF Protection

CSRF Protection
---------------

Final Thoughts
--------------


.. _`Symfony2 Form Component`: https://github.com/symfony/Form
.. _`HTML5 required attribute`: http://diveintohtml5.org/forms.html