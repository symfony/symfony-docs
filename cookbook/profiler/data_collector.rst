.. index::
   single: Profiling; Data collector

How to Create a custom Data Collector
=====================================

:doc:`The Symfony Profiler </components/profiler/index>` delegates data collecting to
data collectors. Symfony comes bundled with a few of them, but you can easily
create your own.

Creating a custom Data Collector
--------------------------------

Creating a custom data collector is as simple as implementing the
:class:`Symfony\\Component\\Profiler\\DataCollector\\DataCollectorInterface`::

    interface DataCollectorInterface
    {
        /**
         * Returns the collected data.
         *
         * @return ProfileDataInterface
         *
         * @todo introduce in 3.0
         */
        public function getCollectedData();
    }

if the data should be collected just prior to the Profile being saved add the :class:`Symfony\\Component\\Profiler\\DataCollector\\LateDataCollectorInterface`::

    interface LateDataCollectorInterface
    {
    }

The ``getCollectedData()`` method is responsible for storing the data it wants to give
access to in a :class:`Symfony\\Component\\Profiler\\ProfileData\\ProfileDataInterface`::

    interface ProfileDataInterface extends \Serializable
    {
        public function getName();
    }

The ``getName()`` method must return a unique name. This is used to access the
information later on (see :doc:`/cookbook/testing/profiling` for
instance).

.. caution::

    As the profiler serializes ProfileData instances, you should not
    store objects that cannot be serialized (like PDO objects), or you need
    to provide your own ``serialize()`` method.

Example DataCollector::

    class MemoryDataCollector extends AbstractDataCollector implements LateDataCollectorInterface
    {
        private $memoryLimit;

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->memoryLimit = ini_get('memory_limit');
        }

        /**
         * {@inheritdoc}
         */
        public function lateCollect()
        {
            return new MemoryData(memory_get_peak_usage(true), $this->memoryLimit);
        }
    }

    class MemoryData implements ProfileDataInterface
    {
        private $memory;
        private $memoryLimit;

        /**
         * Constructor.
         *
         * @param int $memory       The current used memory.
         * @param int $memoryLimit  The memory limit.
         */
        public function __construct($memory, $memoryLimit)
        {
            $this->memory = $memory;
            $this->memoryLimit = $this->convertToBytes($memoryLimit);
        }

        /**
         * {@inheritdoc}
         */
        public function getName()
        {
            return 'memory';
        }

        /**
         * Returns the memory.
         *
         * @return int The memory
         */
        public function getMemory()
        {
            return $this->memory;
        }

        /**
         * Returns the PHP memory limit.
         *
         * @return int The memory limit
         */
        public function getMemoryLimit()
        {
            return $this->memoryLimit;
        }

        //...
    }




.. _data_collector_tag:

Enabling custom Data Collectors
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

When you want to display the data collected by your data collector in the web
debug toolbar or the web profiler, you will need to create a Twig template. The
following example can help you get started:

.. code-block:: jinja

    {% extends 'WebProfilerBundle:Profiler:layout.html.twig' %}

    {% block toolbar %}
        {# This toolbar item may appear along the top or bottom of the screen.#}
        {% set icon %}
        <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAcCAQAAADVGmdYAAAAAmJLR0QA/4ePzL8AAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQffAxkBCDStonIVAAAAGXRFWHRDb21tZW50AENyZWF0ZWQgd2l0aCBHSU1QV4EOFwAAAHpJREFUOMtj3PWfgXRAuqZd/5nIsIdhVBPFmgqIjCuYOrJsYtz1fxuUOYER2TQID8afwIiQ8YIkI4TzCv5D2AgaWSuExJKMIDbA7EEVhQEWXJ6FKUY4D48m7HYU/EcWZ8JlE6qfMELPDcUJuEMPxvYazYTDWRMjOcUyAEswO+VjeQQaAAAAAElFTkSuQmCC" alt=""/></span>
        <span class="sf-toolbar-status">Example</span>
        {% endset %}

        {% set text %}
        <div class="sf-toolbar-info-piece">
            <b>Quick piece of data</b>
            <span>100 units</span>
        </div>
        <div class="sf-toolbar-info-piece">
            <b>Another quick thing</b>
            <span>300 units</span>
        </div>
        {% endset %}

        {# Set the "link" value to false if you do not have a big "panel"
           section that you want to direct the user to. #}
        {{ include('@WebProfiler/Profiler/toolbar_item.html.twig', { 'link': true }) }}

    {% endblock %}

    {% block head %}
        {# Optional, if you need your own JS or CSS files. #}
        {{ parent() }} {# Use parent() to keep the default styles #}
    {% endblock %}

    {% block menu %}
        {# This left-hand menu appears when using the full-screen profiler. #}
        <span class="label">
            <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAcCAQAAADVGmdYAAAAAmJLR0QA/4ePzL8AAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQffAxkBCDStonIVAAAAGXRFWHRDb21tZW50AENyZWF0ZWQgd2l0aCBHSU1QV4EOFwAAAHpJREFUOMtj3PWfgXRAuqZd/5nIsIdhVBPFmgqIjCuYOrJsYtz1fxuUOYER2TQID8afwIiQ8YIkI4TzCv5D2AgaWSuExJKMIDbA7EEVhQEWXJ6FKUY4D48m7HYU/EcWZ8JlE6qfMELPDcUJuEMPxvYazYTDWRMjOcUyAEswO+VjeQQaAAAAAElFTkSuQmCC" alt=""/></span>
            <strong>Example Collector</strong>
        </span>
    {% endblock %}

    {% block panel %}
        {# Optional, for showing the most details. #}
        <h2>Example</h2>
        <p>
            <em>Major information goes here</em>
        </p>
    {% endblock %}

Each block is optional. The ``toolbar`` block is used for the web debug
toolbar and ``menu`` and ``panel`` are used to add a panel to the web
profiler.

All blocks have access to the ``collector`` object.

.. tip::

    Built-in templates use a base64 encoded image for the toolbar:

    .. code-block:: html

        <img src="data:image/png;base64,..." />

    You can easily calculate the base64 value for an image with this
    little script::

        #!/usr/bin/env php
        <?php
        echo base64_encode(file_get_contents($_SERVER['argv'][1]));

To enable the template, add a ``template`` attribute to the ``data_collector``
tag in your configuration. For example, assuming your template is in AppBundle:

.. configuration-block::

    .. code-block:: yaml

        services:
            data_collector.your_collector_name:
                class: AppBundle\Collector\Class\Name
                tags:
                    - { name: data_collector, template: "AppBundle:Collector:templatename", id: "your_collector_name" }

    .. code-block:: xml

        <service id="data_collector.your_collector_name" class="AppBundle\Collector\Class\Name">
            <tag name="data_collector" template="AppBundle:Collector:templatename" id="your_collector_name" />
        </service>

    .. code-block:: php

        $container
            ->register('data_collector.your_collector_name', 'AppBundle\Collector\Class\Name')
            ->addTag('data_collector', array(
                'template' => 'AppBundle:Collector:templatename',
                'id'       => 'your_collector_name',
            ))
        ;

.. caution::

    Make sure the ``id`` attribute is the same string you used for the
    ``getName()`` method.
