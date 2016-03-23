choice_label
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_label`` option was introduced in Symfony 2.7.

**type**: ``string``, ``callable``, :class:``Symfony\\Component\\PropertyAccess\\PropertyPath`` or ``false`` **default**: ``null``

Normally, the array key of each item in the ``choices`` option is used as the
text that's shown to the user. The ``choice_label`` option allows you to take
more control.

If your choice values are objects (default in ``EntityType``), then ``choice_label``
can be a string :ref:`property path <reference-form-option-property-path>`. Imagine you have some
``Status`` class with a ``getDisplayName()`` method::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            new Status(Status::YES),
            new Status(Status::NO),
            new Status(Status::MAYBE),
        ),
        'choices_as_values' => true,
        'choice_label' => 'displayName',
    ));

If set as a callable is called for each choice, passing you its model data ``$choice``
and the ``$key`` from the choices array (default label). ``$value`` is either
`choice_value`_ option value when defined or the default string value of the choice::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'yes' => true,
            'no' => false,
            'maybe' => null,
        ),
        'choices_as_values' => true,
        'choice_label' => function ($choice, $key, $value) {
            if (true === $choice) {
                return 'Definitely!';
            }
            return strtoupper($key);

            // or if you want to translate some key
            //return 'form.choice.'.$key;
        },
    ));

The example above would output:

.. image:: /images/reference/form/choice-example2.png
   :align: center

If set to ``false``, all the tag labels will be discarded for radio or checkbox
inputs. You can also return ``false`` from the callable to discard certain labels.

.. caution::

    If you want to pass a string property path wich is also a callable (e.g 'range'),
    the component will treat it as a callable. You should pass a :class:``Symfony\\Component\\PropertyAccess\\PropertyPath``
    object to ensure the expected behavior::

        use Symfony\Component\PropertyAccess\PropertyPath;

        'choice_label' => new PropertyPath('range'),
