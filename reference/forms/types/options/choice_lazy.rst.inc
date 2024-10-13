``choice_lazy``
~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

The ``choice_lazy`` option is especially useful when dealing with a large set of
choices, where loading all of them at once could lead to performance issues or
significant delays::

    use App\Entity\User;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;

    $builder->add('user', EntityType::class, [
        'class' => User::class,
        'choice_lazy' => true,
    ]);

When set to ``true``, and used in combination with the ``choice_loader`` option,
the form will only load and render the choices that are preset as default values
or submitted. This allows you to defer loading the full list of choices and can
improve the performance of your form.

.. caution::

    Please note that when using ``choice_lazy``, you are responsible for providing
    the user interface to select choices, typically through a JavaScript plugin that
    can handle the dynamic loading of choices.

.. versionadded:: 7.2

    The ``choice_lazy`` option was introduced in Symfony 7.2.
