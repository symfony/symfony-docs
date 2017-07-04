.. index::
   single: Profiling; Data collector

How to Create a custom Data Collector
=====================================

The :doc:`Symfony Profiler </profiler>` delegates data collection
to some special classes called data collectors. Symfony comes bundled with a few
of them, but you can easily create your own.

Creating a custom Data Collector
--------------------------------

Creating a custom data collector is as simple as implementing the
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface`::

    interface DataCollectorInterface
    {
        function collect(Request $request, Response $response, \Exception $exception = null);
        function getName();
    }

The
:method:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface::getName`
method returns the name of the data collector and must be unique in the
application. This value is also used to access the information later on (see
:doc:`/testing/profiling` for instance).

The
:method:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface::collect`
method is responsible for storing the collected data in local properties.

.. caution::

    The ``collect()`` method is only called once. It is not used to "gather"
    data but is there to "pick up" the data that has been stored by your
    service.

Most of the time, it is convenient to extend
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector` and
populate the ``$this->data`` property (it takes care of serializing the
``$this->data`` property). Imagine you create a new data collector that
collects the method and accepted content types from the request::

    // src/AppBundle/DataCollector/RequestCollector.php
    namespace AppBundle\DataCollector;

    use Symfony\Component\HttpKernel\DataCollector\DataCollector;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class RequestCollector extends DataCollector
    {
        public function collect(Request $request, Response $response, \Exception $exception = null)
        {
            $this->data = array(
                'method' => $request->getMethod(),
                'acceptable_content_types' => $request->getAcceptableContentTypes(),
            );
        }

        public function getMethod()
        {
            return $this->data['method'];
        }

        public function getAcceptableContentTypes()
        {
            return $this->data['acceptable_content_types'];
        }

        public function getName()
        {
            return 'app.request_collector';
        }
    }

The getters are added to give the template access to the collected information.

.. caution::

    If the data that is not directly related to the request or response,
    you need to make the data accessible to your DataCollector. This can
    be achieved by injecting the service that holds the information you intend
    to profile into your DataCollector.

.. caution::

    As the profiler serializes data collector instances, you should not
    store objects that cannot be serialized (like PDO objects) or you need
    to provide your own ``serialize()`` method.

.. _data_collector_tag:

Enabling Custom Data Collectors
-------------------------------

If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`
with ``autoconfigure``, then Symfony will automatically see your new data collector!
Your ``collect()`` method should be called next time your refresh.

.. note::

    If you're not using ``autoconfigure``, you can also :ref:`manually wire your service <services-explicitly-configure-wire-services>`
    and :doc:`tag </service_container/tags>` it with ``data_collector``.

Adding Web Profiler Templates
-----------------------------

The information collected by your data collector can be displayed both in the
web debug toolbar and in the web profiler. To do so, you need to create a Twig
template that includes some specific blocks.

In the simplest case, you just want to display the information in the toolbar
without providing a profiler panel. This requires to define the ``toolbar``
block and set the value of two variables called ``icon`` and ``text``:

.. code-block:: html+twig

    {% extends '@WebProfiler/Profiler/layout.html.twig' %}

    {% block toolbar %}
        {% set icon %}
            {# this is the content displayed as a panel in the toolbar #}
            <span class="icon"><img src="..." alt=""/></span>
            <span class="sf-toolbar-status">Request</span>
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

    Built-in collector templates define all their images as embedded base64-encoded
    images. This makes them work everywhere without having to mess with web assets
    links:

    .. code-block:: html

        <img src="data:image/png;base64,..." />

    Another solution is to define the images as SVG files. In addition to being
    resolution-independent, these images can be easily embedded in the Twig
    template or included from an external file to reuse them in several templates:

    .. code-block:: twig

        {{ include('@App/data_collector/icon.svg') }}

    You are encouraged to use the latter technique for your own toolbar panels.

If the toolbar panel includes extended web profiler information, the Twig template
must also define additional blocks:

.. code-block:: html+twig

    {% extends '@WebProfiler/Profiler/layout.html.twig' %}

    {% block toolbar %}
        {% set icon %}
            <span class="icon"><img src="..." alt=""/></span>
            <span class="sf-toolbar-status">Request</span>
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

        # app/config/services.yml
        services:
            AppBundle\DataCollector\RequestCollector:
                tags:
                    -
                        name:     data_collector
                        template: 'data_collector/template.html.twig'
                        # must match the value returned by the getName() method
                        id:       'app.request_collector'
                        # optional priority
                        # priority: 300
                public: false

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\DataCollector\RequestCollector" public="false">
                    <!-- priority="300" -->
                    <tag name="data_collector"
                        template="data_collector/template.html.twig"
                        id="app.request_collector"
                    />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\DataCollector\RequestCollector;

        $container
            ->autowire(RequestCollector::class)
            ->setPublic(false)
            ->addTag('data_collector', array(
                'template' => 'data_collector/template.html.twig',
                'id'       => 'app.request_collector',
                // 'priority' => 300,
            ))
        ;

The position of each panel in the toolbar is determined by the priority defined
by each collector. Most built-in collectors use ``255`` as their priority. If you
want your collector to be displayed before them, use a higher value (like 300).
