.. index::
   single: Components;

How to use the Service Container standalone
===========================================

To use the service container you first have to setup the classloading. 
Take a look into ":doc:`/cookbook/components/class_loader`"

Configure the Service Container directly
----------------------------------------

This creates an instance of the ContainerBuilder and adds a Service with a 
Reference to a Service which is created later. my.other_service is only 
created when my.service is consumed.

.. code-block:: php

  // container.php
  
  include_one __DIR__.'/autoload.php';
  
  use Symfony\Component\DependencyInjection\ContainerBuilder;
  use Symfony\Component\DependencyInjection\Reference;
  
  $container = new ContainerBuilder();
  $container->setParameter('my.service.class','Blage\Service\MyService');
  $container->setParameter('my.other_service.class','Blage\Service\MyOtherService');
  
  $container->register('my.service','%my.service.class%')
            ->addArgument(new Reference('my.other_service'));
            
  $container->register('my.other_service','%my.other_service.class%');


Configure the Service Container via XML
---------------------------------------

To configure the Container with a File containing the configuration, you need
a ``FileLocator``, its Argument is the Directory to search for Files in.
The ``XmlFileLoader`` translates the Configuration to calls like in the above 
example.

.. code-block:: php

  // bootstrap.php
  
  include_once __DIR__.'/autoload.php';
  
  use Symfony\Component\DependencyInjection\ContainerBuilder;
  use Symfony\Component\Config\FileLocator;
  use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
  
  $container = new ContainerBuilder();
  $locator = new FileLocator(__DIR__);
  $xmlLoader = new XmlFileLoader($container, $locator);
  $xmlLoader->load('service.xml');

Now a configuration like this one can be used. The topic about how to use the
service container is covered in ":doc:`/book/service_container`".

.. code-block:: xml
  
  <!-- service.xml -->
  
  <?xml version="1.0" ?>
  
  <container xmlns="http://symfony.com/schema/dic/services"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
  
      <parameters>
          <parameter key="my.service.class">Blage\Service\MyService</parameter>
      </parameters>
  
      <services>
          <service id="my_service" class="%my.service.class%" >
          </service>
      </services>
  </container>

With this set up its now possible to load several configurations.
For example one with the general setup stuff und in the second file only 
services used in tests.

.. code-block:: php

  // bootstrap.php
  
  ...
  
  $xmlLoader->load('service.xml');
  
  if (TEST) {
    $xmlLoader->load('test_services.xml');
  }
