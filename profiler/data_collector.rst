.. index::
   single: Profiling; Data collector

How to Create a custom Data Collector
=====================================

The :doc:`Symfony Profiler </profiler>` obtains its profiling and debug
information using some special classes called data collectors. Symfony comes
bundled with a few of them, but you can also create your own.

Creating a custom Data Collector
--------------------------------

A data collector is a PHP class that implements the
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface`.
For convenience, your data collectors can also extend from the
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector` class, which
implements the interface and provides some utilities and the ``$this->data``
property to store the collected information.

The following example shows a custom collector that stores information about the
request::

    // src/DataCollector/RequestCollector.php
    namespace App\DataCollector;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\DataCollector\DataCollector;

    class RequestCollector extends DataCollector
    {
        public function collect(Request $request, Response $response, \Exception $exception = null)
        {
            $this->data = [
                'method' => $request->getMethod(),
                'acceptable_content_types' => $request->getAcceptableContentTypes(),
            ];
        }

        public function reset()
        {
            $this->data = [];
        }

        public function getName()
        {
            return 'app.request_collector';
        }

        // ...
    }

:method:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface::collect` method:
    Stores the collected data in local properties (``$this->data`` if you extend
    from :class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector`).
    If the data to collect cannot be obtained through the request or response,
    inject the needed services in the data collector.

    .. caution::

        The ``collect()`` method is only called once. It is not used to "gather"
        data but is there to "pick up" the data that has been stored by your
        service.

    .. caution::

        As the profiler serializes data collector instances, you should not
        store objects that cannot be serialized (like PDO objects) or you need
        to provide your own ``serialize()`` method.

:method:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface::reset` method:
    It's called between requests to reset the state of the profiler. Use it to
    remove all the information collected with the ``collect()`` method.

:method:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface::getName` method:
    Returns the collector identifier, which must be unique in the application.
    This value is used later to access the collector information (see
    :doc:`/testing/profiling`) so it's recommended to return a string which is
    short, lowercase and without white spaces.

.. _data_collector_tag:

Enabling Custom Data Collectors
-------------------------------

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`
with ``autoconfigure``, then Symfony will automatically see your new data collector!
Your ``collect()`` method should be called next time your refresh.

If you're not using ``autoconfigure``, you can also :ref:`manually wire your service <services-explicitly-configure-wire-services>`
and :doc:`tag </service_container/tags>` it with ``data_collector``.

Adding Web Profiler Templates
-----------------------------

The information collected by your data collector can be displayed both in the
web debug toolbar and in the web profiler. To do so, you need to create a Twig
template that includes some specific blocks.

However, first you must add some getters in the data collector class to give the
template access to the collected information::

    // src/DataCollector/RequestCollector.php
    namespace App\DataCollector;

    use Symfony\Component\HttpKernel\DataCollector\DataCollector;

    class RequestCollector extends DataCollector
    {
        // ...

        public function getMethod()
        {
            return $this->data['method'];
        }

        public function getAcceptableContentTypes()
        {
            return $this->data['acceptable_content_types'];
        }
    }

In the simplest case, you just want to display the information in the toolbar
without providing a profiler panel. This requires to define the ``toolbar``
block and set the value of two variables called ``icon`` and ``text``:

.. code-block:: html+twig

    {% extends '@WebProfiler/Profiler/layout.html.twig' %}

    {% block toolbar %}
        {% set icon %}
            {# this is the content displayed as a panel in the toolbar #}
            <svg xmlns="http://www.w3.org/2000/svg"> ... </svg>
            <span class="sf-toolbar-value">Request</span>
        {% endset %}

        {% set text %}
            {# this is the content displayed when hovering the mouse over
               the toolbar panel #}
            <div class="sf-toolbar-info-piece">
                <b>Method</b>
                <span>{{ collector.method }}</span>
            </div>

            <div class="sf-toolbar-info-piece">
                <b>Accepted content type</b>
                <span>{{ collector.acceptableContentTypes|join(', ') }}</span>
            </div>
        {% endset %}

        {# the 'link' value set to 'false' means that this panel doesn't
           show a section in the web profiler #}
        {{ include('@WebProfiler/Profiler/toolbar_item.html.twig', { link: false }) }}
    {% endblock %}

.. tip::

    Built-in collector templates define all their images as embedded SVG files.
    This makes them work everywhere without having to mess with web assets links:

    .. code-block:: twig

        {% set icon %}
            {{ include('data_collector/icon.svg') }}
            {# ... #}
        {% endset %}

If the toolbar panel includes extended web profiler information, the Twig template
must also define additional blocks:

.. code-block:: html+twig

    {% extends '@WebProfiler/Profiler/layout.html.twig' %}

    {% block toolbar %}
        {% set icon %}
            {# ... #}
        {% endset %}

        {% set text %}
            <div class="sf-toolbar-info-piece">
                {# ... #}
            </div>
        {% endset %}

        {{ include('@WebProfiler/Profiler/toolbar_item.html.twig', { 'link': true }) }}
    {% endblock %}

    {% block head %}
        {# Optional. Here you can link to or define your own CSS and JS contents. #}
        {# Use {{ parent() }} to extend the default styles instead of overriding them. #}
    {% endblock %}

    {% block menu %}
        {# This left-hand menu appears when using the full-screen profiler. #}
        <span class="label">
            <span class="icon"><img src="..." alt=""/></span>
            <strong>Request</strong>
        </span>
    {% endblock %}

    {% block panel %}
        {# Optional, for showing the most details. #}
        <h2>Acceptable Content Types</h2>
        <table>
            <tr>
                <th>Content Type</th>
            </tr>

            {% for type in collector.acceptableContentTypes %}
            <tr>
                <td>{{ type }}</td>
            </tr>
            {% endfor %}
        </table>
    {% endblock %}

The ``menu`` and ``panel`` blocks are the only required blocks to define the
contents displayed in the web profiler panel associated with this data collector.
All blocks have access to the ``collector`` object.

Finally, to enable the data collector template, override your service configuration
to specify a tag that contains the template:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\DataCollector\RequestCollector:
                tags:
                    -
                        name:     data_collector
                        template: 'data_collector/template.html.twig'
                        # must match the value returned by the getName() method
                        id:       'app.request_collector'
                        # optional priority
                        # priority: 300

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\DataCollector\RequestCollector">
                    <!-- priority="300" -->
                    <tag name="data_collector"
                        template="data_collector/template.html.twig"
                        id="app.request_collector"
                    />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\DataCollector\RequestCollector;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(RequestCollector::class)
                ->autowire()
                ->tag('data_collector', [
                    'template' => 'data_collector/template.html.twig',
                    'id'       => 'app.request_collector',
                    // 'priority' => 300,
                ]);
        };

The position of each panel in the toolbar is determined by the collector priority.
Priorities are defined as positive or negative integers and they default to ``0``.
Most built-in collectors use ``255`` as their priority. If you want your collector
to be displayed before them, use a higher value (like 300).
