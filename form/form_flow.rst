Multi-Step Forms
================

Symfony provides support for building multi-step forms via the ``FormFlow``
system. A form flow splits a form into multiple steps while keeping a single
form instance, shared data, validation groups, and navigation controls.

A form flow is implemented as a custom form type that defines:

* the ordered list of steps
* the storage strategy for the current step
* the navigation behavior between steps
* the persistence strategy for data between requests

Creating a Multi-Step Form
--------------------------

A form flow is created by extending ``AbstractFlowType`` and implementing
``buildFormFlow()``::

    use Symfony\Component\Form\Flow\AbstractFlowType;
    use Symfony\Component\Form\Flow\FormFlowBuilderInterface;
    use Symfony\Component\Form\Flow\Type\NavigatorFlowType;

    class UserSignUpType extends AbstractFlowType
    {
        public function buildFormFlow(FormFlowBuilderInterface $builder, array $options): void
        {
            $builder->addStep('personal', PersonalType::class);
            $builder->addStep('account', AccountType::class);
            $builder->addStep('confirmation', ConfirmationType::class);

            $builder->add('navigator', NavigatorFlowType::class);
        }
    }

Each step corresponds to a form type. Only the form of the current step
is rendered and processed.

.. note::

    :doc:`Validation groups </form/validation_groups>` are automatically scoped
    to the current step. By default, the active groups are ``['Default', '<current_step_name>']``,
    which allows you to define step-specific constraints on your data object.

Storing the Current Step
------------------------

A form flow needs to know which step is currently active. By default,
this is done via a property path on the form data. Configure the ``step_property_path``
option in ``configureOptions()``::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'step_property_path' => 'currentStep',
        ]);
    }

The property is automatically updated when the user moves between steps.

Processing a Multi-Step Form
----------------------------

To use a form flow in a controller, create the form, handle the request,
and check the flow state to determine what to do next::

    // src/Controller/SignUpController.php
    namespace App\Controller;

    use App\Entity\User;
    use App\Form\UserSignUpType;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class SignUpController extends AbstractController
    {
        #[Route('/signup', name: 'signup')]
        public function signup(Request $request): Response
        {
            $flow = $this->createForm(UserSignUpType::class, new User());
            $flow->handleRequest($request);

            if ($flow->isSubmitted() && $flow->isValid() && $flow->isFinished()) {
                // all steps are completed; process the final data
                $data = $flow->getData();

                return $this->redirectToRoute('signup_success');
            }

            return $this->render('signup.html.twig', [
                'form' => $flow->getStepForm(),
            ]);
        }
    }

After calling ``handleRequest()``, the flow knows which button was clicked
(next, previous, finish, or reset) and acts accordingly.

.. warning::

    ``getStepForm()`` both transitions the flow to the next step *and*
    returns the form for that step. Call it only once per request, and
    only after all state checks (``isSubmitted()``, ``isValid()``,
    ``isFinished()``) are complete. Calling it multiple times will advance
    the step counter more than once.

When ``isFinished()`` returns ``true``, the data object returned by
``getData()`` contains all accumulated data from every step.

Step Ordering and Priority
~~~~~~~~~~~~~~~~~~~~~~~~~~

Steps are ordered by priority (higher first). If no priority is defined,
steps are ordered by insertion order::

    $builder->addStep('first', FirstType::class, priority: 10);
    $builder->addStep('second', SecondType::class);

Skipping Steps
~~~~~~~~~~~~~~

Steps can be conditionally skipped using a callable::

    $builder->addStep(
        'professional',
        ProfessionalType::class,
        skip: fn (User $data) => !$data->isWorker()
    );

Skipped steps are not rendered and are ignored during navigation.

Persisting Data Between Requests
--------------------------------

Form data is persisted between requests using a data storage. Symfony
provides several implementations:

* ``SessionDataStorage``: stores data in the session (used by default)
* ``InMemoryDataStorage``: stores data in memory (mainly for tests)
* ``NullDataStorage``: does not persist data

.. note::

    ``SessionDataStorage`` requires an active HTTP session.

To configure a data storage explicitly::

    use Symfony\Component\Form\Flow\DataStorage\SessionDataStorage;

    $resolver->setDefaults([
        'data_storage' => new SessionDataStorage('signup_flow', $requestStack),
    ]);

Multi-Step Form Navigation
--------------------------

Navigation is handled via special submit button types:

* ``PreviousFlowType``: goes back to the previous step
* ``NextFlowType``: advances to the next step, validating the current one
* ``FinishFlowType``: submits the final step and marks the flow as finished
* ``ResetFlowType``: clears all stored data and returns to the first step

Symfony provides a default navigator that includes previous, next, and
finish buttons::

    use Symfony\Component\Form\Flow\Type\NavigatorFlowType;

    $builder->add('navigator', NavigatorFlowType::class);

Buttons are automatically shown or hidden depending on the current step.

To also include a reset button, use the ``with_reset`` option::

    $builder->add('navigator', NavigatorFlowType::class, [
        'with_reset' => true,
    ]);

.. versionadded:: 8.1

    The ``with_reset`` option was introduced in Symfony 8.1.

Custom Navigation Buttons
~~~~~~~~~~~~~~~~~~~~~~~~~

You can define custom navigation buttons independently of the default navigator::

    use Symfony\Component\Form\Flow\Type\NextFlowType;

    $builder->add('skip', NextFlowType::class, [
        'clear_submission' => true,
        'validate' => false,
        'validation_groups' => [],
        'include_if' => ['professional'],
    ]);

Each button type accepts options that control validation behavior, which
steps it appears on (``include_if``), and whether the current submission
data is cleared (``clear_submission``).

Finishing and Resetting the Flow
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When the ``finish`` button is clicked, the flow is marked as finished, the data
storage is cleared (if ``auto_reset`` is enabled), and the cursor is reset to
the first step. Use ``$form->isFinished()`` to check whether the flow has completed.

.. _reference-forms-twig-form-flow:

Rendering Navigation Information
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides a set of ``form_flow_*()`` Twig functions to read the state of
the flow inside templates. They are useful to build progress indicators, step
lists and navigation menus. All of them take a ``FormView`` as their only argument:

* ``form_flow_current_step()``, ``form_flow_first_step()``,
  ``form_flow_last_step()``, ``form_flow_next_step()`` and
  ``form_flow_previous_step()`` return a step name as a string;
* ``form_flow_steps()`` returns the list of all step names;
* ``form_flow_total_steps()`` returns the number of steps and
  ``form_flow_step_index()`` the zero-based index of the current step;
* ``form_flow_is_first_step()``, ``form_flow_is_last_step()``,
  ``form_flow_can_move_back()`` and ``form_flow_can_move_next()`` return a boolean.

The ``form_flow_next_step()`` and ``form_flow_previous_step()`` methods return
``null`` on the last and first steps respectively. When the given form is not
part of a flow, all functions return ``null``, except the ``is_*()`` and
``can_*()`` ones, which return ``false``.

The following example displays the current progress and the list of steps:

.. code-block:: html+twig

    <p>
        Step {{ form_flow_step_index(form) + 1 }}
        of {{ form_flow_total_steps(form) }}:
        {{ form_flow_current_step(form) }}
    </p>

    <ol>
        {% for step in form_flow_steps(form) %}
            <li class="{{ step == form_flow_current_step(form) ? 'active' : '' }}">
                {{ step }}
            </li>
        {% endfor %}
    </ol>

    {{ form_start(form) }}
        {# this also renders the navigator buttons (previous, next, finish) #}
        {{ form_widget(form) }}
    {{ form_end(form) }}

.. versionadded:: 8.1

    The ``form_flow_*()`` Twig functions were introduced in Symfony 8.1.

.. warning::

    Don't use ``form_flow_can_move_back()`` and ``form_flow_can_move_next()`` to
    render your own ``<button>`` elements. Symfony only detects clicks on the
    buttons created by the flow button types (e.g. the ones added by the
    navigator), so plain HTML buttons won't move the flow.

Instead of ``form_flow_steps()``, which returns all the steps defined in the
flow, use the ``visible_steps`` variable to exclude the steps skipped for the
current data:

.. code-block:: html+twig

    <ol>
        {% for step in form.vars.visible_steps %}
            <li class="{{ step.is_current_step ? 'active' : '' }}">
                {{ step.name }}
            </li>
        {% endfor %}
    </ol>

Each step object exposes the following properties:

* ``name``
* ``index``
* ``position``
* ``is_current_step``
* ``is_skipped``
* ``can_be_skipped``

Using Multi-Step Forms with Turbo
---------------------------------

When `Symfony UX Turbo`_ is enabled, Turbo Drive intercepts form submissions via
AJAX and relies on HTTP status codes to decide how to handle the response.
Without returning the correct codes, Turbo will receive the response but will
not render the new step.

The expected status codes are:

* ``200``: initial rendering (form not yet submitted)
* ``422``: form submitted but invalid (Turbo re-renders the form in place)
* ``303``: form submitted and valid, advancing to the next step (Turbo follows the redirect)

The following example shows a controller that handles all three cases::

    // src/Controller/SignUpController.php
    namespace App\Controller;

    use App\Entity\User;
    use App\Form\UserSignUpType;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class SignUpController extends AbstractController
    {
        #[Route('/signup', name: 'signup')]
        public function signup(Request $request): Response
        {
            $flow = $this->createForm(UserSignUpType::class, new User());
            $flow->handleRequest($request);

            if ($flow->isSubmitted() && $flow->isValid() && $flow->isFinished()) {
                // all steps are completed; process the final data
                $data = $flow->getData();

                return $this->redirectToRoute('signup_success');
            }

            // At this point, the flow is either not yet submitted,
            // submitted but invalid, or submitted, valid, and not yet finished.
            $statusCode = match (true) {
                !$flow->isSubmitted() => Response::HTTP_OK,
                !$flow->isValid() => Response::HTTP_UNPROCESSABLE_ENTITY,
                default => Response::HTTP_SEE_OTHER,
            };

            return $this->render('signup.html.twig', [
                'form' => $flow->getStepForm(),
            ], new Response(status: $statusCode));
        }
    }

Known Limitations
-----------------

* **Nested form flows** are not supported; a flow step cannot itself be a flow.
* **One step per request**: only the active step's form is processed on each
  request. Submitting multiple steps in a single request is not possible.
* **Steps must be declared explicitly** in ``buildFormFlow()``. Dynamic or
  runtime-generated steps are not supported.

.. _`Symfony UX Turbo`: https://symfony.com/bundles/ux-turbo/current/index.html
