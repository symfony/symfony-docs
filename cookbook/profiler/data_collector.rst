.. index::
   single: Profiling; Data collector

How to Create a custom Data Collector
=====================================

:doc:`The Symfony Profiler </cookbook/profiler/index>` delegates data collection
to some special classes called data collectors. Symfony comes bundled with a few
of them, but you can easily create your own.

Creating a custom Data Collector
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

The value returned by ``getName()`` must be unique in the application. This value
is also used to access the information later on (see :doc:`/cookbook/testing/profiling`
for instance).

The ``collect()`` method is responsible for storing the collected data in local
properties.

.. caution::

    As the profiler serializes data collector instances, you should not
    store objects that cannot be serialized (like PDO objects), or you need
    to provide your own ``serialize()`` method.

Most of the time, it is convenient to extend
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector` and
populate the ``$this->data`` property (it takes care of serializing the
``$this->data`` property)::

    // src/AppBundle/DataCollector/MyCollector.php
    use Symfony\Component\HttpKernel\DataCollector\DataCollector;

    class MyCollector extends DataCollector
    {
        public function collect(Request $request, Response $response, \Exception $exception = null)
        {
            $this->data = array(
                'variable' => 'value',
            );
        }

        public function getVariable()
        {
            return $this->data['variable'];
        }

        public function getName()
        {
            return 'app.my_collector';
        }
    }

.. _data_collector_tag:

Enabling Custom Data Collectors
-------------------------------

To enable a data collector, define it as a regular service and tag it with
``data_collector``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.my_collector:
                class: AppBundle\DataCollector\MyCollector
                public: false
                tags:
                    - { name: data_collector }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <services>
                <service id="app.my_collector" class="AppBundle\DataCollector\MyCollector"
                         public="false">
                    <tag name="data_collector" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('app.my_collector', 'AppBundle\DataCollector\MyCollector')
            ->setPublic(false)
            ->addTag('data_collector')
        ;

Adding Web Profiler Templates
-----------------------------

The information collected by your data collector can be displayed both in the
web debug toolbar and in the web profiler. To do so, you need to create a Twig
template that includes some specific blocks.

In the simplest case, you just want to display the information in the toolbar
without providing a profiler panel. This requires to define the ``toolbar``
block and set the value of two variables called ``icon`` and ``text``:

.. code-block:: html+jinja

    {% extends 'WebProfilerBundle:Profiler:layout.html.twig' %}

    {% block toolbar %}
        {% set icon %}
            {# this is the content displayed as a panel in the toolbar #}
            <span class="icon"><img src="..." alt=""/></span>
            <span class="sf-toolbar-status">Information</span>
        {% endset %}

        {% set text %}
            {# this is the content displayed when hovering the mouse over
               the toolbar panel #}
            <div class="sf-toolbar-info-piece">
                <b>Quick piece of data</b>
                <span>100 units</span>
            </div>
            <div class="sf-toolbar-info-piece">
                <b>Another piece of data</b>
                <span>300 units</span>
            </div>
        {% endset %}

        {# the 'link' value set to 'false' means that this panel doesn't
           show a section in the web profiler. #}
        {{ include('@WebProfiler/Profiler/toolbar_item.html.twig', { link: false }) }}
    {% endblock %}

.. tip::

    Built-in collector templates define all their images as embedded base64-encoded
    images. This makes them work everywhere without having to mess with web assets
    links:

    .. code-block:: html

        <img src="data:image/png;base64,..." />

    Another solution is to define the images as SVG files. In addition to being
    resolution-independent, these images can be easily embedded in the Twig
    template or included from an external file to reuse them in several templates:

    .. code-block:: jinja

        {{ include('@App/data_collector/icon.svg') }}

    You are encouraged to use the latter technique for your own toolbar panels.

If the toolbar panel includes extended web profiler information, the Twig template
must also define additional blocks:

.. code-block:: html+jinja

    {% extends '@WebProfiler/Profiler/layout.html.twig' %}

    {% block toolbar %}
        {% set icon %}
            <span class="icon"><img src="..." alt=""/></span>
            <span class="sf-toolbar-status">Information</span>
        {% endset %}

        {% set text %}
            <div class="sf-toolbar-info-piece">
                {# ... #}
            </div>
        {% endset %}

        {# the 'link' value is now set to 'true', which allows the user to click
           on it to access the web profiler panel. Since 'true' is the default
           value, you can omit the 'link' parameter entirely #}
        {{ include('@WebProfiler/Profiler/toolbar_item.html.twig', { link: true }) }}
    {% endblock %}

    {% block head %}
        {# Optional, you can here link to or define your own CSS and JS contents #}
        {# {{ parent() }} to keep the default styles #}
    {% endblock %}

    {% block menu %}
        {# This left-hand menu appears when using the full-screen profiler. #}
        <span class="label">
            <span class="icon"><img src="..." alt=""/></span>
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

The ``menu`` and ``panel`` blocks are the only required blocks to define the
contents displayed in the web profiler panel associated with this data collector.
All blocks have access to the ``collector`` object.

Finally, to enable the data collector template, add a ``template`` attribute to
the ``data_collector`` tag in your service configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.my_collector:
                class: AppBundle\DataCollector\MyCollector
                tags:
                    -
                        name:     data_collector
                        template: 'data_collector/template.html.twig'
                        id:       'app.my_collector'
                public: false

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <services>
                <service id="app.my_collector" class="AppBundle\DataCollector\MyCollector" public="false">
                    <tag name="data_collector" template="data_collector/template.html.twig" id="app.my_collector" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('app.my_collector', 'AppBundle\DataCollector\MyCollector')
            ->setPublic(false)
            ->addTag('data_collector', array(
                'template' => 'data_collector/template.html.twig',
                'id'       => 'app.my_collector',
            ))
        ;

.. caution::

    The ``id`` attribute must match the value returned by the ``getName()`` method.

The position of each panel in the toolbar is determined by the priority defined
by each collector. Most built-in collectors use ``255`` as their priority. If you
want your collector to be displayed before them, use a higher value:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.my_collector:
                class: AppBundle\DataCollector\MyCollector
                tags:
                    - { name: data_collector, template: '...', id: '...', priority: 300 }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <service id="app.my_collector" class="AppBundle\DataCollector\MyCollector">
            <tag name="data_collector" template="..." id="..." priority="300" />
        </service>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('app.my_collector', 'AppBundle\DataCollector\MyCollector')
            ->addTag('data_collector', array(
                'template' => '...',
                'id'       => '...',
                'priority' => 300,
            ))
        ;
