``label_format``
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

Configures the string used as the label of the field, in case the ``label``
option was not set. This is useful when using
:ref:`keyword translation messages <translation-real-vs-keyword-messages>`.

If you're using keyword translation messages as labels, you often end up having
multiple keyword messages for the same label (e.g. ``profile_address_street``,
``invoice_address_street``). This is because the label is built for each "path"
to a field. To avoid duplicated keyword messages, you can configure the label
format to a static value, like::

    // ...
    $profileFormBuilder->add('address', AddressType::class, [
        'label_format' => 'form.address.%name%',
    ]);

    $invoiceFormBuilder->add('invoice', AddressType::class, [
        'label_format' => 'form.address.%name%',
    ]);

This option is inherited by the child types. With the code above, the label of
the ``street`` field of both forms will use the ``form.address.street`` keyword
message.

Two variables are available in the label format:

``%id%``
    A unique identifier for the field, consisting of the complete path to the
    field and the field name (e.g. ``profile_address_street``);
``%name%``
    The field name (e.g. ``street``).

The default value (``null``) results in a
:ref:`"humanized" version <reference-twig-humanize-filter>` of the field name.

.. note::

    The ``label_format`` option is evaluated in the form theme. Make sure to
    update your templates in case you
    :doc:`customized form theming </form/form_customization>`.
