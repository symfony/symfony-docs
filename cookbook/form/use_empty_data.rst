.. index::
   single: Form; Empty data

How to configure Empty Data for a Form Class
============================================

The ``empty_data`` option allows you to specify an empty data set for your
form class. This empty data set would be used if you submit your form, but
haven't called ``setData()`` on your form or passed in data when you created
you form. For example::

    public function indexAction()
    {
        $blog = ...;

        // $blog is passed in as the data, so the empty_data option is not needed
        $form = $this->createForm(new BlogType(), $blog);

        // no data is passed in, so empty_data is used to get the "starting data"
        $form = $this->createForm(new BlogType());
    }

By default, ``empty_data`` is set to ``null``. Or, if you have specified
a ``data_class`` option for your form class, it will default to a new instance
of that class. That instance will be created by calling the constructor
with no arguments.

If you want to override this default behavior, there are two ways to do this.

Option 1: Instantiate a new Class
---------------------------------

One reason you might use this option is if you want to use a constructor
that takes arguments. Remember, the default ``data_class`` option calls
that constructor with no arguments::

    // src/Acme/DemoBundle/Form/Type/BlogType.php

    // ...
    use Symfony\Component\Form\AbstractType;
    use Acme\DemoBundle\Entity\Blog;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class BlogType extends AbstractType
    {
        private $someDependency;

        public function __construct($someDependency)
        {
            $this->someDependency = $someDependency;
        }
        // ...

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'empty_data' => new Blog($this->someDependency),
            ));
        }
    }

You can instantiate your class however you want. In this example, we pass
some dependency into the ``BlogType`` when we instantiate it, then use that
to instantiate the ``Blog`` object. The point is, you can set ``empty_data``
to the exact "new" object that you want to use.

Option 2: Provide a Closure
---------------------------

Using a closure is the preferred method, since it will only create the object
if it is needed.

The closure must accept a ``FormInterface`` instance as the first argument::

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Symfony\Component\Form\FormInterface;
    // ...

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'empty_data' => function (FormInterface $form) {
                return new Blog($form->get('title')->getData());
            },
        ));
    }
