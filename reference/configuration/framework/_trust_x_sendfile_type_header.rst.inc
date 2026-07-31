trust_x_sendfile_type_header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``%env(bool:default::SYMFONY_TRUST_X_SENDFILE_TYPE_HEADER)%``

.. versionadded:: 7.2

    In Symfony 7.2, the default value of this option was changed from ``false`` to the
    value stored in the ``SYMFONY_TRUST_X_SENDFILE_TYPE_HEADER`` environment variable.

``X-Sendfile`` is a special HTTP header that tells web servers to replace the
response contents by the file that is defined in that header. This improves
performance because files are no longer served by your application but directly
by the web server.

This configuration option determines whether to trust ``x-sendfile`` header for
BinaryFileResponse. If enabled, Symfony calls the
:method:`BinaryFileResponse::trustXSendfileTypeHeader <Symfony\\Component\\HttpFoundation\\BinaryFileResponse::trustXSendfileTypeHeader>`
method automatically. It becomes the service container parameter named
``kernel.trust_x_sendfile_type_header``.
