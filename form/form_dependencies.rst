How to Access Services or Config from Inside a Form
===================================================

The content of this article is no longer relevant because in current Symfony
versions, form classes are services by default and you can inject services in
them using the :doc:`service autowiring </service_container/autowiring>` feature.

Read the article about :doc:`creating custom form types </form/create_custom_field_type>`
to see an example of how to inject the database service into a form type. In the
same article you can also read about
:ref:`configuration options for form types <form-type-config-options>`, which is
another way of passing services to forms.
