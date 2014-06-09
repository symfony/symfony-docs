.. index::
    single: Forms; Form Events

Form Events
===========

The Form component provides a structured process to let you customize your
forms, by making use of the :doc:`EventDispatcher </components/event_dispatcher/introduction>`
component. Using form events, you may modify information or fields at
different steps of the workflow: from the population of the form to the
submission of the data from the request.

Registering an event listener is very easy using the Form component.

For example, if you wish to register a function to the
``FormEvents::PRE_SUBMIT`` event, the following code lets you add a field,
depending on the request' values::

    // ...

    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;

    $listener = function (FormEvent $event) {
        // ...
    };

    $form = $formFactory->createBuilder()
        // add form fields
        ->addEventListener(FormEvents::PRE_SUBMIT, $listener);

    // ...

The Form Workflow
-----------------

The Form Submission Workflow
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. image:: /images/components/form/general_flow.png
    :align: center

1) Pre-populating the Form (``FormEvents::PRE_SET_DATA`` and ``FormEvents::POST_SET_DATA``)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. image:: /images/components/form/set_data_flow.png
    :align: center

Two events are dispatched during pre-population of a form, when
:method:`Form::setData() <Symfony\\Component\\Form\\Form::setData>`
is called: ``FormEvents::PRE_SET_DATA`` and ``FormEvents::POST_SET_DATA``.

A) The ``FormEvents::PRE_SET_DATA`` Event
.........................................

The ``FormEvents::PRE_SET_DATA`` event is dispatched at the beginning of the
``Form::setData()`` method. It can be used to:

* Modify the data given during pre-population;
* Modify a form depending on the pre-populated data (adding or removing fields dynamically).

:ref:`Form Events Information Table<component-form-event-table>`

+-----------------+-----------+
|   Data type     | Value     |
+=================+===========+
| Model data      | ``null``  |
+-----------------+-----------+
| Normalized data | ``null``  |
+-----------------+-----------+
| View data       | ``null``  |
+-----------------+-----------+

.. caution::

    During ``FormEvents::PRE_SET_DATA``,
    :method:`Form::setData() <Symfony\\Component\\Form\\Form::setData>`
    is locked and will throw an exception if used. If you wish to modify
    data, you should use
    :method:`FormEvent::setData() <Symfony\\Component\\Form\\FormEvent::setData>`
    instead.

.. sidebar:: ``FormEvents::PRE_SET_DATA`` in the Form component

    The ``collection`` form type relies on the
    :class:`Symfony\\Component\\Form\\Extension\\Core\\EventListener\\ResizeFormListener`
    subscriber, listening to the ``FormEvents::PRE_SET_DATA`` event in order
    to reorder the form's fields depending on the data from the pre-populated
    object, by removing and adding all form rows.

B) The ``FormEvents::POST_SET_DATA`` Event
..........................................

The ``FormEvents::POST_SET_DATA`` event is dispatched at the end of the
:method:`Form::setData() <Symfony\\Component\\Form\\Form::setData>`
method. This event is mostly here for reading data after having pre-populated
the form.

:ref:`Form Events Information Table<component-form-event-table>`

+-----------------+------------------------------------------------------+
| Data type       | Value                                                |
+=================+======================================================+
| Model data      | Model data injected into ``setData()``               |
+-----------------+------------------------------------------------------+
| Normalized data | Model data transformed using a model transformer     |
+-----------------+------------------------------------------------------+
| View data       | Normalized data transformed using a view transformer |
+-----------------+------------------------------------------------------+

.. sidebar:: ``FormEvents::POST_SET_DATA`` in the Form component

    .. versionadded:: 2.4
        The data collector extension was introduced in Symfony 2.4.

    The :class:`Symfony\\Component\\Form\\Extension\\DataCollector\\EventListener\\DataCollectorListener`
    class is subscribed to listen to the ``FormEvents::POST_SET_DATA`` event
    in order to collect information about the forms from the denormalized
    model and view data.

2) Submitting a Form (``FormEvents::PRE_SUBMIT``, ``FormEvents::SUBMIT`` and ``FormEvents::POST_SUBMIT``)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. image:: /images/components/form/submission_flow.png
    :align: center

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

* Change data from the request, before submitting the data to the form.
* Add or remove form fields, before submitting the data to the form.

:ref:`Form Events Information Table<component-form-event-table>`

+-----------------+------------------------------------------+
| Data type       | Value                                    |
+=================+==========================================+
| Model data      | Same as in ``FormEvents::POST_SET_DATA`` |
+-----------------+------------------------------------------+
| Normalized data | Same as in ``FormEvents::POST_SET_DATA`` |
+-----------------+------------------------------------------+
| View data       | Same as in ``FormEvents::POST_SET_DATA`` |
+-----------------+------------------------------------------+

.. sidebar:: ``FormEvents::PRE_SUBMIT`` in the Form component

    The :class:`Symfony\\Component\\Form\\Extension\\Core\\EventListener\\TrimListener`
    subscriber subscribes to the ``FormEvents::PRE_SUBMIT`` event in order to
    trim the request's data (for string values).
    The :class:`Symfony\\Component\\Form\\Extension\\Csrf\\EventListener\\CsrfValidationListener`
    subscriber subscribes to the ``FormEvents::PRE_SUBMIT`` event in order to
    validate the CSRF token.

B) The ``FormEvents::SUBMIT`` Event
...................................

The ``FormEvents::SUBMIT`` event is dispatched just before the
:method:`Form::submit() <Symfony\\Component\\Form\\Form::submit>` method
transforms back the normalized data to the model and view data.

It can be used to change data from the normalized representation of the data.

:ref:`Form Events Information Table<component-form-event-table>`

+-----------------+-------------------------------------------------------------------------------------+
| Data type       | Value                                                                               |
+=================+=====================================================================================+
| Model data      | Same as in ``FormEvents::POST_SET_DATA``                                            |
+-----------------+-------------------------------------------------------------------------------------+
| Normalized data | Data from the request reverse-transformed from the request using a view transformer |
+-----------------+-------------------------------------------------------------------------------------+
| View data       | Same as in ``FormEvents::POST_SET_DATA``                                            |
+-----------------+-------------------------------------------------------------------------------------+

.. caution::

    At this point, you cannot add or remove fields to the form.

.. sidebar:: ``FormEvents::SUBMIT`` in the Form component

    The :class:`Symfony\\Component\\Form\\Extension\\Core\\EventListener\\ResizeFormListener`
    subscribes to the ``FormEvents::SUBMIT`` event in order to remove the
    fields that need to be removed whenever manipulating a collection of forms
    for which ``allow_delete`` has been enabled.

C) The ``FormEvents::POST_SUBMIT`` Event
........................................

The ``FormEvents::POST_SUBMIT`` event is dispatched after the
:method:`Form::submit() <Symfony\\Component\\Form\\Form::submit>` once the
model and view data have been denormalized.

It can be used to fetch data after denormalization.

:ref:`Form Events Information Table<component-form-event-table>`

+-----------------+---------------------------------------------------------------+
| Data type       | Value                                                         |
+=================+===============================================================+
| Model data      | Normalized data reverse-transformed using a model transformer |
+-----------------+---------------------------------------------------------------+
| Normalized data | Same as in ``FormEvents::POST_SUBMIT``                        |
+-----------------+---------------------------------------------------------------+
| View data       | Normalized data transformed using a view transformer          |
+-----------------+---------------------------------------------------------------+

.. caution::

    At this point, you cannot add or remove fields to the form.

.. sidebar:: ``FormEvents::POST_SUBMIT`` in the Form component

    .. versionadded:: 2.4
        The data collector extension was introduced in Symfony 2.4.

    The :class:`Symfony\\Component\\Form\\Extension\\DataCollector\\EventListener\\DataCollectorListener`
    subscribes to the ``FormEvents::POST_SUBMIT`` event in order to collect
    information about the forms.
    The :class:`Symfony\\Component\\Form\\Extension\\Validator\\EventListener\\ValidationListener`
    subscribes to the ``FormEvents::POST_SUBMIT`` event in order to
    automatically validate the denormalized object, and update the normalized
    as well as the view's representations.

Registering Event Listeners or Event Subscribers
------------------------------------------------

In order to be able to use Form events, you need to create an event listener
or an event subscriber, and register it to an event.

The name of each of the "form" events is defined as a constant on the
:class:`Symfony\\Component\\Form\\FormEvents` class.
Additionally, each event callback (listener or subscriber method) is passed a
single argument, which is an instance of
:class:`Symfony\\Component\\Form\\FormEvent`. The event object contains a
reference to the current state of the form, and the current data being
processed.

.. _component-form-event-table:

+------------------------+-------------------------------+------------------+
| Name                   | ``FormEvents`` Constant       | Event's data     |
+========================+===============================+==================+
| ``form.pre_set_data``  | ``FormEvents::PRE_SET_DATA``  | Model data       |
+------------------------+-------------------------------+------------------+
| ``form.post_set_data`` | ``FormEvents::POST_SET_DATA`` | Model data       |
+------------------------+-------------------------------+------------------+
| ``form.pre_bind``      | ``FormEvents::PRE_SUBMIT``    | Request data     |
+------------------------+-------------------------------+------------------+
| ``form.bind``          | ``FormEvents::SUBMIT``        | Normalized data  |
+------------------------+-------------------------------+------------------+
| ``form.post_bind``     | ``FormEvents::POST_SUBMIT``   | View data        |
+------------------------+-------------------------------+------------------+

.. versionadded:: 2.3
    Before Symfony 2.3, ``FormEvents::PRE_SUBMIT``, ``FormEvents::SUBMIT``
    and ``FormEvents::POST_SUBMIT`` were called ``FormEvents::PRE_BIND``,
    ``FormEvents::BIND`` and ``FormEvents::POST_BIND``.

.. caution::

    The ``FormEvents::PRE_BIND``, ``FormEvents::BIND`` and
    ``FormEvents::POST_BIND`` constants will be removed in version 3.0 of
    Symfony.
    The event names still keep their original values, so make sure you use the
    ``FormEvents`` constants in your code for forward compatibility.

Event Listeners
~~~~~~~~~~~~~~~

An event listener may be any type of valid callable.

Creating and binding an event listener to the form is very easy::

    // ...

    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;

    $form = $formFactory->createBuilder()
        ->add('username', 'text')
        ->add('show_email', 'checkbox')
        ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user) {
                return;
            }

            // Check whether the user has chosen to display his email or not.
            // If the data was submitted previously, the additional value that is
            // included in the request variables needs to be removed.
            if (true === $user['show_email']) {
                $form->add('email', 'email');
            } else {
                unset($user['email']);
                $event->setData($user);
            }
        })
        ->getForm();

    // ...

When you have created a form type class, you can use one of its methods as a
callback for better readability::

    // ...

    class SubscriptionType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('username', 'text');
            $builder->add('show_email', 'checkbox');
            $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        }

        public function onPreSetData(FormEvent $event)
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

.. code-block:: php

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;

    class AddEmailFieldListener implements EventSubscriberInterface
    {
        public function getSubscribedEvents()
        {
            return array(
                FormEvents::PRE_SET_DATA => 'onPreSetData',
                FormEvents::PRE_SUBMIT   => 'onPreSubmit',
            );
        }

        public function onPreSetData(FormEvent $event)
        {
            $user = $event->getData();
            $form = $event->getForm();

            // Check whether the user from the initial data has chosen to
            // display his email or not.
            if (true === $user->isShowEmail()) {
                $form->add('email', 'email');
            }
        }

        public function onPreSubmit(FormEvent $event)
        {
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user) {
                return;
            }

            // Check whether the user has chosen to display his email or not.
            // If the data was submitted previously, the additional value that
            // is included in the request variables needs to be removed.
            if (true === $user['show_email']) {
                $form->add('email', 'email');
            } else {
                unset($user['email']);
                $event->setData($user);
            }
        }
    }

To register the event subscriber, use the addEventSubscriber() method::

    // ...

    $form = $formFactory->createBuilder()
        ->add('username', 'text')
        ->add('show_email', 'checkbox')
        ->addEventSubscriber(new AddEmailFieldListener())
        ->getForm();

    // ...
