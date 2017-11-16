.. index::
    single: Configuration reference; WebProfiler

WebProfilerBundle Configuration ("web_profiler")
================================================

The WebProfilerBundle provides detailed technical information about each request
execution and displays it in both the web debug toolbar and the profiler.

.. caution::

    The web debug toolbar is not available for responses of type ``StreamedResponse``.

Configuration
-------------

* `toolbar`_
* `position`_
* `intercept_redirects`_
* `excluded_ajax_paths`_
* `verbose`_

toolbar
~~~~~~~

**type**: ``boolean`` **default**: ``true``

It enables and disables the toolbar entirely. Usually you set this to ``true``
in the ``dev`` and ``test`` environments and to ``false`` in the ``prod``
environment.

position
~~~~~~~~

.. versionadded:: 3.4
    The ``position`` option was deprecated in Symfony 3.4 and it will be removed
    in Symfony 4.0, where the toolbar is always displayed in the ``bottom`` position.

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

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        web_profiler:
            toolbar:              false
            intercept_redirects:  false
            excluded_ajax_paths:  ^/bundles|^/_wdt

            # DEPRECATED, it can be removed safely from your configuration
            position:             bottom

            # DEPRECATED, it can be removed safely from your configuration
            verbose:              true

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:webprofiler="http://symfony.com/schema/dic/webprofiler"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/webprofiler
                http://symfony.com/schema/dic/webprofiler/webprofiler-1.0.xsd">

            <web-profiler:config
                toolbar="false"
                verbose="true"
                intercept-redirects="false"
                excluded-ajax-paths="^/bundles|^/_wdt"
            />
        </container>
