.. index::
   single: Configuration Reference; WebProfiler

WebProfilerBundle Configuration
===============================

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        web_profiler:

            # DEPRECATED, it is not useful anymore and can be removed safely from your configuration
            verbose:              true
            toolbar:              false
            position:             bottom
            intercept_redirects:  false