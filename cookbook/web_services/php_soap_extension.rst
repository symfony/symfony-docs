.. index::
    single: Web Services; SOAP

How to Create a SOAP Web Service in a Symfony2 Controller
=========================================================

Setting up a controller to act as a SOAP server is simple with a couple 
tools.  You must, of course, have the `PHP SOAP`_ extension installed.  
As the PHP SOAP extension can not currently generate a WSDL, you must either 
create one from scratch or use a 3rd party generator.

Below is an example of a controller that is capable of handling a SOAP 
request.  If ``indexAction()`` is accessible via the route ``/soap``, then the 
WSDL document can be retrieved via ``/soap?wsdl``.

.. code-block:: php

    class MySoapController extends Controller 
    {
        public function indexAction()
        {
            $server = new \SoapServer('/path/to/hello.wsdl');
            
            $server->setObject($this);
            
            $response = new Response();
            
            $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');
            
            ob_start();
            
            $server->handle();
            
            $response->setContent(ob_get_clean());
            
            return $response;
        }
 
        public function hello($name)
        {
            return 'Hello, ' . $name . '!';
        }
    }

Take note of the calls to ``ob_start()`` and ``ob_get_clean()``.  These
methods control `output buffering`_ which allows you to "trap" the echoed 
output of ``$server->handle()``. This is necessary because Symfony expects
your controller to return a ``Response`` object with the output as its "content".
You must also remember to set the "Content-Type" header to "text/xml", as
this is what the client  will expect.  So, you use ``ob_start()`` to start
buffering the STDOUT and use  ``ob_get_clean()`` to dump the echoed output
into the content of the Response and clear the output buffer.  Finally, you're
ready to return the ``Response``.

Below is an example calling the service using `NuSOAP`_ client.  This example 
assumes the ``indexAction`` in the controller above is accessible via the
route ``/soap``::

    $client = new soapclient('http://example.com/app.php/soap?wsdl', true);
    
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
         targetNamespace="urn:arnleadservicewsdl">
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
