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
so far, it has nothing to do with Symfony and it doesn't persist to the
database (i.e. it's not tied to Doctrine). It's quite simply a normal PHP
object that directly solves a problem inside *your* application (i.e. the
need to represent a product in your application). Of course, by the end of
this chapter, you'll be able to submit data to a ``Product`` instance (via)
a form, validate its data, and persist it to a database.

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
                ->add('price', 'money')
                ->getForm();

            // create a product and give it some dummy data for this example
            $product = new Product();
            $product->name = 'Test product';
            $product->setPrice('50.00');
            $form->setData($product);

            return $this->render('AcmeStoreBundle:Default:index.html.twig', array(
                'form' => $this->get('form.factory')->createRenderer($form, 'twig'),
            ));
        }
    }

.. tip::

   If you're ultimately going to persist the ``Product`` object to Doctrine,
   there is an even shorter way to configure the fields of the form. This
   is covered later in the :ref:`book-forms-doctrine-field-guessing`.

Creating a form is short and easy in Symfony2 because form objects are created
via a "form builder". A form builder is simply an object you can interact
with to succinctly create form objects.

In this example, you've created two fields on your form - ``name`` and ``price`` -
corresponding to the ``name`` and ``price`` fields on the ``Product`` class.
The ``name`` field has a type of ``text``, meaning the user will submit simple
text for this field. The ``price`` field has the type ``money``, which is
special ``text`` field where money can be displayed and submitted in a localized
format. Symfony2 comes with many build-in types that will be discussed shortly
(see :ref:`book-forms-type-reference`).

.. tip::

   Notice that the form system is smart enough to access the ``price`` field
   via the ``getPrice`` and ``setPrice`` methods. You have the option of
   making any property either public or adding getter and setter methods
   for it.

Now that the form has been created, the next step is to render it in a template.
To render the form, you should pass a special form "renderer" to your template.
As you'll see, this is a special object that contains many helpful methods
for rendering your form:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/StoreBundle/Resources/views/Default/index.html.twig #}
        
        <form action="{{ path('store_product') }}" method="post">
            {{ form.widget }}
            
            <input type="submit" />
        </form>

    .. code-block:: html+php
    
        <?php // src/Acme/StoreBundle/Resources/views/Default/index.html.php ?>
        
        <form action="<?php echo $view['router']->generate('store_product') ?>" method="post">
            <?php echo $form->getWidget() ?>

            <input type="submit" />
        </form>

.. image:: /book/images/forms-simple.png
    :align: center

That's it! By printing ``form.widget``, each field in the form is rendered,
along with a label and any error messages for the field. As easy as this is,
it's not very flexible (yet). Later, you'll learn how to customize the form
output.

Before moving on, notice how the rendered name input field has the value
of the ``name`` property from the ``$product`` object (i.e. "Test product").
This is the first job of a form: to take data from an object and translate
it into a format that's suitable for being rendered in an HTML form.

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

The controller follows a common pattern for handling forms, and has three
possible paths:

#. When loading the URL in a browser, the request method is ``GET``, meaning
   that the form is simply created and rendered (but not bound);

#. When the user submits the form, but that submitted data is invalid (validation
   is covered in the next section), the form is bound and then rendered,
   this time displaying all validation errors;

#. When the user submits the form with valid data, the form is bound and
   you have the opportunity to perform some action using the ``$product``
   object (e.g. persisting it to the database) before redirecting the user
   to some other page (e.g. a "thank you" or "success" page).

.. note::

   Redirecting a user after a successful form submit prevents the user from
   being able to hit "refresh" and re-post the data.

.. index::
   single: Forms; Validation

Form Validation
---------------

In the previous section, you learned how a form can be submitted with valid
or invalid data. In Symfony2, validation is applied to the underlying object
(e.g. ``Product``). In other words, the question isn't whether the "form"
is valid, but rather whether or not the ``$product`` object is valid after
the form has applied the submitted data to it. Calling ``$form->isValid()``
is just a shortcut that asks the ``$product`` object whether or not it has
valid data.

Validation is done by adding a set of rules (called constraints) to a class.
To see this in action, add validation constraints so that the ``name`` field
cannot be empty and the ``price`` field can't be empty and must be a non-negative
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
             * @validation:NotBlank()
             */
            public $name;

            /**
             * @validation:NotBlank()
             * @validation:Min(0)
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

Validation is a very powerful feature of Symfony2 and has its own
:doc:`dedicated chapter</book/validation>`.

.. index::
   single: Forms; Built-in Field Types

Built-in Field Types
--------------------

Symfony comes standard with a large group of field types that cover all of
the common form fields and data types you'll encounter:

.. include:: /reference/forms/types/map.rst.inc

.. index::
   single: Forms; Rendering in a Template

Rendering a Form in a Template
------------------------------

So far, you've seen how an entire field can be rendered with just one line
of code. Of course, you'll usually need much more flexibility when rendering:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/StoreBundle/Resources/views/Default/index.html.twig #}
        
        <form action="{{ path('store_product') }}" method="post" {{ form.enctype }}>
            {{ form.errors }}
            
            {% for field in form %}
                <div>
                    {{ field.label }}
                    {{ field.errors }}
                    {{ field.widget }}
                </div>
            {% endfor %}
            
            {{ form.rest }}
            
            <input type="submit" />
        </form>

    .. code-block:: html+php
    
        <?php // src/Acme/StoreBundle/Resources/views/Default/index.html.php ?>
        
        <form action="<?php echo $view['router']->generate('store_product') ?>" method="post" <?php echo $form->getEnctype() ?>>
            <?php echo $form->getErrors() ?>

            <?php foreach ($form as $field): ?>
                <div>
                    <?php echo $field->getLabel() ?>
                    <?php echo $field->getErrors() ?>
                    <?php echo $field->getWidget() ?>
                </div>
            <?php endforeach; ?>

            <?php echo $form->getRest() ?>

            <input type="submit" />
        </form>

Let's take a look at each part:

* ``form.enctype`` - If at least on field is a file upload field, this will
  render the obligatory ``enctype="multipart/form-data"``;

* ``form.errors`` - This will render any errors global to the whole form
  (field-specific errors are displayed next to each field);

* ``form.rest`` - This renders any fields that have not yet been rendered
  and is usually a good idea to place at the bottom of each form (in case
  you forgot to output a field).

By looping through each field (via ``{% for field in form %}`` in Twig),
you can customize the exact output of each field:

* ``field.label`` - Renders the label of the field, which is a humanized
  version of the field name (e.g. ``first_name`` would be ``First name``)
  but can easily be overridden;

* ``field.errors`` - Renders each error specific to the field;

* ``field.widget`` - Renders the actual form element representing the field.

Rendering Specific Fields
~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of iterating through each field, you'll more commonly output each
field exactly where you want it:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form.errors }}

        <div>
            {{ form.name.label }}
            {{ form.name.errors }}
            {{ form.name.widget }}
        </div>

        <div>
            {{ form.price.label }}
            {{ form.price.errors }}
            {{ form.price.widget }}
        </div>

        {{ form.rest }}

    .. code-block:: html+php

        <?php echo $form->getErrors() ?>

        <div>
            <?php echo $form['name']->getLabel() ?>
            <?php echo $form['name']->getErrors() ?>
            <?php echo $form['name']->getWidget() ?>
        </div>

        <div>
            <?php echo $form['price']->getLabel() ?>
            <?php echo $form['price']->getErrors() ?>
            <?php echo $form['price']->getWidget() ?>
        </div>

        <?php echo $form->getRest() ?>

In many cases, this can be simplified even further by rendering the entire
"row" for a field at once:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form.errors }}

        {{ form.name.row }}
        {{ form.price.row }}

        {{ form.rest }}

    .. code-block:: html+php

        <?php echo $form->getErrors() ?>

        <?php echo $form['name']->getRow() ?>
        <?php echo $form['price']->getRow() ?>

        <?php echo $form->getRest() ?>

When rendering the row for a field, the label, errors and form widget are
all output and wrapped - by default - in a ``div`` HTML tag. As with *everything*
related to form rendering, the way a "row" is rendered can be completely
customized. See :ref:`book-forms-overriding-template-blocks` for more information.

.. tip::
   One advantage of rendering a field row is that the row can be customized
   on a field type-by-field type basis. For example, when a ``hidden`` field
   type row is rendered, the label is not rendered (because it doesn't make
   sense to put a label in front of a hidden field).

.. index::
   single: Forms; Creating form classes

Creating Form Classes
---------------------

As you've seen, forms can be created and used directly in a controller. A
more robust option is to build a form in a standalone PHP class, which can
then be used inside a controller or anywhere else in your application. Create
a new class that will house the logic for building the product form:

.. code-block:: php

    // src/Acme/StoreBundle/Form/ProductType.php

    namespace Acme\StoreBundle\Form;

    use Symfony\Component\Form\Type\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('name', 'text');
            $builder->add('price', 'money');
        }
    }

This new class represents the product form, and can now be used to build a
form object in the controller:

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