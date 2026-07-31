.. _config-framework-error_controller:

error_controller
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``error_controller``

This is the controller that is called when an exception is thrown anywhere in
your application. The default controller
(:class:`Symfony\\Component\\HttpKernel\\Controller\\ErrorController`)
renders specific templates under different error conditions (see
:doc:`/controller/error_pages`).
