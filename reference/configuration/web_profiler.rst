Profiler Configuration Reference (WebProfilerBundle)
====================================================

The WebProfilerBundle is a **development tool** that provides detailed technical
information about each request execution and displays it in both the web debug
toolbar and the :doc:`profiler </profiler>`. All these options are configured
under the ``web_profiler`` key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference web_profiler

    # displays the actual config values used by your application
    $ php bin/console debug:config web_profiler

.. warning::

    The web debug toolbar is not available for responses of type ``StreamedResponse``.

excluded_ajax_paths
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``^/((index|app(_[\w]+)?)\.php/)?_wdt``

When the toolbar logs AJAX requests, it matches their URLs against this regular
expression. If the URL matches, the request is not displayed in the toolbar. This
is useful when the application makes lots of AJAX requests, or if they are heavy
and you want to exclude some of them.

.. _intercept_redirects:

intercept_redirects
~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If a redirect occurs during an HTTP response, the browser follows it automatically
and you won't see the toolbar or the profiler of the original URL, only the
redirected URL.

When setting this option to ``true``, the browser *stops* before making any
redirection and shows you the URL which is going to redirect to, its toolbar,
and its profiler. Once you've inspected the toolbar/profiler data, you can click
on the given link to perform the redirect.

toolbar
~~~~~~~

enabled
.......
**type**: ``boolean`` **default**: ``false``

It enables and disables the toolbar entirely. Usually you set this to ``true``
in the ``dev`` and ``test`` environments and to ``false`` in the ``prod``
environment.

ajax_replace
............
**type**: ``boolean`` **default**: ``false``

If you set this option to ``true``, the toolbar is replaced on AJAX requests.
This only works in combination with an enabled toolbar.
