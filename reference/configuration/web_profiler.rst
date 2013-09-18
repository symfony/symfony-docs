.. index::
   single: Configuration reference; WebProfiler

WebProfilerBundle Configuration
===============================

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        web_profiler:

            # DEPRECATED, it is not useful anymore and can be removed safely from your configuration
            verbose:              true

            # display the web debug toolbar at the bottom of pages with a summary of profiler info
            toolbar:              false
            position:             bottom

            # gives you the opportunity to look at the collected data before following the redirect
            intercept_redirects: false

    .. code-block:: xml

        <web-profiler:config
            toolbar="false"
            verbose="true"
            intercept_redirects="false"
        />
