.. index::
    single: Configuration reference; WebProfiler

Profiler Configuration Reference (WebProfilerBundle)
====================================================

The WebProfilerBundle provides detailed technical information about each request
execution and displays it in both the web debug toolbar and the
:doc:`profiler </profiler>`. All these options are configured under the
``web_profiler`` key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php app/console config:dump-reference web_profiler

    # displays the actual config values used by your application
    $ php app/console debug:config web_profiler

.. note::

    When using XML, you must use the ``http://symfony.com/schema/dic/webprofiler``
    namespace and the related XSD schema is available at:
    ``http://symfony.com/schema/dic/webprofiler/webprofiler-1.0.xsd``

.. caution::

    The web debug toolbar is not available for responses of type ``StreamedResponse``.

Configuration
-------------

.. class:: list-config-options

* `excluded_ajax_paths`_
* `intercept_redirects`_
* `position`_
* `toolbar`_
* `verbose`_

toolbar
~~~~~~~

**type**: ``boolean`` **default**: ``false``

It enables and disables the toolbar entirely. Usually you set this to ``true``
in the ``dev`` and ``test`` environments and to ``false`` in the ``prod``
environment.

position
~~~~~~~~

**type**: ``string`` **default**: ``bottom``

It defines the location of the browser window where the toolbar is displayed.
the only allowed values are ``bottom`` and ``top``.

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

excluded_ajax_paths
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``'^/(app(_[\\w]+)?\\.php/)?_wdt'``

When the toolbar logs Ajax requests, it matches their URLs against this regular
expression. If the URL matches, the request is not displayed in the toolbar. This
is useful when the application makes lots of Ajax requests or they are heavy and
you want to exclude some of them.

verbose
~~~~~~~

**type**: ``boolean`` **default**: ``true``

This option is **deprecated** and has no effect on the toolbar or the profiler,
so you can safely remove it from your configuration.
