.. index::
   single: Forms

Working with Forms
==================

Symfony2 comes with a built-in form component. It deals with displaying,
rendering and submitting HTML forms.

While it is possible to process form submissions with Symfony2's 
:class:`Symfony\\Component\\HttpFoundation\\Request` class alone, the form 
component takes care of a number of common form-related tasks, such as:

1. Displaying an HTML form with automatically generated form fields
2. Converting the submitted data to PHP data types
3. Reading from and writing data into POPOs (plain old PHP objects)
4. Validating submitted form data with Symfony2's ``Validator``
5. Protecting form submissions against CSRF attacks

Overview
--------

The component deals with these concepts:

*Field*
  A class that converts submitted data to normalized values.

*Form*
  A collection of fields that knows how to validate itself.

*Template*
  A file that renders a form or a field in HTML.

*Domain objects*
  An object a form uses to populate default values and where submitted
  data is written.

The form component only relies on the HttpFoundation and Validator
components to work. If you want to use the internationalization features,
PHP's intl extension is required as well.

Form Objects
------------

A Form object encapsulates a collection of fields that convert submitted
data to the format used in your application. Form classes are created as
subclasses of :class:`Symfony\\Component\\Form\\Form`. You should implement the
method ``configure()`` to initialize the form with a set of fields.

.. code-block:: php

    // src/Acme/HelloBundle/Contact/ContactForm.php
    namespace Acme\HelloBundle\Contact;

    use Symfony\Component\Form\Form;
    use Symfony\Component\Form\TextField;
    use Symfony\Component\Form\TextareaField;
    use Symfony\Component\Form\CheckboxField;
    
    class ContactForm extends Form
    {
        protected function configure()
        {
            $this->add(new TextField('subject', array(
                'max_length' => 100,
            )));
            $this->add(new TextareaField('message'));
            $this->add(new TextField('sender'));
            $this->add(new CheckboxField('ccmyself', array(
                'required' => false,
            )));
        }
    }

A form consists of ``Field`` objects. In this case, our form has the fields
``subject``, ``message``, ``sender`` and ``ccmyself``. ``TextField``,
``TextareaField`` and ``CheckboxField`` are only three of the
available form fields; a full list can be found in :doc:`Form fields
<fields>`.

Using a Form in a Controller
----------------------------

The standard pattern for using a form in a controller looks like this:

.. code-block:: php

    // src/Acme/HelloBundle/Controller/HelloController.php
    public function contactAction()
    {
        $contactRequest = new ContactRequest($this->get('mailer'));
        $form = ContactForm::create($this->get('form.context'), 'contact');
        
        // If a POST request, write the submitted data into $contactRequest
        // and validate the object
        $form->bind($this->get('request'), $contactRequest);
        
        // If the form has been submitted and is valid...
        if ($form->isValid()) {
            $contactRequest->send();
        }

        // Display the form with the values in $contactRequest
        return $this->render('AcmeHello:Hello:contact.html.twig', array(
            'form' => $form
        ));
    }
   
There are two code paths there:

1. If the form has not been submitted or is invalid, it is simply passed to
   the template.
2. If the form has been submitted and is valid, the contact request is sent.

We created the form with the static ``create()`` method. This method expects
a form context that contains all default services (for example a ``Validator``)
and settings that a form needs to work.

.. note:

    If you don't use Symfony2 or its service container, don't worry. You can
    easily create a ``FormContext`` and a ``Request`` manually:
    
    .. code-block:: php
    
        use Symfony\Component\Form\FormContext;
        use Symfony\Component\HttpFoundation\Request;
        
        $context = FormContext::buildDefault();
        $request = Request::createFromGlobals();

Forms and Domain Objects
------------------------

In the last example a ``ContactRequest`` was bound to the form. The property
values of this object are used to populate the form fields. After binding,
the submitted values are written into the object again. The ``ContactRequest``
class could look like this:

.. code-block:: php

    // src/Acme/HelloBundle/Contact/ContactRequest.php
    namespace Acme\HelloBundle\Contact;

    class ContactRequest
    {
        protected $subject = 'Subject...';
        
        protected $message;
        
        protected $sender;
        
        protected $ccmyself = false;
        
        protected $mailer;
        
        public function __construct(\Swift_Mailer $mailer)
        {
            $this->mailer = $mailer;
        }
        
        public function setSubject($subject)
        {
            $this->subject = $subject;
        }
        
        public function getSubject()
        {
            return $this->subject;
        }
        
        // Setters and getters for the other properties
        // ...
        
        public function send()
        {
            // Send the contact mail
            $message = \Swift_Message::newInstance()
                ->setSubject($this->subject)
                ->setFrom($this->sender)
                ->setTo('me@example.com')
                ->setBody($this->message);
                
            $this->mailer->send($message);
        }
    }
    
.. note::

    See :doc:`Emails </cookbook/email>` for more information on sending mails.

For each field in your form, the class of the domain object needs to have

1. A public property with the field's name, or
2. A public setter and getter with the prefix "set"/"get", followed by the
   field's name with a first capital letter.
   
Validating Submitted Data
-------------------------

The form uses the ``Validator`` component to validate submitted form values.
All constraints on the domain object, on the form and on its fields will be 
validated when ``bind()`` is called. We will add a few constraints to
``ContactRequest`` to make sure that nobody can submit the form with invalid
data.

.. code-block:: php

    // src/Acme/HelloBundle/Contact/ContactRequest.php
    namespace Acme\HelloBundle\Contact;

    class ContactRequest
    {
        /**
         * @validation:MaxLength(100)
         * @validation:NotBlank
         */
        protected $subject = 'Subject...';
        
        /**
         * @validation:NotBlank
         */
        protected $message;
        
        /**
         * @validation:Email
         * @validation:NotBlank
         */
        protected $sender;
        
        /**
         * @validation:AssertType("boolean")
         */
        protected $ccmyself = false;
        
        // Other code...
    }

If any constraint fails, the error is displayed next to the corresponding
form field. You can learn more about constraints in :ref:`validation-constraints`.

Creating Form Fields Automatically
----------------------------------

If you use Doctrine2 or Symfony's ``Validator``, Symfony already knows quite
a lot about your domain classes. It knows which data type is used to persist
a property in the database, what validation constraints the property has etc.
The Form component can use this information to "guess" which field type should
be created with which settings.

To use this feature, a form needs to know the class of the related domain
object. You can set this class within the ``configure()`` method of the form
by using ``setDataClass()`` and passing the fully qualified class name as
a string. Calling ``add()`` with only the name of the property will then
automatically create the best-matching field. 

.. code-block:: php

    // src/Acme/HelloBundle/Contact/ContactForm.php
    class ContactForm extends Form
    {
        protected function configure()
        {
            $this->setDataClass('Acme\\HelloBundle\\Contact\\ContactRequest');
            $this->add('subject');  // TextField with max_length=100 because
                                    // of the @MaxLength constraint
            $this->add('message');  // TextField
            $this->add('sender');   // EmailField because of the @Email constraint
            $this->add('ccmyself'); // CheckboxField because of @AssertType("boolean")
        }
    }

These field guesses are obviously not always right. For the property ``message``
Symfony created a ``TextField``, it couldn't know from the validation constraints
that you wanted a ``TextareaField`` instead. So you have to create this field
manually. You can also tweak the options of the generated fields by passing
them in the second parameter. We will add a ``max_length`` option to the
``sender`` field to limit its length.

.. code-block:: php

    // src/Acme/HelloBundle/Contact/ContactForm.php
    class ContactForm extends Form
    {
        protected function configure()
        {
            $this->setDataClass('Acme\\HelloBundle\\Contact\\ContactRequest');
            $this->add('subject'); 
            $this->add(new TextareaField('message'));
            $this->add('sender', array('max_length' => 50));
            $this->add('ccmyself');
        }
    }
    
Generating form fields automatically helps you to increase your development
speed and reduces code duplication. You can store information about class 
properties once and let Symfony2 do the other work for you.

Rendering Forms as HTML
-----------------------

In the controller we passed the form to the template in the ``form`` variable.
In the template we can use the ``form_field`` helper to output a raw prototype
of the form.

.. code-block:: html+jinja

    # src/Acme/HelloBundle/Resources/views/Hello/contact.html.twig
    {% extends 'AcmeHello::layout.html.twig' %}

    {% block content %}
    <form action="#" method="post">
        {{ form_field(form) }}
        
        <input type="submit" value="Send!" />
    </form>
    {% endblock %}
    
Customizing the HTML Output
---------------------------

In most applications you will want to customize the HTML of the form. You
can do so by using the other built-in form rendering helpers.

.. code-block:: html+jinja

    # src/Acme/HelloBundle/Resources/views/Hello/contact.html.twig
    {% extends 'AcmeHello::layout.html.twig' %}

    {% block content %}
    <form action="#" method="post" {{ form_enctype(form) }}>
        {{ form_errors(form) }}
        
        {% for field in form %}
            {% if not field.ishidden %}
            <div>
                {{ form_errors(field) }}
                {{ form_label(field) }}
                {{ form_field(field) }}
            </div>
            {% endif %}
        {% endfor %}

        {{ form_hidden(form) }}
        <input type="submit" />
    </form>
    {% endblock %}
    
Symfony2 comes with the following helpers:

*``form_enctype``*
  Outputs the ``enctype`` attribute of the form tag. Required for file uploads.

*``form_errors``*
  Outputs the a ``<ul>`` tag with errors of a field or a form.

*``form_label``*
  Outputs the ``<label>`` tag of a field.

*``form_field``*
  Outputs HTML of a field or a form.

*``form_hidden``*
  Outputs all hidden fields of a form.

Form rendering is covered in detail in :doc:`Forms in Templates <view>`.

Congratulations! You just created your first fully-functional form with
Symfony2.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/email`
* :doc:`/cookbook/gmail`
* :doc:`/cookbook/templating/PHP`
