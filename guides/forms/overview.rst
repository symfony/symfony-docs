.. index::
   single: Forms

Working with forms
==================

Symfony2 comes with a built-in form component. It deals with displaying,
rendering and submitting HTML forms.

While it is possible to process form submissions with Symfony2's ``Request``
class alone, the form component takes care of a number of common
form-related tasks, such as:

1. Displaying an HTML form with automatically generated form fields
2. Converting the submitted data to PHP data types
3. Reading from and writing data into POPOs (plain old PHP objects)
4. Validating submitted form data with Symfony2's ``Validator``

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

Form objects
------------

A Form object encapsulates a collection of fields that convert submitted
data to the format used in your application. Form classes are created as
subclasses of :class:`Symfony\\Component\\Form\\Form`. You should implement the
method ``configure()`` to initialize the form with a set of fields.

.. code-block:: php

    // src/Sensio/HelloBundle/Contact/ContactForm.php
    use Symfony\Component\Form
    use Symfony\Component\Form\TextField
    use Symfony\Component\Form\TextareaField
    use Symfony\Component\Form\EmailField
    use Symfony\Component\Form\CheckboxField
    
    class ContactForm extends Form
    {
        protected function configure()
        {
            $this->add(new TextField('subject', array(
                'max_length' => 100,
            )));
            $this->add(new TextareaField('message'));
            $this->add(new EmailField('sender'));
            $this->add(new CheckboxField('ccmyself', array(
                'required' => false,
            )));
        }
    }

A form consists of ``Field`` objects. In this case, our form has the fields
``subject``, ``message``, ``sender`` and ``ccmyself``. ``TextField``,
``TextareaField``, ``EmailField`` and ``CheckboxField`` are only four of the
available form fields; a full list can be found in :doc:`Form fields
<fields>`.

Using a form in a controller
----------------------------

The standard pattern for using a form in a controller looks like this:

.. code-block:: php

    // src/Sensio/HelloBundle/Controller/HelloController.php
    public function contactAction()
    {
        $contactRequest = new ContactRequest();
        $form = new ContactForm();
        
        // If a POST request, write submitted data into $contactRequest
        // and validate it
        $form->bind($this->get('request'), $contactRequest);
        
        // If the form has been submitted and validates...
        if ($form->isValid()) {
            $contactRequest->send();
        }

        // Display the form with the values in $contactRequest
        return $this->render('HelloBundle:Hello:contact.html.twig', array(
            'form' => $form
        ));
    }
   
There are two code paths there:

1. If the form has not been submitted or is invalid, it is simply passed to
   the template.
2. If the form has been submitted and is valid, the contact request is sent.

Forms and domain objects
------------------------

In the last example a ``ContactRequest`` was bound to the form. The property
values of this object are used to populate the form fields. After binding,
the submitted values are written into the object again. The ``ContactRequest``
class could look like this:

.. code-block:: php

    // src/Sensio/HelloBundle/Contact/ContactRequest.php
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

    See :doc:`Emails </guides/emails>` for more information on sending mails.

For each field in your form, the class of the domain object needs to have

1. A public property with the field's name, or
2. A public setter and getter with the prefix "set"/"get", followed by the
   field's name with a first capital letter.
   
Validating submitted data
-------------------------

The form uses the ``Validator`` component to validate submitted form values.
All constraints on the domain object, on the form and on its fields will be 
validated when ``bind()`` is called. You can learn more about constraints 
in :doc:`Validation constraints </guides/validator/constraints>`.

Rendering forms as HTML
-----------------------

In the controller we passed the form to the template in the ``form`` variable.
In the template we can use the ``form_field`` helper to output a raw prototype
of the form.

.. code-block:: html+jinja

    # src/Sensio/HelloBundle/Resources/views/Hello/contact.html.twig
    {% extends 'HelloBundle::layout.html.twig' %}

    <form action="#" method="post">
        {{ form_field(form) }}
        
        <input type="submit" value="Send!" />
    </form>

Form rendering in templates is covered in chapter :doc:`Forms in templates
<view>`.

Congratulations! You just created your first fully-functional form with
Symfony2.