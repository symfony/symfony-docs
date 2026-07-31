.. _configuration-framework-secret:

secret
~~~~~~

**type**: ``string`` **required**

This is a string that should be unique to your application and it's commonly
used to add more entropy to security related operations. Its value should
be a series of characters, numbers and symbols chosen randomly and the
recommended length is around 32 characters.

In practice, Symfony uses this value for encrypting the cookies used
in the :doc:`remember me functionality </security/remember_me>` and for
creating signed URIs when using :ref:`ESI (Edge Side Includes) <edge-side-includes>`.
That's why you should treat this value as if it were a sensitive credential and
**never make it public**.

This option becomes the service container parameter named ``kernel.secret``,
which you can use whenever the application needs an immutable random string
to add more entropy.

If ``APP_SECRET`` is not set but the ``SYMFONY_DECRYPTION_SECRET`` environment
variable is available (used by the :doc:`secrets vault </configuration/secrets>`),
the ``kernel.secret`` parameter is automatically derived from it. This means
applications using the secrets vault don't need a separate ``APP_SECRET`` value.

.. versionadded:: 7.2

    The automatic derivation of ``kernel.secret`` from
    ``SYMFONY_DECRYPTION_SECRET`` was introduced in Symfony 7.2.

As with any other security-related parameter, it is a good practice to change
this value from time to time. However, keep in mind that changing this value
will invalidate all signed URIs and Remember Me cookies. That's why, after
changing this value, you should regenerate the application cache and log
out all the application users.
