disallow_search_engine_index
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true`` when the debug mode is enabled, ``false`` otherwise.

If ``true``, Symfony adds a ``X-Robots-Tag: noindex`` HTTP tag to all responses
(unless your own app adds that header, in which case it's not modified). This
`X-Robots-Tag HTTP header`_ tells search engines to not index your web site.
This option is a protection measure in case you accidentally publish your site
in debug mode.

.. _`X-Robots-Tag HTTP header`: https://developers.google.com/search/reference/robots_meta_tag
