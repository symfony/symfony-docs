.. index::
   single: Components;

How to use ClassLoader standalone
===========================================

Choose your prefered way to install the components. This article assumes 
installation via PEAR in /usr/share/php. Install the ``Symfony\Component\ClassLoader``.

.. code-block:: php

  // auoload.php
  
  include_once('/usr/share/php/Symfony/Component/ClassLoaderUniversalClassLoader.php');
  
  use Symfony\Component\ClassLoaderUniversalClassLoader;
  
  $namespaces = array(
    'Symfony' => array('/usr/share/php')
  );
  
  $loader = new UniversalClassLoader();
  $loader->addNamespaces($namespaces);
  $loader->register();

Now the namespace ``Symfony`` is registered and the classes are usable without 
explicitly including them.
This can be used for own code too, just add another entry to the array. The values
(the array with the path) is an array because you can split the same namespace
over several locations. This is especellay usefull for tests.

.. code-block:: php

  // auoload.php
  ...
  
  $namespaces = array(
    'Symfony' => array('/usr/share/php'),
    'Blage' => array('/path/to/project','/path/to/project/test'),
  );
  
  ...
