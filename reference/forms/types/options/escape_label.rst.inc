escape_label
~~~~~~~~~~~~
**type**: ``Boolean``  **default**: ``true``

.. versionadded:: 2.1
    The ``escape_label`` option is new in Symfony 2.1.

If set to false, the label's content won't be escaped. This option is useful when wanting to use HTML tags inside a label.

.. code-block:: php

    $builder->add('acceptRules', 'checkbox', array(
        'label'        => '<strong>I accept the rules</strong>',
        'escape_label' => false,
    ));


