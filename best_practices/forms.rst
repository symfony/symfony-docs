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
scope of that form:

.. code-block:: php

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

Handling Form Submits
---------------------

Handling a form submit usually follows a similar template:

.. code-block:: php

    public function new(Request $request)
    {
        // build the form ...

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

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
