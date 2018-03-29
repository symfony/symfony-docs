Forms
=====

Forms are one of the most misused Symfony components due to its vast scope and
endless list of features. In this chapter we'll show you some of the best
practices so you can leverage forms but get work done quickly.

Building Forms
--------------

.. best-practice::

    Define your forms as PHP classes.

The Form component allows you to build forms right inside your controller code.
This is perfectly fine if you don't need to reuse the form somewhere else. But
for organization and reuse, we recommend that you define each form in its own
PHP class::

    namespace App\Form;

    use App\Entity\Post;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

    class PostType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('title')
                ->add('summary', TextareaType::class)
                ->add('content', TextareaType::class)
                ->add('authorEmail', EmailType::class)
                ->add('publishedAt', DateTimeType::class)
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => Post::class,
            ]);
        }
    }

.. best-practice::

    Put the form type classes in the ``App\Form`` namespace, unless you
    use other custom form classes like data transformers.

To use the class, use ``createForm()`` and pass the fully qualified class name::

    // ...
    use App\Form\PostType;

    // ...
    public function new(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        // ...
    }

Form Button Configuration
-------------------------

Form classes should try to be agnostic to *where* they will be used. This
makes them easier to re-use later.

.. best-practice::

    Add buttons in the templates, not in the form classes or the controllers.

The Symfony Form component allows you to add buttons as fields on your form.
This is a nice way to simplify the template that renders your form. But if you
add the buttons directly in your form class, this would effectively limit the
scope of that form::

    class PostType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                // ...
                ->add('save', SubmitType::class, ['label' => 'Create Post'])
            ;
        }

        // ...
    }

This form *may* have been designed for creating posts, but if you wanted
to reuse it for editing posts, the button label would be wrong. Instead,
some developers configure form buttons in the controller::

    namespace App\Controller\Admin;

    use App\Entity\Post;
    use App\Form\PostType;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;

    class PostController extends Controller
    {
        // ...

        public function new(Request $request)
        {
            $post = new Post();
            $form = $this->createForm(PostType::class, $post);
            $form->add('submit', SubmitType::class, [
                'label' => 'Create',
                'attr' => ['class' => 'btn btn-default pull-right'],
            ]);

            // ...
        }
    }

This is also an important error, because you are mixing presentation markup
(labels, CSS classes, etc.) with pure PHP code. Separation of concerns is
always a good practice to follow, so put all the view-related things in the
view layer:

.. code-block:: html+twig

    {{ form_start(form) }}
        {{ form_widget(form) }}

        <input type="submit" class="btn" value="Create" />
    {{ form_end(form) }}

Rendering the Form
------------------

There are a lot of ways to render your form, ranging from rendering the entire
thing in one line to rendering each part of each field independently. The
best way depends on how much customization you need.

One of the simplest ways - which is especially useful during development -
is to render the form tags and use the ``form_widget()`` function to render
all of the fields:

.. code-block:: html+twig

    {{ form_start(form, {attr: {class: 'my-form-class'} }) }}
        {{ form_widget(form) }}
    {{ form_end(form) }}

If you need more control over how your fields are rendered, then you should
remove the ``form_widget(form)`` function and render your fields individually.
See :doc:`/form/form_customization` for more information on this and how you
can control *how* the form renders at a global level using form theming.

As the whole rendering of a form can be passed to a form theme, it can be 
usefull to do something like that: 

.. code-block:: html+twig 

    <!-- templates/random.html.twig -->
    {% form_theme someform 'templates/form/themes/_someform.html.twig' %}

    {{ form(someform) }}

This way, the entire form rendering is passed to the theme where you can
control all field independently. 

Handling Form Submits
---------------------

Handling a form submit usually follows a similar template::

    public function new(Request $request)
    {
        // build the form ...

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('admin_post_show', [
                'id' => $post->getId()
            ]);
        }

        // render the template
    }

We recommend that you use a single action for both rendering the form and
handling the form submit. For example, you *could* have a ``new()`` action that
*only* renders the form and a ``create()`` action that *only* processes the form
submit. Both those actions will be almost identical. So it's much simpler to let
``new()`` handle everything.

If you need to add more control over the form handling process, you can use a ``FormHander``
which is responsible to handling the whole logic between the submission and the rendering part
of the ``new()`` method. 
So, what is a FormHandler ? 

In it's purest expression, a FormHandler is a service which wait for your form and 
call everything you need to manage the handling process, here's a exemple: 

.. code-block:: php

    <?php

    class PostTypeHandler
    { 
        private $entityManager;

        public function __construct(EntityManagerInterface $entityManager)
        {
            $this->entityManager = $entityManager;
        }
        
        public function handle(FormInterface $postType): bool
        { 
            if ($postType->isSubmitted() && $postType->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($post);
                $entityManager->flush();
                
                return true;
            }

            return false;
        } 
    }

Here's a simple exemple of what can do a FormHandler but you can injecte every services
that you need. 
Once in the controller, the logic can be simplified by the call of the PostTypeHandler: 

.. code-block:: php

    public function new(Request $request, PostTypeHandler $handler)
    {
        // build the form ...

        $form->handleRequest($request);

        if ($handler->handle($form)) {
            return $this->redirectToRoute('admin_post_show', [
                'id' => $post->getId()
            ]);
        }

        // render the template
    }

Keep in mind that a FormHandler is just a service, your controller can easily
call it using the ``controler.service_arguments`` tag and let it handle all the 
heavy tasks, this way, your code stay easy to test and maintain. 
Last thing, thanks to the DIC, you can easily define a interface and call it,
this way, you stay in line with the SOLID princiles:

.. code-block:: php

    public function new(Request $request, PostTypeHandlerInterface $handler)
    {
        // build the form ...

        $form->handleRequest($request);

        if ($handler->handle($form)) {
            return $this->redirectToRoute('admin_post_show', [
                'id' => $post->getId()
            ]);
        }

        // render the template
    }

Next: :doc:`/best_practices/i18n`
