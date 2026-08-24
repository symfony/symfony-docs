``choice_lazy``
~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

The ``choice_lazy`` option is particularly useful when dealing with a large set
of choices, where loading them all at once could cause performance issues or
delays::

    use App\Entity\User;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;

    $builder->add('user', EntityType::class, [
        'class' => User::class,
        'choice_lazy' => true,
    ]);

When set to ``true`` and used alongside the ``choice_loader`` option, the form
will only load and render the choices that are preset as default values or
submitted. This defers the loading of the full list of choices, helping to
improve your form's performance.

.. warning::

    Keep in mind that when using ``choice_lazy``, you are responsible for
    providing the user interface for selecting choices, typically through a
    JavaScript plugin capable of dynamically loading choices.
