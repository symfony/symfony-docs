.. index::
    single: Web Services; SOAP

How to Create a SOAP Web Service in a Symfony2 Controller
=========================================================

Setting up a controller to act as a SOAP server is simple with a couple
tools. You must, of course, have the `PHP SOAP`_ extension installed.
As the PHP SOAP extension can not currently generate a WSDL, you must either
create one from scratch or use a 3rd party generator.

.. note::

    There are several SOAP server implementations available for use with
    PHP. `Zend SOAP`_ and `NuSOAP`_ are two examples. Although the PHP SOAP
    extension is used in these examples, the general idea should still
    be applicable to other implementations.

SOAP works by exposing the methods of a PHP object to an external entity
(i.e. the person using the SOAP service). To start, create a class - ``HelloService`` -
which represents the functionality that you'll expose in your SOAP service.
In this case, the SOAP service will allow the client to call a method called
``hello``, which happens to send an email::

    // src/Acme/SoapBundle/Services/HelloService.php
    namespace Acme\SoapBundle\Services;

    class HelloService
    {
        private $mailer;

        public function __construct(\Swift_Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        public function hello($name)
        {

            $message = \Swift_Message::newInstance()
                                    ->setTo('me@example.com')
                                    ->setSubject('Hello Service')
                                    ->setBody($name . ' says hi!');

            $this->mailer->send($message);

            return 'Hello, '.$name;
        }
    }

Next, you can train Symfony to be able to create an instance of this class.
Since the class sends an e-mail, it's been designed to accept a ``Swift_Mailer``
instance. Using the Service Container, you can configure Symfony to construct
a ``HelloService`` object properly:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            hello_service:
                class: Acme\SoapBundle\Services\HelloService
                arguments: ["@mailer"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="hello_service" class="Acme\SoapBundle\Services\HelloService">
                <argument type="service" id="mailer"/>
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register('hello_service', 'Acme\SoapBundle\Services\HelloService')
            ->addArgument(new Reference('mailer'));

Below is an example of a controller that is capable of handling a SOAP
request. If ``indexAction()`` is accessible via the route ``/soap``, then the
WSDL document can be retrieved via ``/soap?wsdl``.

.. code-block:: php

    namespace Acme\SoapBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;

    class HelloServiceController extends Controller
    {
        public function indexAction()
        {
            $server = new \SoapServer('/path/to/hello.wsdl');
            $server->setObject($this->get('hello_service'));

            $response = new Response();
            $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');

            ob_start();
            $server->handle();
            $response->setContent(ob_get_clean());

            return $response;
        }
    }

Take note of the calls to ``ob_start()`` and ``ob_get_clean()``. These
methods control `output buffering`_ which allows you to "trap" the echoed
output of ``$server->handle()``. This is necessary because Symfony expects
your controller to return a ``Response`` object with the output as its "content".
You must also remember to set the "Content-Type" header to "text/xml", as
this is what the client will expect. So, you use ``ob_start()`` to start
buffering the STDOUT and use ``ob_get_clean()`` to dump the echoed output
into the content of the Response and clear the output buffer. Finally, you're
ready to return the ``Response``.

Below is an example calling the service using a `NuSOAP`_ client. This example
assumes that the ``indexAction`` in the controller above is accessible via the
route ``/soap``::

    $client = new \Soapclient('http://example.com/app.php/soap?wsdl', true);

    $result = $client->call('hello', array('name' => 'Scott'));

An example WSDL is below.

.. code-block:: xml

    <?xml version="1.0" encoding="ISO-8859-1"?>
    <definitions xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:xsd="http://www.w3.org/2001/XMLSchema"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
        xmlns:tns="urn:arnleadservicewsdl"
        xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
        xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
        xmlns="http://schemas.xmlsoap.org/wsdl/"
        targetNamespace="urn:helloservicewsdl">

        <types>
            <xsd:schema targetNamespace="urn:hellowsdl">
                <xsd:import namespace="http://schemas.xmlsoap.org/soap/encoding/" />
                <xsd:import namespace="http://schemas.xmlsoap.org/wsdl/" />
            </xsd:schema>
        </types>

        <message name="helloRequest">
            <part name="name" type="xsd:string" />
        </message>

        <message name="helloResponse">
            <part name="return" type="xsd:string" />
        </message>

        <portType name="hellowsdlPortType">
            <operation name="hello">
                <documentation>Hello World</documentation>
                <input message="tns:helloRequest"/>
                <output message="tns:helloResponse"/>
            </operation>
        </portType>

        <binding name="hellowsdlBinding" type="tns:hellowsdlPortType">
            <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
            <operation name="hello">
                <soap:operation soapAction="urn:arnleadservicewsdl#hello" style="rpc"/>

                <input>
                    <soap:body use="encoded" namespace="urn:hellowsdl"
                        encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                </input>

                <output>
                    <soap:body use="encoded" namespace="urn:hellowsdl"
                        encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                </output>
            </operation>
        </binding>

        <service name="hellowsdl">
            <port name="hellowsdlPort" binding="tns:hellowsdlBinding">
                <soap:address location="http://example.com/app.php/soap" />
            </port>
        </service>
    </definitions>

.. _`PHP SOAP`:          http://php.net/manual/en/book.soap.php
.. _`NuSOAP`:            http://sourceforge.net/projects/nusoap
.. _`output buffering`:  http://php.net/manual/en/book.outcontrol.php
.. _`Zend SOAP`:         http://framework.zend.com/manual/en/zend.soap.server.html
