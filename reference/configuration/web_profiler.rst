.. index::
    single: Configuration reference; WebProfiler

WebProfilerBundle Configuration ("web_profiler")
================================================

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        web_profiler:

            # DEPRECATED, it is not useful anymore and can be removed
            # safely from your configuration
            verbose:              true

            # display the web debug toolbar at the bottom of pages with
            # a summary of profiler info
            toolbar:              false
            position:             bottom

            # gives you the opportunity to look at the collected data
            # before following the redirect
            intercept_redirects: false

            # Exclude AJAX requests in the web debug toolbar for specified paths
            excluded_ajax_paths:  ^/bundles|^/_wdt

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
