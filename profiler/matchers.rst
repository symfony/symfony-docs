.. index::
    single: Profiling; Matchers

How to Use Matchers to Enable the Profiler Conditionally
========================================================

.. caution::

    The possibility to use a matcher to enable the profiler conditionally was
    removed in Symfony 4.0.

Symfony Profiler cannot be enabled/disabled conditionally using matchers, because
that feature was removed in Symfony 4.0. However, you can use the ``enable()``
and ``disable()`` methods of the :class:`Symfony\\Component\\HttpKernel\\Profiler\\Profiler`
class in your controllers to manage the profiler programmatically::

    use Symfony\Component\HttpKernel\Profiler\Profiler;
    // ...

    class DefaultController
    {
        // ...

        public function someMethod(Profiler $profiler)
        {
            // for this particular controller action, the profiler is disabled
            $profiler->disable();

            // ...
        }
    }
