.. index::
    single: Web Services; SOAP

.. _how-to-create-a-soap-web-service-in-a-symfony2-controller:

How to Create a SOAP Web Service in a Symfony Controller
========================================================

Setting up a controller to act as a SOAP server is aided by a couple
tools. Those tools expect you to have the `PHP SOAP`_ extension installed.
As the PHP SOAP extension cannot currently generate a WSDL, you must either
create one from scratch or use a 3rd party generator.

.. note::

    There are several SOAP server implementations available for use with
    PHP. `Laminas SOAP`_ and `NuSOAP`_ are two examples. Although the PHP SOAP
    extension is used in these examples, the general idea should still
    be applicable to other implementations.

SOAP works by exposing the methods of a PHP object to an external entity
(i.e. the person using the SOAP service). To start, create a class - ``HelloService`` -
which represents the functionality that you'll expose in your SOAP service.
In this case, the SOAP service will allow the client to call a method called
``hello``, which happens to send an email::

    // src/Service/HelloService.php
    namespace App\Service;

    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\Email;

    class HelloService
    {
        private MailerInterface $mailer;

        public function __construct(MailerInterface $mailer)
        {
            $this->mailer = $mailer;
        }

        public function hello(string $name): string
        {
            $email = (new Email())
                ->from('admin@example.com')
                ->to('me@example.com')
                ->subject('Hello Service')
                ->text($name.' says hi!');

            $this->mailer->send($email);

            return 'Hello, '.$name;
        }
    }

Next, make sure that your new class is registered as a service. If you're using
the :ref:`default services configuration <service-container-services-load-example>`,
you don't need to do anything!

Finally, below is an example of a controller that is capable of handling a SOAP
request. Because ``index()`` is accessible via ``/soap``, the WSDL document
can be retrieved via ``/soap?wsdl``::

    // src/Controller/HelloServiceController.php
    namespace App\Controller;

    use App\Service\HelloService;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class HelloServiceController extends AbstractController
    {
        #[Route('/soap')]
        public function index(HelloService $helloService, Request $request)
        {
            $soapServer = new \SoapServer('/path/to/hello.wsdl');
            $soapServer->setObject($helloService);

            $response = new Response();

            ob_start();
            $soapServer->handle($request->getContent());
            $response->setContent(ob_get_clean());

            foreach (headers_list() as $header) {
                $header = explode(':', $header, 2);
                $response->headers->set($header[0], $header[1]);
            }
            header_remove();

            return $response;
        }
    }

Take note of the calls to ``ob_start()`` and ``ob_get_clean()``. These
methods control `output buffering`_ which allows you to "trap" the echoed
output of ``$server->handle()``. This is necessary because Symfony expects
your controller to return a ``Response`` object with the output as its "content".
So, you use ``ob_start()`` to start buffering the STDOUT and use
``ob_get_clean()`` to dump the echoed output into the content of the Response
and clear the output buffer. Since ``$server->handle()`` can set headers it is
also necessary to "trap" these. For this we use ``headers_list`` which provides
the set headers, these are then parsed and added into the Response after which
``header_remove`` is used to remove the headers and to avoid duplicates.
Finally, you're ready to return the ``Response``.

Below is an example of calling the service using a native `SoapClient`_ client. This example
assumes that the ``index()`` method in the controller above is accessible via
the route ``/soap``::

    $soapClient = new \SoapClient('http://example.com/index.php/soap?wsdl');

    $result = $soapClient->__soapCall('hello', ['name' => 'Scott']);

An example WSDL is below.

.. code-block:: xml

    <?xml version="1.0" encoding="ISO-8859-1"?>
    <definitions xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:xsd="http://www.w3.org/2001/XMLSchema"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
        xmlns:tns="urn:helloservicewsdl"
        xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
        xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
        xmlns="http://schemas.xmlsoap.org/wsdl/"
        targetNamespace="urn:helloservicewsdl">

        <types>
            <xsd:schema targetNamespace="urn:hellowsdl">
                <xsd:import namespace="http://schemas.xmlsoap.org/soap/encoding/"/>
                <xsd:import namespace="http://schemas.xmlsoap.org/wsdl/"/>
            </xsd:schema>
        </types>

        <message name="helloRequest">
            <part name="name" type="xsd:string"/>
        </message>

        <message name="helloResponse">
            <part name="return" type="xsd:string"/>
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
                <soap:address location="http://example.com/index.php/soap"/>
            </port>
        </service>
    </definitions>

.. _`PHP SOAP`: https://www.php.net/manual/en/book.soap.php
.. _`NuSOAP`: https://sourceforge.net/projects/nusoap
.. _`output buffering`: https://www.php.net/manual/en/book.outcontrol.php
.. _`Laminas SOAP`: https://docs.laminas.dev/laminas-soap/server/
.. _`SoapClient`: https://www.php.net/manual/en/class.soapclient.php
