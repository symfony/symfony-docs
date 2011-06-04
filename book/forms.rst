.. index::
   single: Forms

Forms
=====

Dealing with HTML forms is one of the most common - and challenging - tasks for
a web developer. Symfony2 integrates a Form component that makes dealing with
forms easy. In this chapter, you'll build a complex form from the ground-up,
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

This type of class is commonly called a "plain-old-PHP-object" because, so far,
it has nothing to do with Symfony or any other library. It's quite simply a
normal PHP object that directly solves a problem inside *your* application (i.e.
the need to represent a product in your application). Of course, by the end of
this chapter, you'll be able to submit data to a ``Product`` instance (via a
form), validate its data, and persist it to a database.

So far, you haven't actually done any work related to "forms" - you've simply
created a PHP class that will help you solve a problem in *your* application.
The goal of the form built in this chapter will be to allow your users to
interact with the data of a ``Product`` object.

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
            // create a product and give it some dummy data for this example
            $product = new Product();
            $product->name = 'Test product';
            $product->setPrice('50.00');

            $form = $this->createFormBuilder($product)
                ->add('name', 'text')
                ->add('price', 'money', array('currency' => 'USD'))
                ->getForm();

            return $this->render('AcmeStoreBundle:Default:index.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

.. tip::

   Later, when you add validation to the ``Product`` object, you'll learn
   that there is an even shorter way to configure the fields of the form. This
   is covered later in the :ref:`book-forms-field-guessing` section.

Creating a form is short and easy in Symfony2 because form objects are built
via a "form builder". A form builder is an object you can interact with to
help you easily create form objects.

In this example, you've added two fields to your form - ``name`` and ``price`` -
corresponding to the ``name`` and ``price`` properties of the ``Product`` class.
The ``name`` field has a type of ``text``, meaning the user will submit simple
text for this field. The ``price`` field has the type ``money``, which is
special ``text`` field where money can be displayed and submitted in a localized
format. Symfony2 comes with many build-in types that will be discussed shortly
(see :ref:`book-forms-type-reference`).

Now that the form has been created, the next step is to render it. This can
be easily done by passing a special form "view" object to your template (see
the ``$form->createView()`` in the controller above) and using a set of form
helper functions:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/StoreBundle/Resources/views/Default/index.html.twig #}
        
        <form action="{{ path('store_product') }}" method="post" {{ form_enctype(form) }}>
            {{ form_widget(form) }}
            
            <input type="submit" />
        </form>

    .. code-block:: html+php
    
        <?php // src/Acme/StoreBundle/Resources/views/Default/index.html.php ?>
        
        <form action="<?php echo $view['router']->generate('store_product') ?>" method="post" <?php echo $view['form']->enctype($form) ?> >
            <?php echo $view['form']->widget($form) ?>

            <input type="submit" />
        </form>

.. image:: /book/images/forms-simple.png
    :align: center

That's it! By printing ``form_widget(form)``, each field in the form is
rendered, along with a label and eventual error messages. As easy as this is,
it's not very flexible (yet). Later, you'll learn how to customize the form
output.

Before moving on, notice how the rendered name input field has the value
of the ``name`` property from the ``$product`` object (i.e. "Test product").
This is the first job of a form: to take data from an object and translate
it into a format that's suitable for being rendered in an HTML form.

.. tip::

   The form system is smart enough to access the value of the protected
   ``price`` property via the ``getPrice()`` and ``setPrice()`` methods on the
   ``Product`` class. Unless a property is public, it *must* have a "getter" and
   "setter" method so that the form component can get and put data onto the
   property. For a Boolean property, you can use an "isser" method (e.g.
   ``isPublished()``) instead of a getter (e.g. ``getPublished()``).

Handling Form Submissions
~~~~~~~~~~~~~~~~~~~~~~~~~

The second job of a form is to translate user-submitted data back to the
properties of an object. To make this happen, the submitted data from the
user must be bound to the form. Add the following functionality to your
controller:

.. code-block:: php

    public function indexAction()
    {
        // just setup a fresh $product object (no dummy data)
        $product = new Product();
        
        $form = $this->createFormBuilder($product)
            ->add('name', 'text')
            ->add('price', 'money', array('currency' => 'USD'))
            ->getForm();

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

Now, when submitting the form, the controller binds the submitted data to the
form, which translates that data back to the ``name`` and ``price`` properties
of the ``$product`` object. This all happens via the ``bindRequest()`` method.

.. note::

    As soon as ``bindRequest()`` is called, the submitted data is transferred
    to the underlying object immediately. For example, imagine that ``Foo``
    is submitted for the ``name`` field:

    .. code-block:: php

        $product = new Product();
        $product->name = 'Test product';
    
        $form->bindRequest($this->get('request'));
        echo $product->name;

    The above statement will echo ``Foo``, because ``bindRequest`` ultimately
    moves the submitted data back to the ``$product`` object.

This controller follows a common pattern for handling forms, and has three
possible paths:

#. When initially loading the form in a browser, the request method is ``GET``,
   meaning that the form is simply created and rendered (but not bound);

#. When the user submits the form (i.e. the method is ``POST``), but that
   submitted data is invalid (validation is covered in the next section),
   the form is bound and then rendered, this time displaying all validation
   errors;

#. When the user submits the form with valid data, the form is bound and
   you have the opportunity to perform some actions using the ``$product``
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

Validation is done by adding a set of rules (called constraints) to a class. To
see this in action, add validation constraints so that the ``name`` field cannot
be empty and the ``price`` field cannot be empty and must be a non-negative
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
        use Symfony\Component\Validator\Constraints as Assert;

        class Product
        {
            /**
             * @Assert\NotBlank()
             */
            public $name;

            /**
             * @Assert\NotBlank()
             * @Assert\Min(0)
             */
            protected $price;
        }

    .. code-block:: php

        // Acme/StoreBundle/Entity/Product.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\Min;

        class Product
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new NotBlank());
                
                $metadata->addPropertyConstraint('price', new NotBlank());
                $metadata->addPropertyConstraint('price', new Min(0));
            }
        }

That's it! If you re-submit the form with invalid data, you'll see the
corresponding errors printed out with the form.

.. note::

   If you have a look at the generated HTML code, the Form component generates
   new HTML5 fields including a special "required" attribute to enforce some
   validation directly via the web browser. Some of modern web browsers like
   Firefox 4, Chrome 3.0 or Opera 9.5 understand this special "required"
   attribute.

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
covered in the ":doc:`/cookbook/forms/create_custom_field_type`" article
of the cookbook.

.. index::
   single: Forms; Field type options

Common Field Type Options
~~~~~~~~~~~~~~~~~~~~~~~~~

You may have noticed that the ``price`` field has been passed an array of
options:

.. code-block:: php

    ->add('price', 'money', array('currency' => 'USD'))

Each field type has a number of different options that can be passed to it.
Many of these are specific to the field type and details can be found in
the documentation for each type. Some options, however, are shared between
most fields:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/max_length.rst.inc

.. index::
   single: Forms; Field type guessing

.. _book-forms-field-guessing:

Field Type Guessing
-------------------

Now that you've added validation metadata to the ``Product`` class, Symfony
already knows a bit about your fields. If you allow it, Symfony can "guess"
the type of your field and set it up for you. In this example, Symfony will
guess from the validation rules that both the ``name`` and ``price`` fields
are normal ``text`` fields. Since it's right about the ``name`` field, you
can modify your code so that Symfony guesses the field for you:

.. code-block:: php

    public function indexAction()
    {
        $product = new Product();

        $form = $this->createFormBuilder($product)
            ->add('name')
            ->add('price', 'money', array('currency' => 'USD'))
            ->getForm();
    }

The ``text`` type for the ``name`` field has now been omitted since it's
correctly guessed from the validation rules. However, the ``money`` type for the
``price`` field was kept, since it's more specific than what the system could
guess (``text``).

.. note::

    The ``createBuilder()`` method takes up to three arguments (but only
    the first is required):
    
     * the string ``form`` stands for the what you're building (a form) and
       is also used as the name of the form. If you look at the generated
       code, the two fields are named ``name="form[price]"`` and ``name="form[name]"``;
     
     * The default data to initialize the form fields. This argument can be an
       associative array or a plain old PHP object like in this example;
     
     * an array of options for the form.

This example is pretty trivial, but field guessing can be a major time saver.
As you'll see later, adding Doctrine metadata can further improve the system's
ability to guess field types.

.. index::
   single: Forms; Rendering in a Template

.. _form-rendering-template:

Rendering a Form in a Template
------------------------------

So far, you've seen how an entire form can be rendered with just one line
of code. Of course, you'll usually need much more flexibility when rendering:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/StoreBundle/Resources/views/Default/index.html.twig #}
        
        <form action="{{ path('store_product') }}" method="post" {{ form_enctype(form) }}>
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

* ``form_enctype(form)`` - If at least one field is a file upload field, this
  renders the obligatory ``enctype="multipart/form-data"``;

* ``form_errors(form)`` - Renders any errors global to the whole form
  (field-specific errors are displayed next to each field);

* ``form_row(form.price)`` - Renders the label, any errors, and the HTML
  form widget for the given field (e.g. ``price``);

* ``form_rest(form)`` - Renders any fields that have not yet been rendered.
  It's usually a good idea to place a call to this helper at the bottom of
  each form (in case you forgot to output a field or don't want to bother
  manually rendering hidden fields). This helper is also useful for taking
  advantage of the automatic :ref:`CSRF Protection<forms-csrf>`.

The majority of the work is done by the ``form_row`` helper, which renders
the label, errors and HTML form widget of each field inside a ``div`` tag
by default. In the :ref:`form-theming` section, you'll learn how
the ``form_row`` output can be customized on many different levels.

.. tip::

    As of HTML5, user agents can interactively validate the form
    "constraints". Generated forms take full advantage of this new feature by
    adding sensible HTML attributes. It can however be disabled by using the
    ``novalidate`` attribute on the ``form`` tag or ``formnovalidate`` on the
    submit tag.

Rendering each Field by Hand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``form_row`` helper is great because you can very quickly render each
field of your form (and the markup used for the "row" can be customized as
well). But since life isn't always so simple, you can also render each field
entirely by hand:

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

If the auto-generated label for a field isn't quite right, you can explicitly
specify it:

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

Twig Template Function Reference
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're using Twig, a full reference of the form rendering functions is
available in the :doc:`reference manual</reference/forms/twig_reference>`.

.. index::
   single: Forms; Creating form classes

Creating Form Classes
---------------------

As you've seen, a form can be created and used directly in a controller.
However, a better practice is to build the form in a separate, standalone PHP
class, which can then be reused anywhere in your application. Create a new class
that will house the logic for building the product form:

.. code-block:: php

    // src/Acme/StoreBundle/Form/ProductType.php

    namespace Acme\StoreBundle\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('name');
            $builder->add('price', 'money', array('currency' => 'USD'));
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
        $product = // ...
        $form = $this->createForm(new ProductType(), $product);
        
        // ...
    }

.. note::
    You can also set the data on the form via the ``setData()`` method:
    
    .. code-block:: php
    
        $form = $this->createForm(new ProductType());
        $form->setData($product);

    If you use the ``setData`` method - and want to take advantage of field
    type guessing, be sure to add the following to your form class:
    
    .. code-block:: php
    
        public function getDefaultOptions(array $options)
        {
            return array(
                'data_class' => 'Acme\StoreBundle\Entity\Product',
            );
        }
    
    This is necessary because the object is passed to the form after field
    type guessing.

Placing the form logic into its own class means that the form can be easily
reused elsewhere in your project. This is the best way to create forms, but
the choice is ultimately up to you.

.. index::
   single: Forms; Doctrine

Forms and Doctrine
------------------

The goal of a form is to translate data from an object (e.g. ``Product``) to an
HTML form and then translate user-submitted data back to the original object. As
such, the topic of persisting the ``Product`` object to the database is entirely
unrelated to the topic of forms. If you've configured the ``Product`` class to
be persisted by Doctrine, then persisting it after a form submission can be done
when the form is valid:

.. code-block:: php

    if ($form->isValid()) {
        $em = $this->get('doctrine')->getEntityManager();
        $em->persist($product);
        $em->flush();

        return $this->redirect($this->generateUrl('store_product_success'));
    }

If, for some reason, you don't have access to your original ``$product``
object, you can fetch it from the form:

.. code-block:: php

    $product = $form->getData();

For more information, see the :doc:`Doctrine ORM chapter</book/doctrine/orm>`.

The key thing to understand is that when the form is bound, the submitted
data is transferred to the underlying object immediately. If you want to
persist that data, you simply need to persist the object itself (which already
contains the submitted data).

If the underlying object of a form (e.g. ``Product``) happens to be mapped
with the Doctrine ORM, the form framework will use that information - along
with the validation metadata - to guess the type of a particular field.

.. index::
   single: Forms; Embedded forms

Embedded Forms
--------------

Often, you'll want to build a form that will include fields from many different
objects. For example, a registration form may contain data belonging to
a ``User`` object as well as many ``Address`` objects. Fortunately, this
is easy and natural with the form component.

Embedding a Single Object
~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose that each ``Product`` belongs to a simple ``Category`` object:

.. code-block:: php

    // src/Acme/StoreBundle/Entity/Category.php
    namespace Acme\StoreBundle\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Category
    {
        /**
         * @Assert\NotBlank()
         */
        public $name;
    }

The ``Product`` class has a new ``$category`` property, indicating to which
``Category`` it belongs:

.. code-block:: php

    use Symfony\Component\Validator\Constraints as Assert;

    class Product
    {
        // ...

        /**
         * @Assert\Type(type="Acme\StoreBundle\Entity\Category")
         */
        protected $category;

        // ...

        public function getCategory()
        {
            return $this->category;
        }

        public function setCategory(Category $category)
        {
            $this->category = $category;
        }
    }

Now that your application has been updated to reflect the new requirements,
create a form class so that a ``Category`` object can be modified by the user:

.. code-block:: php

    // src/Acme/StoreBundle/Form/CategoryType.php
    namespace Acme\StoreBundle\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class CategoryType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('name');
        }

        public function getDefaultOptions(array $options)
        {
            return array(
                'data_class' => 'Acme\StoreBundle\Entity\Category',
            );
        }
    }

The type of the ``name`` field is being guessed (as a ``text`` field) from
the validation metadata of the ``Category`` object.

The end goal is to allow the ``Category`` of a ``Product`` to be modified right
inside the product form. To accomplish this, add a ``category`` field to the
``ProductType`` object whose type is an instance of the new ``CategoryType``
class:

.. code-block:: php

    public function buildForm(FormBuilder $builder, array $options)
    {
        // ...

        $builder->add('category', new CategoryType());
    }

The fields from ``CategoryType`` can now be rendered alongside those from
the ``ProductType`` class. Render the ``Category`` fields in the same way
as the original ``Product`` fields:

.. configuration-block::

    .. code-block:: html+jinja

        {# ... #}
        {{ form_row(form.price) }}

        <h3>Category</h3>
        <div class="category">
            {{ form_row(form.category.name) }}
        </div>

        {{ form_rest(form) }}
        {# ... #}

    .. code-block:: html+php

        <!-- ... -->
        <?php echo $view['form']->row($form['price']) ?>

        <h3>Category</h3>
        <div class="category">
            <?php echo $view['form']->row($form['category']['name']) ?>
        </div>

        <?php echo $view['form']->rest($form) ?>
        <!-- ... -->

When the user submits the form, the submitted data for the ``Category`` fields
is merged onto the ``Category`` object. In other words, everything works
exactly as it does with the main ``Product`` object. The ``Category`` instance
is accessible naturally via ``$product->getCategory()`` and can be persisted
to the database or used however you need.

Embedding a Collection of Forms
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also embed a collection of forms into one form. This is done by
using the ``collection`` field type. Assuming that you have a property
called ``reviews`` and a class called ``ProductReviewType``, you could do
the following inside ``ProductType``:

.. code-block:: php

    public function buildForm(FormBuilder $builder, array $options)
    {
        // ...

        $builder->add('reviews', 'collection', array(
           'type'       => new ProductReviewType(),
        ));
    }

.. _form-theming:

Form Theming
------------

Every part of how a form renders can be customized. You're free to change
how each form "row" renders, change the markup used to render errors, or
even customize how a textarea tag should be rendered. Nothing is off-limits,
and different customizations can be used in different places.

Symfony uses templates to render each and every part of a form. In Twig,
the different pieces of a form - a row, a textarea tag, errors - are represented
by Twig "blocks". To customize any part of how a form renders, you just need
to override the appropriate block.

To understand how this works, let's customize the ``form_row`` output and
add a class attribute to the ``div`` element that surrounds each row. To
do this, create a new template file that will store the new markup:

.. configuration-block::

    .. code-block:: html+jinja
    
        {# src/Acme/StoreBundle/Resources/views/Form/fields.html.twig #}
        {% extends 'TwigBundle:Form:div_layout.html.twig' %}
    
        {% block field_row %}
        {% spaceless %}
            <div class="form_row">
                {{ form_label(form) }}
                {{ form_errors(form) }}
                {{ form_widget(form) }}
            </div>
        {% endspaceless %}
        {% endblock field_row %}

    .. code-block:: html+php

        <!-- src/Acme/StoreBundle/Resources/views/Form/field_row.html.php -->
        <div class="form_row">
            <?php echo $view['form']->label($form, $label) ?>
            <?php echo $view['form']->errors($form) ?>
            <?php echo $view['form']->widget($form, $parameters) ?>
        </div>

The ``field_row`` block is the name of the block used when rendering most
fields via the ``form_row`` function. To use the ``field_row`` block defined
in this template, add the following to the top of the template that renders
the form:

.. configuration-block:: php

    .. code-block:: html+jinja

        {# src/Acme/StoreBundle/Resources/views/Default/index.html.twig #}
        {% form_theme form 'AcmeStoreBundle:Form:fields.html.twig' %}
        
        <form ...>

The ``form_theme`` tag "imports" the template and uses all of its form-related
blocks when rendering the form. In other words, when ``form_row`` is called
later in this template, it will use the ``field_row`` block from the
``fields.html.twig`` template.

To customize any portion of a form, you just need to override the appropriate
block. Knowing exactly which block to override is the subject of the next
section.

In the following section, you'll learn more about how to customize different
portions of a form. For a more extensive discussion, see :doc:`/cookbook/form/twig_form_customization`.

.. _form-template-blocks:

Form Template Blocks
~~~~~~~~~~~~~~~~~~~~

Every part of a form that is rendered - HTML form elements, errors, labels, etc
- is defined in a base template as individual Twig blocks. By default, every
block needed is defined in the `div_layout.html.twig`_ file that lives inside
the core ``TwigBundle``. Inside this file, you can see every block needed
to render a form and every default field type.

Each block follows the same basic pattern and is broken up into two pieces,
separated by a single underscore character (``_``). A few examples are:

* ``field_row`` - used by ``form_row`` to render most fields;
* ``textarea_widget`` - used by ``form_widget`` to render a ``textarea`` field
  type;
* ``field_errors`` - used by ``form_errors`` to render errors for a field;

Each block follows the same basic pattern: ``type_part``. The ``type`` portion
corresponds to the field type being rendered (e.g. ``textarea`` or ``checkbox``)
whereas the ``part`` portion corresponds to *what* is being rendered (e.g.
``label``, ``widget``). By default, there are exactly 7 possible parts of
a form that can be rendered:

+-------------+--------------------------+------------------------------------------------------+
| ``label``   | (e.g. ``field_label``)   | renders the field's label                            |
+-------------+--------------------------+------------------------------------------------------+
| ``widget``  | (e.g. ``field_widget``)  | renders the field's HTML representation              |
+-------------+--------------------------+------------------------------------------------------+
| ``errors``  | (e.g. ``field_errors``)  | renders the field's errors                           |
+-------------+--------------------------+------------------------------------------------------+
| ``row``     | (e.g. ``field_row``)     | renders the field's entire row (label+widget+errors) |
+-------------+--------------------------+------------------------------------------------------+
| ``rows``    | (e.g. ``field_rows``)    | renders the child rows of a form                     |
+-------------+--------------------------+------------------------------------------------------+
| ``rest``    | (e.g. ``field_rest``)    | renders the unrendered fields of a form              |
+-------------+--------------------------+------------------------------------------------------+
| ``enctype`` | (e.g. ``field_enctype``) | renders the ``enctype`` attribute of a form          |
+-------------+--------------------------+------------------------------------------------------+

By knowing the field type (e.g. ``textarea``) and which part you want to
customize (e.g. ``widget``), you can construct the block name that needs
to be overridden (e.g. ``textarea_widget``). The best way to customize the
block is to copy it from ``div_layout.html.twig`` to a new template, customize
it, and then use the ``form_theme`` tag as shown in the earlier example.

Form Type Block Inheritance
~~~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases, the block you want to customize will appear to be missing.
For example, if you look in the ``div_layout.html.twig`` file, you'll find
no ``textarea_errors`` block. So how are the errors for a textarea field
rendered?

The answer is: via the ``field_errors`` block. When Symfony renders the errors
for a textarea type, it looks first for a ``textarea_errors`` block before
falling back to the ``field_errors`` block. Each field type has a *parent*
type (the parent type of ``textarea`` is ``field``), and Symfony uses the
block for the parent type if the base block doesn't exist.

So, to override the errors for *only* ``textarea`` fields, copy the
``field_errors`` block, rename it to ``textarea_errors`` and customize it. To
override the default error rendering for *all* fields, copy and customize the
``field_errors`` block directly.

Global Form Theming
~~~~~~~~~~~~~~~~~~~

So far, you've seen how you can use the ``form_theme`` Twig block in a template
to import form customizations that will be used inside that template. You can
also tell Symfony to automatically use certain form customizations for all
templates in your application. To automatically include the customized blocks
from the ``fields.html.twig`` template created earlier, modify your application
configuration file:

.. configuration-block:: 

    .. code-block:: yaml
        
        # app/config/config.yml
        twig:
            form:
                resources: ['AcmeStoreBundle:Form:fields.html.twig']
            # ...
    
    .. code-block:: xml
    
        <!-- app/config/config.xml -->
        <twig:config ...>
                <twig:form>
                    <resource>AcmeStoreBundle:Form:fields.html.twig</resource>
                </twig:form>
                <!-- ... -->
        </twig:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'form' => array('resources' => array('AcmeStoreBundle:Form:fields.html.twig'))
            // ...
        ));

Any blocks inside the ``fields.html.twig`` template are now used globally
to define form output. 

.. sidebar::  Customizing Form Output all in a Single File

    You can also customize a form block right inside the template where that
    customization is needed. Note that this method will only work if the
    template used extends some base template via the ``{% extends %}``:
    
    .. code-block:: html+jinja
    
        {% extends '::base.html.twig' %}
        
        {% form_theme form _self %}
        {% use 'TwigBundle:Form:div_layout.html.twig' %}

        {% block field_row %}
            {# custom field row output #}
        {% endblock field_row %}

        {% block content %}
            {# ... #}
            
            {{ form_row(form.name) }}
        {% endblock %}

    The ``{% form_theme form _self %}`` tag allows form blocks to be customized
    directly inside the template that will use those customizations. Use
    this method to quickly make form output customizations that will only
    ever be needed in a single template.
    
    The ``use`` tag is also helpful as it gives you access to all of the
    blocks defined inside ``div_layout.html.twig``. For example, this ``use``
    statement is necessary to make the following form customization, as it
    gives you access to the ``attributes`` block defined in ``div_layout.html.twig``:

    .. code-block:: html+jinja

        {% block text_widget %}
            <div class="text_widget">
                <input type="text" {{ block('attributes') }} value="{{ value }}" />
            </div>
        {% endblock %}

.. index::
   single: Forms; CSRF Protection

.. _forms-csrf:

CSRF Protection
---------------

CSRF - or `Cross-site request forgery`_ - is a method by which a malicious
user attempts to make your legitimate users unknowingly submit data that
they don't intend to submit. Fortunately, CSRF attacks can be prevented by
using a CSRF token inside your forms.

The good news is that, by default, Symfony embeds and validates CSRF tokens
automatically for you. This means that you can take advantage of the CSRF
protection without doing anything. In fact, every form in this chapter has
taken advantage of the CSRF protection!

CSRF protection works by adding a field to your form - called ``_token`` by
default - that contains a value that only you and your user knows. This ensures
that the user - not some other entity - is submitting the given data. Symfony
automatically validates the presence and accuracy of this token.

The ``_token`` field is a hidden field and will be automatically rendered
if you include the ``form_rest()`` function in your template, which ensures
that all un-rendered fields are output.

The CSRF token can be customized on a form-by-form basis. For example:

.. code-block:: php

    class ProductType extends AbstractType
    {
        public function getDefaultOptions(array $options)
        {
            return array(
                'data_class'      => 'Acme\StoreBundle\Entity\Product',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'intention'  => 'product_creation',
            );
        }
    }

To disable CSRF protection, set the ``csrf_protection`` option to false.
Customizations can also be made globally in your project. For more information,
see the :ref:`form configuration reference </reference-frameworkbundle-forms>`
section.

.. note::

    The ``intention`` option is optional but greatly enhances the security of
    the generated token by making it different for each form.

Final Thoughts
--------------

You now know all of the building blocks necessary to build complex and
functional forms for your application. When building forms, keep in mind that
the first goal of a form is to translate data from an object (``Product``) to an
HTML form so that the user can modify that data. The second goal of a form is to
take the data submitted by the user and to re-apply it to the object.

There's still much more to learn about the powerful world of forms, such as
how to handle file uploads and how to create a form where a dynamic number
of sub-forms can be added (e.g. a todo list where you can keep adding more
fields via Javascript before submitting). See the cookbook for these topics.

Learn more from the Cookbook
----------------------------

* :doc:`Handling File Uploads </cookbook/form/file_uploads>`
* :doc:`Creating Custom Field Types </cookbook/form/custom_field_types>`
* :doc:`/cookbook/form/twig_form_customization`

.. _`Symfony2 Form Component`: https://github.com/symfony/Form
.. _`div_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/TwigBundle/Resources/views/Form/div_layout.html.twig
.. _`Cross-site request forgery`: http://en.wikipedia.org/wiki/Cross-site_request_forgery
