.. index::
   single: Profiling; Data collector

How to create a custom Data Collector
=====================================

The Symfony2 :ref:`Profiler <internals-profiler>` delegates data collecting to
data collectors. Symfony2 comes bundled with a few of them, but you can easily
create your own.

Creating a Custom Data Collector
--------------------------------

Creating a custom data collector is as simple as implementing the
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface`::

    interface DataCollectorInterface
    {
        /**
         * Collects data for the given Request and Response.
         *
         * @param Request    $request   A Request instance
         * @param Response   $response  A Response instance
         * @param \Exception $exception An Exception instance
         */
        function collect(Request $request, Response $response, \Exception $exception = null);

        /**
         * Returns the name of the collector.
         *
         * @return string The collector name
         */
        function getName();
    }

The ``getName()`` method must return a unique name. This is used to access the
information later on (see :doc:`/cookbook/testing/profiling` for
instance).

The ``collect()`` method is responsible for storing the data it wants to give
access to in local properties.

.. caution::

    As the profiler serializes data collector instances, you should not
    store objects that cannot be serialized (like PDO objects), or you need
    to provide your own ``serialize()`` method.

Most of the time, it is convenient to extend
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector` and
populate the ``$this->data`` property (it takes care of serializing the
``$this->data`` property)::

    class MemoryDataCollector extends DataCollector
    {
        public function collect(Request $request, Response $response, \Exception $exception = null)
        {
            $this->data = array(
                'memory' => memory_get_peak_usage(true),
            );
        }

        public function getMemory()
        {
            return $this->data['memory'];
        }

        public function getName()
        {
            return 'memory';
        }
    }

.. _data_collector_tag:

Enabling Custom Data Collectors
-------------------------------

To enable a data collector, add it as a regular service in one of your
configuration, and tag it with ``data_collector``:

.. configuration-block::

    .. code-block:: yaml

        services:
            data_collector.your_collector_name:
                class: Fully\Qualified\Collector\Class\Name
                tags:
                    - { name: data_collector }

    .. code-block:: xml

        <service id="data_collector.your_collector_name" class="Fully\Qualified\Collector\Class\Name">
            <tag name="data_collector" />
        </service>

    .. code-block:: php

        $container
            ->register('data_collector.your_collector_name', 'Fully\Qualified\Collector\Class\Name')
            ->addTag('data_collector')
        ;

Adding Web Profiler Templates
-----------------------------

When you want to display the data collected by your Data Collector in the web
debug toolbar or the web profiler, create a Twig template following this
skeleton:

.. code-block:: jinja

    {% extends 'WebProfilerBundle:Profiler:layout.html.twig' %}

    {% block toolbar %}
        {# the web debug toolbar content #}
    {% endblock %}

    {% block head %}
        {# if the web profiler panel needs some specific JS or CSS files #}
    {% endblock %}

    {% block menu %}
        {# the menu content #}
    {% endblock %}

    {% block panel %}
        {# the panel content #}
    {% endblock %}

Each block is optional. The ``toolbar`` block is used for the web debug
toolbar and ``menu`` and ``panel`` are used to add a panel to the web
profiler.

All blocks have access to the ``collector`` object.

.. tip::

    Built-in templates use a base64 encoded image for the toolbar (``<img
    src="data:image/png;base64,..."``). You can easily calculate the
    base64 value for an image with this little script: ``echo
    base64_encode(file_get_contents($_SERVER['argv'][1]));``.

To enable the template, add a ``template`` attribute to the ``data_collector``
tag in your configuration. For example, assuming your template is in some
``AcmeDebugBundle``:

.. configuration-block::

    .. code-block:: yaml

        services:
            data_collector.your_collector_name:
                class: Acme\DebugBundle\Collector\Class\Name
                tags:
                    - { name: data_collector, template: "AcmeDebugBundle:Collector:templatename", id: "your_collector_name" }

    .. code-block:: xml

        <service id="data_collector.your_collector_name" class="Acme\DebugBundle\Collector\Class\Name">
            <tag name="data_collector" template="AcmeDebugBundle:Collector:templatename" id="your_collector_name" />
        </service>

    .. code-block:: php

        $container
            ->register('data_collector.your_collector_name', 'Acme\DebugBundle\Collector\Class\Name')
            ->addTag('data_collector', array(
                'template' => 'AcmeDebugBundle:Collector:templatename',
                'id'       => 'your_collector_name',
            ))
        ;
