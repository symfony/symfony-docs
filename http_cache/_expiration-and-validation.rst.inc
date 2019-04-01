.. sidebar:: Expiration and Validation

    You can use both validation and expiration within the same ``Response``.
    As expiration wins over validation, you can benefit from the best of
    both worlds. In other words, by using both expiration and validation, you
    can instruct the cache to serve the cached content, while checking back
    at some interval (the expiration) to verify that the content is still valid.

    .. tip::

        You can also define HTTP caching headers for expiration and validation by using
        annotations. See the `FrameworkExtraBundle documentation`_.

.. _`FrameworkExtraBundle documentation`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/cache.html
