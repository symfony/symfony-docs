.. index::
   single: Profiling; Profiling data

How to Access Profiling Data Programmatically
=============================================

Most of the times, the profiler information is accessed and analyzed using its
web-based visualizer. However, you can also retrieve profiling information
programmatically thanks to the methods provided by the ``profiler`` service.

When the response object is available, use the
:method:`Symfony\\Component\\HttpKernel\\Profiler\\Profiler::loadProfileFromResponse`
method to access to its associated profile::

    // ... $profiler is the 'profiler' service
    $profile = $profiler->loadProfileFromResponse($response);

When the profiler stores data about a request, it also associates a token with it;
this token is available in the ``X-Debug-Token`` HTTP header of the response.
Using this token, you can access the profile of any past response thanks to the
:method:`Symfony\\Component\\HttpKernel\\Profiler\\Profiler::loadProfile` method::

    $token = $response->headers->get('X-Debug-Token');
    $profile = $container->get('profiler')->loadProfile($token);

.. tip::

    When the profiler is enabled but not the web debug toolbar, inspect the page
    with your browser's developer tools to get the value of the ``X-Debug-Token``
    HTTP header.

The ``profiler`` service also provides the
:method:`Symfony\\Component\\HttpKernel\\Profiler\\Profiler::find` method to
look for tokens based on some criteria::

    // gets the latest 10 tokens
    $tokens = $container->get('profiler')->find('', '', 10, '', '', '');

    // gets the latest 10 tokens for all URL containing /admin/
    $tokens = $container->get('profiler')->find('', '/admin/', 10, '', '', '');

    // gets the latest 10 tokens for local POST requests
    $tokens = $container->get('profiler')->find('127.0.0.1', '', 10, 'POST', '', '');

    // gets the latest 10 tokens for requests that happened between 2 and 4 days ago
    $tokens = $container->get('profiler')
        ->find('', '', 10, '', '4 days ago', '2 days ago');
