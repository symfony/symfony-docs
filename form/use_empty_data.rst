How to Configure empty Data for a Form Class
============================================

The ``empty_data`` option allows you to specify an empty data set for your
form class. This empty data set would be used if you submit your form, but
haven't called ``setData()`` on your form or passed in data when you created
your form. For example, in a controller::

    public function index(): Response
    {
        $blog = ...;

        // $blog is passed in as the data, so the empty_data
        // option is not needed
        $form = $this->createForm(BlogType::class, $blog);

        // no data is passed in, so empty_data is
        // used to get the "starting data"
        $form = $this->createForm(BlogType::class);
    }

By default, ``empty_data`` is set to ``null``. Or, if you have specified
a ``data_class`` option for your form class, it will default to a new instance
of that class. That instance will be created by calling the constructor
with no arguments.

If you want to override this default behavior, there are two ways to do this:

* `Option 1: Instantiate a new Class`_
* `Option 2: Provide a Closure`_

If you didn't set the ``data_class`` option, you can pass the initial data as
string or pass an array of strings (where the key matches the field name) when
the form type is compound.

Option 1: Instantiate a new Class
---------------------------------

One reason you might use this option is if you want to use a constructor
that takes arguments. Remember, the default ``data_class`` option calls
that constructor with no arguments::

    // src/Form/Type/BlogType.php
    namespace App\Form\Type;

    // ...
    use App\Entity\Blog;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class BlogType extends AbstractType
    {
        public function __construct(
            private object $someDependency,
        ) {
        }
        // ...

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'empty_data' => new Blog($this->someDependency),
            ]);
        }
    }

You can instantiate your class however you want. In this example, you pass
some dependency into the ``BlogType`` then use that to instantiate the ``Blog`` class.
The point is, you can set ``empty_data`` to the exact "new" object that you want to use.

.. tip::

    In order to pass arguments to the ``BlogType`` constructor, you'll need to
    :ref:`register the form as a service <service-container-creating-service>`
    and :doc:`tag it </service_container/tags>` with ``form.type``.
    If you're using the
    :ref:`default services.yaml configuration <service-container-services-load-example>`,
    this is already done for you.

.. _forms-empty-data-closure:

Option 2: Provide a Closure
---------------------------

Using a closure is the preferred method, since it will only create the object
if it is needed.

The closure must accept a ``FormInterface`` instance as the first argument::

    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    // ...

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'empty_data' => function (FormInterface $form): Blog {
                return new Blog($form->get('title')->getData());
            },
        ]);
    }
