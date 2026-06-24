How to Dynamically Modify Forms Using Form Events
=================================================

This article shows practical examples of using :doc:`form events </form/events>`
to create dynamic forms. Each example includes the complete code you need.

If you're new to form events, read the :doc:`form events documentation </form/events>`
first to understand when to use each event and how they work.

.. _form-events-underlying-data:

Showing Fields Only for New Entities
------------------------------------

A common pattern is showing certain fields only when creating a new entity,
but hiding them when editing. For example, you might allow setting a username
during user registration or setting a SKU during product creation but prevent
changes afterward.

Use ``PRE_SET_DATA`` to check if the entity has an ID (is persisted) or not (is new)::

    // src/Form/ProductType.php
    namespace App\Form;

    use App\Entity\Product;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Event\PreSetDataEvent;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('description')
                ->add('price')
            ;

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (PreSetDataEvent $event): void {
                    $product = $event->getData();
                    $form = $event->getForm();

                    // product is new if there's no data or no ID
                    $isNew = !$product || null === $product->getId();

                    if ($isNew) {
                        // SKU can only be set when creating, not editing
                        $form->add('sku', TextType::class, [
                            'help' => 'Cannot be changed after creation',
                        ]);
                    }
                }
            );
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Product::class,
            ]);
        }
    }

.. tip::

    For simple cases like this, consider using form options instead::

        $form = $this->createForm(ProductType::class, $product, [
            'is_new' => null === $product->getId(),
        ]);

        // then in buildForm(), check $options['is_new']

    Form options are simpler but less flexible than events.

.. _form-events-submitted-data:

Dependent Selects (Country/State Pattern)
-----------------------------------------

The most common use case for form events is dependent selects, where one
field's choices depend on another field's value. The classic example is
Country and State/Province fields.

This requires handling two scenarios:

#. **Initial render**: Populate the State field based on the entity's current
   Country value
#. **Form submission**: Update the State field based on the submitted Country
   value

Here's the complete implementation::

    // src/Form/AddressType.php
    namespace App\Form;

    use App\Entity\Address;
    use App\Entity\Country;
    use App\Entity\State;
    use App\Repository\StateRepository;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Event\PostSetDataEvent;
    use Symfony\Component\Form\Event\PostSubmitEvent;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class AddressType extends AbstractType
    {
        public function __construct(
            private StateRepository $stateRepository,
        ) {
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('street')
                ->add('city')
                ->add('country', EntityType::class, [
                    'class' => Country::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Select a country',
                ])
            ;

            // this closure adds the State field with appropriate choices
            $addStateField = function (FormInterface $form, ?Country $country): void {
                $states = null === $country
                    ? []
                    : $this->stateRepository->findByCountry($country);

                $form->add('state', EntityType::class, [
                    'class' => State::class,
                    'choices' => $states,
                    'choice_label' => 'name',
                    'placeholder' => null === $country
                        ? 'Select country first'
                        : ([] === $states ? 'No states available' : 'Select a state'),
                ]);
            };

            // 1) handle initial render: add State based on entity's Country
            $builder->addEventListener(
                FormEvents::POST_SET_DATA,
                function (PostSetDataEvent $event) use ($addStateField): void {
                    $address = $event->getData();
                    $country = $address?->getCountry();

                    $addStateField($event->getForm(), $country);
                }
            );

            // 2) handle submission: update State when Country changes:
            // listen to Country field's POST_SUBMIT (after Country is processed)
            $builder->get('country')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (PostSubmitEvent $event) use ($addStateField): void {
                    // get the selected Country entity (not the raw ID)
                    $country = $event->getForm()->getData();

                    // add State field to the PARENT form
                    $addStateField($event->getForm()->getParent(), $country);
                }
            );
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Address::class,
            ]);
        }
    }

.. note::

    The key is to listen to ``POST_SUBMIT`` on the Country field (child), then
    modify the State field on the parent form. You cannot modify the form that
    fired the event, but you can modify its parent.

Adding JavaScript for Dynamic Updates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The PHP code above handles form submission correctly. For a better user
experience, add JavaScript to update the State field immediately when the
user changes the Country:

.. code-block:: html+twig

    {# templates/address/form.html.twig #}
    {{ form_start(form, {attr: {id: 'address-form'}}) }}
        {{ form_row(form.street) }}
        {{ form_row(form.city) }}
        {{ form_row(form.country) }}
        {{ form_row(form.state) }}
    {{ form_end(form) }}

    <script>
    const form = document.getElementById('address-form');
    const countrySelect = document.getElementById('address_country');
    const stateSelect = document.getElementById('address_state');

    countrySelect.addEventListener('change', async function() {
        // submit just the country field to get updated state options
        const formData = new FormData();
        formData.append(this.name, this.value);

        const response = await fetch(form.action, {
            method: form.method,
            body: new URLSearchParams(formData),
        });

        // parse the response HTML and extract the new state options
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newStateSelect = doc.getElementById('address_state');

        stateSelect.innerHTML = newStateSelect.innerHTML;
    });
    </script>

This reuses your existing form processing logic on the server. No additional
endpoints needed.

.. tip::

    For a more polished solution, consider using `Symfony UX Live Component`_
    which handles dependent fields automatically without custom JavaScript.

.. _form-events-conditional-fields:

Conditional Fields Based on Checkboxes
--------------------------------------

Show additional fields when a checkbox is checked. This requires handling
both the initial render and submission::

    // src/Form/OrderType.php
    namespace App\Form;

    use App\Entity\Order;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Event\PostSetDataEvent;
    use Symfony\Component\Form\Event\PreSubmitEvent;
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Validator\Constraints\NotBlank;

    class OrderType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('product')
                ->add('isGift', CheckboxType::class, [
                    'required' => false,
                    'label' => 'This is a gift',
                ])
            ;

            $addGiftFields = function (FormInterface $form, bool $isGift): void {
                if ($isGift) {
                    $form->add('giftRecipient', TextType::class, [
                        'label' => 'Recipient name',
                        'constraints' => [new NotBlank()],
                    ]);
                    $form->add('giftMessage', TextareaType::class, [
                        'required' => false,
                        'label' => 'Gift message',
                    ]);
                }
            };

            // 1) handle initial render
            $builder->addEventListener(
                FormEvents::POST_SET_DATA,
                function (PostSetDataEvent $event) use ($addGiftFields): void {
                    $order = $event->getData();
                    $isGift = $order?->isGift() ?? false;

                    $addGiftFields($event->getForm(), $isGift);
                }
            );

            // 2) handle submission
            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (PreSubmitEvent $event) use ($addGiftFields): void {
                    // raw submitted data (array of strings)
                    $data = $event->getData();
                    $isGift = isset($data['isGift']) && $data['isGift'];

                    $addGiftFields($event->getForm(), $isGift);
                }
            );
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Order::class,
            ]);
        }
    }

.. note::

    Notice that ``PRE_SUBMIT`` uses raw request data (array), while
    ``POST_SET_DATA`` uses the entity. The checkbox value in raw data is a
    string ``"1"`` or not present, not a boolean. Use ``POST_SET_DATA``
    (not ``PRE_SET_DATA``) when modifying the form structure based on data;
    ``PRE_SET_DATA`` is meant for modifying the data itself via ``$event->setData()``.

.. _form-events-dynamic-validation:

Adding Different Fields Based on a Selection
--------------------------------------------

Sometimes the fields to display depend on the value of another field (e.g.
different payment methods require different details). Use ``PRE_SUBMIT`` to
add the right fields based on the submitted value::

    // src/Form/PaymentType.php
    namespace App\Form;

    use App\Entity\Payment;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Event\PreSubmitEvent;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Validator\Constraints as Assert;

    class PaymentType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder->add('method', ChoiceType::class, [
                'choices' => [
                    'Credit Card' => 'card',
                    'Bank Transfer' => 'bank',
                    'PayPal' => 'paypal',
                ],
                'placeholder' => 'Choose payment method',
            ]);

            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (PreSubmitEvent $event): void {
                    $data = $event->getData();
                    $form = $event->getForm();
                    $method = $data['method'] ?? null;

                    match ($method) {
                        'card' => $form
                            ->add('cardNumber', TextType::class, [
                                'constraints' => [
                                    new Assert\NotBlank(),
                                    new Assert\Luhn(), // checks the credit card number
                                ],
                            ])
                            ->add('cardExpiry', TextType::class, [
                                'constraints' => [new Assert\NotBlank()],
                            ]),
                        'bank' => $form->add('iban', TextType::class, [
                            'constraints' => [
                                new Assert\NotBlank(),
                                new Assert\Iban(),
                            ],
                        ]),
                        'paypal' => $form->add('paypalEmail', TextType::class, [
                            'constraints' => [new Assert\NotBlank()],
                        ]),
                        default => null,
                    };
                }
            );
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Payment::class,
            ]);
        }
    }

.. tip::

    If you only need different *validation rules* (not different fields) based
    on a value, use the :doc:`When constraint </reference/constraints/When>` instead.

.. _form-events-preprocessing-data:

Preprocessing Submitted Data
----------------------------

Use ``PRE_SUBMIT`` to normalize or sanitize user input before the form
processes it::

    // src/Form/ContactType.php
    namespace App\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Event\PreSubmitEvent;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\Extension\Core\Type\TelType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;

    class ContactType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('name', TextType::class)
                ->add('email', EmailType::class)
                ->add('phone', TelType::class, ['required' => false])
                ->add('message', TextareaType::class)
            ;

            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (PreSubmitEvent $event): void {
                    $data = $event->getData();

                    // normalize phone: keep only digits
                    if (!empty($data['phone'])) {
                        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
                    }

                    // normalize email: lowercase
                    if (!empty($data['email'])) {
                        $data['email'] = strtolower(trim($data['email']));
                    }

                    // clean message: normalize whitespace
                    if (!empty($data['message'])) {
                        $data['message'] = preg_replace('/\s+/', ' ', trim($data['message']));
                    }

                    $event->setData($data);
                }
            );
        }
    }

.. _form-events-external-services:

Populating Fields from External Services
----------------------------------------

Fetch data from APIs or services to populate form fields. Use ``PRE_SET_DATA``
to load data when the form is built::

    // src/Form/ProfileType.php
    namespace App\Form;

    use App\Entity\Profile;
    use App\Service\GeocodingService;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Event\PreSetDataEvent;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class ProfileType extends AbstractType
    {
        public function __construct(
            private GeocodingService $geocoding,
        ) {
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('name', TextType::class)
                ->add('postalCode', TextType::class)
            ;

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (PreSetDataEvent $event): void {
                    $profile = $event->getData();
                    $form = $event->getForm();

                    // fetch cities based on current postal code
                    $postalCode = $profile?->getPostalCode();
                    $cities = $postalCode
                        ? $this->geocoding->getCitiesForPostalCode($postalCode)
                        : [];

                    $form->add('city', ChoiceType::class, [
                        'choices' => array_combine($cities, $cities),
                        'placeholder' => $cities ? 'Select a city' : 'Enter postal code first',
                    ]);
                }
            );
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Profile::class,
            ]);
        }
    }

.. warning::

    Be careful with external service calls in form events. They run on every
    form render and submission, which can cause performance issues.

.. _form-events-reusable-subscriber:

Creating Reusable Event Subscribers
-----------------------------------

If you use the same event logic across multiple forms, create an event
subscriber class::

    // src/Form/EventSubscriber/TimestampFieldsSubscriber.php
    namespace App\Form\EventSubscriber;

    use Symfony\Component\DependencyInjection\Attribute\Exclude;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Form\Event\PreSetDataEvent;
    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
    use Symfony\Component\Form\FormEvents;

    /**
     * Adds read-only createdAt/updatedAt fields for entities with timestamps.
     *
     * The #[Exclude] attribute prevents this class from being registered as a
     * service in the container. Form event subscribers should only be attached
     * to forms (via addEventSubscriber()), not the kernel event dispatcher.
     */
    #[Exclude]
    class TimestampFieldsSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents(): array
        {
            return [
                FormEvents::PRE_SET_DATA => 'addTimestampFields',
            ];
        }

        public function addTimestampFields(PreSetDataEvent $event): void
        {
            $entity = $event->getData();
            $form = $event->getForm();

            // skip for new entities
            if (!$entity || !method_exists($entity, 'getCreatedAt')) {
                return;
            }

            if (null !== $entity->getCreatedAt()) {
                $form->add('createdAt', DateTimeType::class, [
                    'disabled' => true,
                    'label' => 'Created',
                ]);
            }

            if (method_exists($entity, 'getUpdatedAt') && null !== $entity->getUpdatedAt()) {
                $form->add('updatedAt', DateTimeType::class, [
                    'disabled' => true,
                    'label' => 'Last modified',
                ]);
            }
        }
    }

Use it in any form type::

    use App\Form\EventSubscriber\TimestampFieldsSubscriber;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            // ...
            ->addEventSubscriber(new TimestampFieldsSubscriber())
        ;
    }

If your subscriber needs dependencies, inject them into the form type and pass
them to the subscriber. This keeps the subscriber excluded from the container
(avoiding unwanted auto-configuration as a kernel event subscriber)::

    // src/Form/EventSubscriber/AuditFieldsSubscriber.php
    namespace App\Form\EventSubscriber;

    use App\Repository\UserRepository;
    use Symfony\Component\DependencyInjection\Attribute\Exclude;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Form\Event\PreSetDataEvent;
    use Symfony\Component\Form\FormEvents;

    #[Exclude]
    class AuditFieldsSubscriber implements EventSubscriberInterface
    {
        public function __construct(
            private UserRepository $userRepository,
        ) {
        }

        public static function getSubscribedEvents(): array
        {
            return [FormEvents::PRE_SET_DATA => 'addAuditFields'];
        }

        public function addAuditFields(PreSetDataEvent $event): void
        {
            // use $this->userRepository to fetch user names for audit display
            // ...
        }
    }

Then inject the dependencies into your form type and instantiate the subscriber::

    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ...
            ->addEventSubscriber(new AuditFieldsSubscriber($this->userRepository))
        ;
    }

.. _`Symfony UX Live Component`: https://ux.symfony.com/live-component
