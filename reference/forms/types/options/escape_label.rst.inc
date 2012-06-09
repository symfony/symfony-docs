escape_label
~~~~~~~~~~~~
**type**: ``Boolean``  **default**: ``true``

If set to false, the label's content won't be escaped. This option is useful when wanting to use HTML tags inside a label.

.. code-block:: php

    $builder->add('acceptRules', 'checkbox', array(
        'label'        => '<strong>I accept the rules</strong>',
        'escape_label' => false,
    ));


