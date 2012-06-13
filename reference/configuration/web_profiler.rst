.. index::
   single: Configuration reference; WebProfiler

WebProfilerBundle Configuration
===============================

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        web_profiler:
            
            # display secondary information, disable to make the toolbar shorter
            verbose:             true

            # display the web debug toolbar at the bottom of pages with a summary of profiler info
            toolbar:             false

            # gives you the opportunity to look at the collected data before following the redirect
            intercept_redirects: false