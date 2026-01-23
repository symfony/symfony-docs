Form Events
===========

Form events let you hook into the :ref:`form lifecycle <form-events-lifecycle>`
to :doc:`modify forms dynamically </form/dynamic_form_modification>`, transform
data, or react to submissions. This article explains when (and when not) to use
form events, how to choose the right event, and how to debug issues.

.. seealso::

    For practical examples using form events, see
    :doc:`/form/dynamic_form_modification`.

.. _form-events-when-to-use:

When to Use Form Events
-----------------------

Form events are the right tool when you need to modify a form based on
**runtime data** that isn't known when the form type is defined. Common
scenarios include:

**Adding or removing fields based on entity state**
    Show a "name" field only for new entities, or hide certain fields when
    editing existing records.

**Changing field options based on submitted data**
    Update the choices of a ``<select>`` field based on another field's value
    (for example, showing states/provinces based on selected country).

**Modifying submitted data before processing**
    Normalize, sanitize, or transform user input before validation.

**Populating fields from external sources**
    Fetch data from APIs or services to fill in form fields dynamically.

.. _form-events-decision-table:

Choosing the Right Event
~~~~~~~~~~~~~~~~~~~~~~~~

Use this decision table to select the appropriate event:

=======================================================================  ===============
Scenario                                                                 Event to use
=======================================================================  ===============
Modify initial data before the form processes it                         ``PRE_SET_DATA``
Modify form structure based on initial data (entity is new vs existing)  ``POST_SET_DATA``
Modify or sanitize raw submitted data before processing                  ``PRE_SUBMIT``
Add/remove fields based on submitted values (like dependent selects)     ``PRE_SUBMIT`` (on parent) or ``POST_SUBMIT`` (on the child)
Modify normalized submitted data                                         ``SUBMIT``
React after submission is complete (for logging, etc.)                   ``POST_SUBMIT``
=======================================================================  ===============

.. tip::

    When in doubt between ``PRE_SET_DATA`` and ``PRE_SUBMIT``, ask yourself:
    "Do I need to react to *initial* data or *submitted* data?" Initial data
    comes from your object; submitted data comes from the HTTP request.

.. _form-events-when-not-to-use:

When NOT to Use Form Events
---------------------------

Before reaching for events, consider these simpler alternatives:

**For static conditional fields**, use form options::

    // instead of events, pass options to your form type
    $form = $this->createForm(ProductType::class, $product, [
        'show_advanced_fields' => $user->isAdmin(),
    ]);

    // in your form type
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name');

        if ($options['show_advanced_fields']) {
            $builder->add('internalCode');
        }
    }

**For data that depends on the logged in user**, inject the
:class:`Symfony\\Bundle\\SecurityBundle\\Security` service::

    class MessageType extends AbstractType
    {
        public function __construct(
            private Security $security,
            private UserRepository $userRepository,
        ) {
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder->add('body', TextareaType::class);

            $currentUser = $this->security->getUser();
            $friends = $this->userRepository->findFriendsOf($currentUser);

            $builder->add('recipient', EntityType::class, [
                'class' => User::class,
                'choices' => $friends,
                'choice_label' => 'displayName',
                'placeholder' => 'Select a friend',
            ]);
        }

        // ...
    }

**For data transformation**, use :doc:`data transformers </form/data_transformers>`
instead. They're designed specifically for converting between formats.

**For custom data mapping**, use :doc:`data mappers </form/data_mappers>` or
the ``getter``/``setter`` field options.

**For validation logic**, use :doc:`validation constraints </validation>` with
:doc:`validation groups </form/validation_groups>` or the :doc:`When </reference/constraints/When>`
constraint.

**For setting default values**, use the ``empty_data`` option or populate your
object before passing it to the form.

.. _form-events-lifecycle:

The Form Lifecycle
------------------

Understanding when each event fires helps you choose the right one. A form goes
through two phases: it is first **built and pre-populated**, then it **handles a
submission**. Each phase moves the data between three representations: *model*
(your objects), *normalized* (an intermediate format), and *view* (the strings
and arrays used in HTML). Submission walks the same path in reverse, which is why
the events form two symmetric groups.

**Phase 1: Building and pre-populating**

This phase runs when you call ``createForm()`` and then ``setData()``, either
directly or through ``handleRequest()`` before the form is submitted:

#. ``createForm(TaskType::class, $task)`` creates the form, and ``buildForm()``
   defines its fields.
#. ``FormEvents::PRE_SET_DATA`` fires with the model data, before any
   transformation. The fields are not fully built yet, so this is where you add
   or remove fields based on the object being edited.
#. The data is transformed from the model to the normalized and then the view
   representation (``Data transformed (Model -> Norm -> View)``).
#. ``FormEvents::POST_SET_DATA`` fires once all three representations are set.
   The form is fully populated, so this event reads the final data. You can still
   add or remove fields here, although ``PRE_SET_DATA`` is preferred.
#. The form is ready to render.

**Phase 2: Handling submission**

This phase runs when the user submits the form and ``handleRequest()`` processes
the request:

#. ``FormEvents::PRE_SUBMIT`` fires with the raw request data, as strings and
   arrays. This is where you modify the submitted values or add and remove fields
   based on what the user sent.
#. The view data is reverse-transformed into the normalized representation
   (``Data transformed (View -> Norm)``).
#. ``FormEvents::SUBMIT`` fires with the normalized data. You can change those
   values, but the field structure is locked: you cannot add or remove fields
   from this event onward.
#. The normalized data is reverse-transformed into the model data
   (``Data transformed (Norm -> Model)``).
#. ``FormEvents::POST_SUBMIT`` fires with the fully transformed data. The current
   form's structure is fixed, so dependent fields are added to the parent form
   instead (see :ref:`form-events-nested-forms`).
#. Validation runs through a listener on ``POST_SUBMIT``, so a populated and
   validated object is available once submission completes.

.. _form-events-nested-forms:

Events in Nested Forms
----------------------

Each form in the tree has its own event dispatcher, which is immutable after
the form is built. This means you must register all event listeners during
``buildForm()``; you cannot add listeners later.

When you embed forms within forms, events fire in a specific order. Consider
a ``TaskType`` that embeds a ``CategoryType``:

.. code-block:: text

    TaskType
    └── CategoryType (embedded as 'category' field)

**During pre-population (setData):**

Events fire from parent to children. Setting data in children corresponds to
the ``Data transformed (Model -> Norm -> View)`` step in the
:ref:`lifecycle above <form-events-lifecycle>`:

#. ``TaskType::PRE_SET_DATA``
#. ``CategoryType::PRE_SET_DATA``
#. ``CategoryType::POST_SET_DATA``
#. ``TaskType::POST_SET_DATA``

**During submission:**

The submit phase starts on the parent form. After the parent ``PRE_SUBMIT``,
children are *fully* submitted (including their own ``PRE_SUBMIT``, ``SUBMIT``,
and ``POST_SUBMIT`` events) before the parent continues. This corresponds to
the step before ``Data transformed (View -> Norm)`` in the
:ref:`lifecycle above <form-events-lifecycle>`:

#. ``TaskType::PRE_SUBMIT``
#. ``CategoryType::PRE_SUBMIT``
#. ``CategoryType::SUBMIT``
#. ``CategoryType::POST_SUBMIT``
#. ``TaskType::SUBMIT``
#. ``TaskType::POST_SUBMIT``

This order matters when you need to modify parent forms based on child data.
That's why dependent fields typically listen to ``POST_SUBMIT`` on the child:
by that point, the child's data is fully processed.

**Practical example**: For a Country/State dependent select, listen to
``POST_SUBMIT`` on the Country field to update the State field choices:

#. Country field: ``POST_SUBMIT`` fires
#. Listener reads selected country
#. Listener modifies parent form's ``State`` field
#. Parent form continues submit (``State`` field now has correct choices)

.. _form-events-listeners-vs-subscribers:

Form Event Listeners vs Subscribers
-----------------------------------

Form events are dispatched by the Form component for each form instance. They
are not part of the :doc:`application-wide event system </event_dispatcher>`
used for events like ``kernel.request``. You cannot listen to form events from
a regular event subscriber registered in the service container.

You can register form event handlers in two ways: **listeners** and **subscribers**.

Event Listeners
~~~~~~~~~~~~~~~

Best for simple, form-specific logic. They are defined as inline closures or methods::

    $builder->addEventListener(
        FormEvents::PRE_SET_DATA,
        function (PreSetDataEvent $event): void {
            // ...
        }
    );

    // or reference a method on the form type
    $builder->addEventListener(
        FormEvents::PRE_SET_DATA,
        $this->onPreSetData(...)
    );

Event Subscribers
~~~~~~~~~~~~~~~~~

Best for reusable logic across multiple forms. They are defined in dedicated classes::

    // src/Form/EventSubscriber/AddTimestampFieldsSubscriber.php
    namespace App\Form\EventSubscriber;

    use Symfony\Component\DependencyInjection\Attribute\Exclude;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Form\Event\PreSetDataEvent;
    use Symfony\Component\Form\FormEvents;

    #[Exclude]
    class AddTimestampFieldsSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents(): array
        {
            return [
                FormEvents::PRE_SET_DATA => 'onPreSetData',
            ];
        }

        public function onPreSetData(PreSetDataEvent $event): void
        {
            $form = $event->getForm();

            // add created/updated display fields if entity has timestamps
            $entity = $event->getData();
            if ($entity && method_exists($entity, 'getCreatedAt')) {
                $form->add('createdAt', DateTimeType::class, [
                    'disabled' => true,
                ]);
            }
        }
    }

Then, register it in your form type::

    use App\Form\EventSubscriber\AddTimestampFieldsSubscriber;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            // ...
            ->addEventSubscriber(new AddTimestampFieldsSubscriber())
        ;
    }

.. _form-events-reference:

Event Reference
---------------

.. _form-events-data-summary:
.. _component-form-event-table:

This table summarizes what data you can access in each event explained below:

======================  =============================  ===============
Name                    ``FormEvents`` Constant        Event's Data
======================  =============================  ===============
``form.pre_set_data``   ``FormEvents::PRE_SET_DATA``   Model data
``form.post_set_data``  ``FormEvents::POST_SET_DATA``  Model data
``form.pre_submit``     ``FormEvents::PRE_SUBMIT``     Request data
``form.submit``         ``FormEvents::SUBMIT``         Normalized data
``form.post_submit``    ``FormEvents::POST_SUBMIT``    View data
======================  =============================  ===============

.. _form-events-pre-set-data:

PRE_SET_DATA
~~~~~~~~~~~~

Fires at the start of ``setData()``, before any data transformation.

**When to use:**

* Add or remove fields based on whether the entity is new or existing
* Modify initial data before the form processes it

**What you can access:**

* ``$event->getData()``: The model data (your object or array)
* ``$event->getForm()``: The form instance (fields may not be fully built yet)

**What you can do:**

* Add or remove form fields
* Call ``$event->setData()`` to modify the initial data

**Example**: Show "name" field only for new products::

    use Symfony\Component\Form\Event\PreSetDataEvent;
    use Symfony\Component\Form\FormEvents;

    $builder->addEventListener(
        FormEvents::PRE_SET_DATA,
        function (PreSetDataEvent $event): void {
            $product = $event->getData();
            $form = $event->getForm();

            $isNew = !$product || null === $product->getId();

            if ($isNew) {
                $form->add('name', TextType::class);
            }
        }
    );

.. _form-events-post-set-data:

POST_SET_DATA
~~~~~~~~~~~~~

Fires after ``setData()`` completes and all data transformations finish.

**When to use:**

* Access fully transformed data (model, normalized, and view data are all set)
* Add fields that depend on computed values from transformers

**What you can access:**

* ``$event->getData()``: The model data
* ``$event->getForm()->getNormData()``: The normalized data
* ``$event->getForm()->getViewData()``: The view data

**What you can do:**

* Add or remove form fields (mainly useful for unmapped fields, since mapped
  fields won't receive initial data from the object because the data was already set
  in the previous step; adding fields in ``PRE_SET_DATA`` is generally preferred)
* Set values in unmapped child fields using
  ``$event->getForm()->get('child_name')->setData(...)`` (this is the ideal
  event for this, as the value set here takes precedence over the child's default data)
* Read (but typically don't modify) the data

**Example**: Add a computed summary field::

    use Symfony\Component\Form\Event\PostSetDataEvent;
    use Symfony\Component\Form\FormEvents;

    $builder->addEventListener(
        FormEvents::POST_SET_DATA,
        function (PostSetDataEvent $event): void {
            $order = $event->getData();
            $form = $event->getForm();

            // add a read-only field showing computed total
            if ($order && $order->getItems()->count() > 0) {
                $form->add('totalDisplay', TextType::class, [
                    'mapped' => false,
                    'disabled' => true,
                    'data' => $order->calculateTotal(),
                ]);
            }
        }
    );

.. _form-events-pre-submit:

PRE_SUBMIT
~~~~~~~~~~

Fires at the start of ``submit()`` (called directly or via ``handleRequest()``),
with raw request data.

**When to use:**

* Modify, sanitize, or normalize submitted data before processing
* Add or remove fields based on what the user submitted
* Handle dependent fields (alternative to ``POST_SUBMIT`` on child)

**What you can access:**

* ``$event->getData()``: Raw request data (array of strings, typically)
* ``$event->getForm()``: The form with its current structure

**What you can do:**

* Add or remove form fields
* Call ``$event->setData()`` to modify submitted data

**Example**: Normalize phone number input::

    use Symfony\Component\Form\Event\PreSubmitEvent;
    use Symfony\Component\Form\FormEvents;

    $builder->addEventListener(
        FormEvents::PRE_SUBMIT,
        function (PreSubmitEvent $event): void {
            $data = $event->getData();

            if (isset($data['phone'])) {
                // remove all non-digit characters
                $data['phone'] = preg_replace('/\D/', '', $data['phone']);
                $event->setData($data);
            }
        }
    );

**Example**: Add field based on submitted checkbox::

    use Symfony\Component\Form\Event\PreSubmitEvent;
    use Symfony\Component\Form\FormEvents;

    $builder->addEventListener(
        FormEvents::PRE_SUBMIT,
        function (PreSubmitEvent $event): void {
            $data = $event->getData();
            $form = $event->getForm();

            if (!empty($data['hasDiscount'])) {
                $form->add('discountCode', TextType::class, [
                    'constraints' => [new NotBlank()],
                ]);
            }
        }
    );

.. _form-events-submit:

SUBMIT
~~~~~~

Fires after view-to-norm transformation, before norm-to-model transformation.

**When to use:**

* Modify normalized data (rarely needed; consider :doc:`data transformers </form/data_transformers>`
instead)

**What you can access:**

* ``$event->getData()``: Normalized data (after view transformer)
* ``$event->getForm()``: The form instance

**What you can do:**

* Call ``$event->setData()`` to modify normalized data (this can also be used
  to populate the data of a compound form based on the data submitted in its children)
* You **cannot** add or remove fields

**Example**: Ensure URL has a protocol::

    use Symfony\Component\Form\Event\SubmitEvent;
    use Symfony\Component\Form\FormEvents;

    $builder->get('website')->addEventListener(
        FormEvents::SUBMIT,
        function (SubmitEvent $event): void {
            $url = $event->getData();

            if ($url && !preg_match('~^https?://~i', $url)) {
                $event->setData('https://' . $url);
            }
        }
    );

.. note::

    Symfony's :doc:`UrlType </reference/forms/types/url>` already does this via
    ``FixUrlProtocolListener``. This is just an example of modifying normalized data.

.. _form-events-post-submit:

POST_SUBMIT
~~~~~~~~~~~

Fires after all transformations complete. Validation is triggered by listeners
that run on ``POST_SUBMIT``.

**When to use:**

* Implement dependent fields (listen on the field your new field depends on)
* Perform custom validation or logging
* React to the final submitted state

**What you can access:**

* ``$event->getData()``: View data
* ``$event->getForm()->getData()``: Model data
* ``$event->getForm()->getNormData()``: Normalized data

**What you can do:**

* You **cannot** add or remove fields on the form the listener is attached to
* You **can** add fields to the parent form (used for dependent fields)
* You **can** add custom ``FormError`` instances to the form (useful for validation
  logic that is harder to achieve with constraints, e.g. involving unmapped children)

**Example**: Dependent Country/State select (on child's ``POST_SUBMIT``)::

    use Symfony\Component\Form\Event\PostSubmitEvent;
    use Symfony\Component\Form\FormEvents;

    // listen to the country field's POST_SUBMIT
    $builder->get('country')->addEventListener(
        FormEvents::POST_SUBMIT,
        function (PostSubmitEvent $event): void {
            $country = $event->getForm()->getData();
            $parentForm = $event->getForm()->getParent();

            // add/update the state field on the parent form
            $parentForm->add('state', ChoiceType::class, [
                'choices' => $this->getStatesForCountry($country),
                'placeholder' => 'Select a state',
            ]);
        }
    );

.. warning::

    You cannot modify the form that the listener is attached to during
    ``POST_SUBMIT``. Always modify the parent form instead.

Troubleshooting
---------------

The :doc:`Symfony Profiler </profiler>` is the first tool you should check to
debug any form event issues. You'll see all forms and their fields, submitted
data at each level, validation errors and their sources and the data class and
options for each form.

Field Not Appearing After Submission
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You added the field in ``PRE_SET_DATA`` but not in ``PRE_SUBMIT``. When the form
is submitted, ``PRE_SET_DATA`` runs with the *original* data, not the submitted data.

Add the field in both events, or use ``PRE_SUBMIT`` if the field depends on
submitted values.

"This Form Should Not Contain Extra Fields" Error
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The form structure at submission time doesn't match what was rendered. A field
exists in the submitted data but not in the form.

Ensure fields are added consistently. If you add a field dynamically when
rendering, you must also add it when processing the submission.

Data Transformer Exception During Submit
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A field was added with a data transformer, but the submitted value doesn't match
what the transformer expects.

Check that ``$event->getForm()->getData()`` vs ``$event->getData()`` give you
the right type. On child fields, ``getData()`` returns the client data (usually
a string ID), while ``getForm()->getData()`` returns the transformed object.

Changes in POST_SUBMIT Not Reflected
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You're trying to modify the form that the listener is attached to.
``POST_SUBMIT`` doesn't allow modifications to its own form.

Attach the listener to a child field and modify the parent form.

Checking Form State
~~~~~~~~~~~~~~~~~~~

Use these methods to understand the form's current state::

    // in any event listener
    $form = $event->getForm();

    // is this the root form or a child?
    $form->isRoot();          // true for the main form
    $form->getParent();       // null for root, parent FormInterface otherwise

    // what's the form's name?
    $form->getName();         // e.g., 'task', 'category', etc.

    // has the form been submitted?
    $form->isSubmitted();     // true after handleRequest() processes data

    // is the data synchronized (no transformer errors)?
    $form->isSynchronized();  // false if a transformer threw an exception

Learn More
----------

.. toctree::
    :maxdepth: 1

    /form/dynamic_form_modification
    /form/data_transformers
    /form/data_mappers
