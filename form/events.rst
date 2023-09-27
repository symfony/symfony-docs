Form Events
===========

The Form component provides a structured process to let you customize your
forms, by making use of the
:doc:`EventDispatcher component </components/event_dispatcher>`.
Using form events, you may modify information or fields at different steps
of the workflow: from the population of the form to the submission of the
data from the request.

For example, if you need to add a field depending on request values, you can
register an event listener to the ``FormEvents::PRE_SUBMIT`` event as follows::

    // ...

    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;

    $listener = function (FormEvent $event) {
        // ...
    };

    $form = $formFactory->createBuilder()
        // ... add form fields
        ->addEventListener(FormEvents::PRE_SUBMIT, $listener);

    // ...

The Form Workflow
-----------------

In the lifecycle of a form, there are two moments where the form data can
be updated:

1. During **pre-population** (``setData()``) when building the form;
2. When handling **form submission** (``handleRequest()``) to update the
   form data based on the values the user entered.

.. raw:: html

    <object data="../_images/form/form_workflow.svg" type="image/svg+xml"
        alt="A generic flow diagram showing the two phases. These are
        described in the next subsections."
    ></object>

1) Pre-populating the Form (``FormEvents::PRE_SET_DATA`` and ``FormEvents::POST_SET_DATA``)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. raw:: html

    <object data="../_images/form/form_prepopulation_workflow.svg" type="image/svg+xml"
        alt="A flow diagram showing the two events that are dispatched during pre-population."
    ></object>

Two events are dispatched during pre-population of a form, when
:method:`Form::setData() <Symfony\\Component\\Form\\Form::setData>`
is called: ``FormEvents::PRE_SET_DATA`` and ``FormEvents::POST_SET_DATA``.

A) The ``FormEvents::PRE_SET_DATA`` Event
.........................................

The ``FormEvents::PRE_SET_DATA`` event is dispatched at the beginning of the
``Form::setData()`` method. It is used to modify the data given during
pre-population with
:method:`FormEvent::setData() <Symfony\\Component\\Form\\FormEvent::setData>`.
The method :method:`Form::setData() <Symfony\\Component\\Form\\Form::setData>`
is locked since the event is dispatched from it and will throw an exception
if called from a listener.

====================  ======================================
Data Type             Value
====================  ======================================
Event data            Model data injected into ``setData()``
Form model data       ``null``
Form normalized data  ``null``
Form view data        ``null``
====================  ======================================

.. seealso::

    See all form events at a glance in the
    :ref:`Form Events Information Table <component-form-event-table>`.

    instead.

.. sidebar:: ``FormEvents::PRE_SET_DATA`` in the Form component

    The ``Symfony\Component\Form\Extension\Core\Type\CollectionType`` form type relies
    on the ``Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener``
    subscriber, listening to the ``FormEvents::PRE_SET_DATA`` event in order
    to reorder the form's fields depending on the data from the pre-populated
    object, by removing and adding all form rows.

B) The ``FormEvents::POST_SET_DATA`` Event
..........................................

The ``FormEvents::POST_SET_DATA`` event is dispatched at the end of the
:method:`Form::setData() <Symfony\\Component\\Form\\Form::setData>`
method. This event can be used to modify a form depending on the populated data
(adding or removing fields dynamically).

====================  ====================================================
Data Type             Value
====================  ====================================================
Event data            Model data injected into ``setData()``
Form model data       Model data injected into ``setData()``
Form normalized data  Model data transformed using a model transformer
Form view data        Normalized data transformed using a view transformer
====================  ====================================================

.. seealso::

    See all form events at a glance in the
    :ref:`Form Events Information Table <component-form-event-table>`.

.. sidebar:: ``FormEvents::POST_SET_DATA`` in the Form component

    The ``Symfony\Component\Form\Extension\DataCollector\EventListener\DataCollectorListener``
    class is subscribed to listen to the ``FormEvents::POST_SET_DATA`` event
    in order to collect information about the forms from the denormalized
    model and view data.

2) Submitting a Form (``FormEvents::PRE_SUBMIT``, ``FormEvents::SUBMIT`` and ``FormEvents::POST_SUBMIT``)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. raw:: html

    <object data="../_images/form/form_submission_workflow.svg" type="image/svg+xml"
        alt="A flow diagram showing the three events that are dispatched when handling form submissions."
    ></object>

Three events are dispatched when
:method:`Form::handleRequest() <Symfony\\Component\\Form\\Form::handleRequest>`
or :method:`Form::submit() <Symfony\\Component\\Form\\Form::submit>` are
called: ``FormEvents::PRE_SUBMIT``, ``FormEvents::SUBMIT``,
``FormEvents::POST_SUBMIT``.

A) The ``FormEvents::PRE_SUBMIT`` Event
.......................................

The ``FormEvents::PRE_SUBMIT`` event is dispatched at the beginning of the
:method:`Form::submit() <Symfony\\Component\\Form\\Form::submit>` method.

It can be used to:

* Change data from the request, before submitting the data to the form;
* Add or remove form fields, before submitting the data to the form.

====================  ========================================
Data Type             Value
====================  ========================================
Event data            Data from the request
Form model data       Same as in ``FormEvents::POST_SET_DATA``
Form normalized data  Same as in ``FormEvents::POST_SET_DATA``
Form view data        Same as in ``FormEvents::POST_SET_DATA``
====================  ========================================

.. seealso::

    See all form events at a glance in the
    :ref:`Form Events Information Table <component-form-event-table>`.

.. sidebar:: ``FormEvents::PRE_SUBMIT`` in the Form component

    The ``Symfony\Component\Form\Extension\Core\EventListener\TrimListener``
    subscriber subscribes to the ``FormEvents::PRE_SUBMIT`` event in order to
    trim the request's data (for string values).
    The ``Symfony\Component\Form\Extension\Csrf\EventListener\CsrfValidationListener``
    subscriber subscribes to the ``FormEvents::PRE_SUBMIT`` event in order to
    validate the CSRF token.

B) The ``FormEvents::SUBMIT`` Event
...................................

The ``FormEvents::SUBMIT`` event is dispatched right before the
:method:`Form::submit() <Symfony\\Component\\Form\\Form::submit>` method
transforms back the normalized data to the model and view data.

It can be used to change data from the normalized representation of the data.

====================  ===================================================================================
Data Type             Value
====================  ===================================================================================
Event data            Data from the request reverse-transformed from the request using a view transformer
Form model data       Same as in ``FormEvents::POST_SET_DATA``
Form normalized data  Same as in ``FormEvents::POST_SET_DATA``
Form view data        Same as in ``FormEvents::POST_SET_DATA``
====================  ===================================================================================

.. seealso::

    See all form events at a glance in the
    :ref:`Form Events Information Table <component-form-event-table>`.

.. caution::

    At this point, you cannot add or remove fields to the form.

.. sidebar:: ``FormEvents::SUBMIT`` in the Form component

    The ``Symfony\Component\Form\Extension\Core\EventListener\FixUrlProtocolListener``
    subscribes to the ``FormEvents::SUBMIT`` event in order to prepend a default
    protocol to URL fields that were submitted without a protocol.

C) The ``FormEvents::POST_SUBMIT`` Event
........................................

The ``FormEvents::POST_SUBMIT`` event is dispatched after the
:method:`Form::submit() <Symfony\\Component\\Form\\Form::submit>` once the
model and view data have been denormalized.

It can be used to fetch data after denormalization.

====================  ===================================================================================
Data Type             Value
====================  ===================================================================================
Event data            Normalized data transformed using a view transformer
Form model data       Normalized data reverse-transformed using a model transformer
Form normalized data  Data from the request reverse-transformed from the request using a view transformer
Form view data        Normalized data transformed using a view transformer
====================  ===================================================================================

.. seealso::

    See all form events at a glance in the
    :ref:`Form Events Information Table <component-form-event-table>`.

.. caution::

    At this point, you cannot add or remove fields to the current form and its
    children.

.. sidebar:: ``FormEvents::POST_SUBMIT`` in the Form component

    The ``Symfony\Component\Form\Extension\DataCollector\EventListener\DataCollectorListener``
    subscribes to the ``FormEvents::POST_SUBMIT`` event in order to collect
    information about the forms.
    The ``Symfony\Component\Form\Extension\Validator\EventListener\ValidationListener``
    subscribes to the ``FormEvents::POST_SUBMIT`` event in order to
    automatically validate the denormalized object.

Registering Event Listeners or Event Subscribers
------------------------------------------------

In order to be able to use Form events, you need to create an event listener
or an event subscriber and register it to an event.

The name of each of the "form" events is defined as a constant on the
:class:`Symfony\\Component\\Form\\FormEvents` class.
Additionally, each event callback (listener or subscriber method) is passed a
single argument, which is an instance of
:class:`Symfony\\Component\\Form\\FormEvent`. The event object contains a
reference to the current state of the form and the current data being
processed.

.. _component-form-event-table:

======================  =============================  ===============
Name                    ``FormEvents`` Constant        Event's Data
======================  =============================  ===============
``form.pre_set_data``   ``FormEvents::PRE_SET_DATA``   Model data
``form.post_set_data``  ``FormEvents::POST_SET_DATA``  Model data
``form.pre_submit``     ``FormEvents::PRE_SUBMIT``     Request data
``form.submit``         ``FormEvents::SUBMIT``         Normalized data
``form.post_submit``    ``FormEvents::POST_SUBMIT``    View data
======================  =============================  ===============

Event Listeners
~~~~~~~~~~~~~~~

An event listener may be any type of valid callable. For example, you can
define an event listener function inline right in the ``addEventListener``
method of the ``FormFactory``::

    // ...

    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;

    $form = $formFactory->createBuilder()
        ->add('username', TextType::class)
        ->add('showEmail', CheckboxType::class)
        ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user) {
                return;
            }

            // checks whether the user has chosen to display their email or not.
            // If the data was submitted previously, the additional value that is
            // included in the request variables needs to be removed.
            if (isset($user['showEmail']) && $user['showEmail']) {
                $form->add('email', EmailType::class);
            } else {
                unset($user['email']);
                $event->setData($user);
            }
        })
        ->getForm();

    // ...

When you have created a form type class, you can use one of its methods as a
callback for better readability::

    // src/Form/SubscriptionType.php
    namespace App\Form;

    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;

    // ...
    class SubscriptionType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('username', TextType::class)
                ->add('showEmail', CheckboxType::class)
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    [$this, 'onPreSetData']
                )
            ;
        }

        public function onPreSetData(FormEvent $event): void
        {
            // ...
        }
    }

Event Subscribers
~~~~~~~~~~~~~~~~~

Event subscribers have different uses:

* Improving readability;
* Listening to multiple events;
* Regrouping multiple listeners inside a single class.

Consider the following example of a form event subscriber::

    // src/Form/EventListener/AddEmailFieldListener.php
    namespace App\Form\EventListener;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;

    class AddEmailFieldListener implements EventSubscriberInterface
    {
        public static function getSubscribedEvents(): array
        {
            return [
                FormEvents::PRE_SET_DATA => 'onPreSetData',
                FormEvents::PRE_SUBMIT   => 'onPreSubmit',
            ];
        }

        public function onPreSetData(FormEvent $event): void
        {
            $user = $event->getData();
            $form = $event->getForm();

            // checks whether the user from the initial data has chosen to
            // display their email or not.
            if (true === $user->isShowEmail()) {
                $form->add('email', EmailType::class);
            }
        }

        public function onPreSubmit(FormEvent $event): void
        {
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user) {
                return;
            }

            // checks whether the user has chosen to display their email or not.
            // If the data was submitted previously, the additional value that
            // is included in the request variables needs to be removed.
            if (isset($user['showEmail']) && $user['showEmail']) {
                $form->add('email', EmailType::class);
            } else {
                unset($user['email']);
                $event->setData($user);
            }
        }
    }

To register the event subscriber, use the ``addEventSubscriber()`` method::

    use App\Form\EventListener\AddEmailFieldListener;
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;

    // ...

    $form = $formFactory->createBuilder()
        ->add('username', TextType::class)
        ->add('showEmail', CheckboxType::class)
        ->addEventSubscriber(new AddEmailFieldListener())
        ->getForm();

    // ...
