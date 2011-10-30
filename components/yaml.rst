.. index::
   single: Yaml

Introduction
============

According to its original `authors`_, YAML is a human-friendly, cross language,
Unicode based data serialization language designed around the common native data
structures of agile programming languages. It is broadly useful for programming
needs ranging from configuration files to Internet messaging to object
persistence to data auditing.

The Yaml component provides a convenient API for dumping PHP arrays to YAML, and
parsing YAML strings (and also files) to PHP arrays. This component supports
most of the `YAML 2.1 specification`_ and also embeds YAML syntax validation.

Dependencies
============

The Yaml component has no dependency with any other component or third party
library.

Installation
============

There are three common ways to get and install a Symfony component into your
project. The following sections describe how to install the Yaml component from
PEAR, Git or a frozen tarball.

From the PEAR Channel
---------------------

Before installing any Symfony component, make sure you first already registered
the Symfony `PEAR channel`_ in your PEAR configuration.

    $ pear channel-discover pear.symfony.com

Then, install the latest version of the component by using the `pear install` command as follow.

    $ pear install symfony2/Yaml

If you want to install a specific version of the component, simply use the
following `pear install` command shape.

    $ pear install symfony2/Yaml-1.0.0
    $ pear install symfony2/Yaml-beta

From the Official Git Repository
--------------------------------

Installing a component from its Git repository is as easy as cloning the
`official repository`_ hosted on Github.

    $ git clone https://github.com/symfony/Yaml.git /path/to/lib/Symfony/Component/Yaml


From a Tarball Archive
----------------------

If you want to install a frozen version of the Yaml component, simply download
the corresponding package and unarchive it in your project.

    http://pear.symfony.com/get/Yaml-2.0.4.tgz

Make sure to unarchive the tarball under the ``Symfony/Component/Yaml`` 
directory of your project.

When installing any Symfony component from a Git repository or a tarball, make
sure to follow the directory structure and naming conventions. This matters if
you want to use a PSR-0 autoloading system like the Symfony ClassLoader
component to automatically include your classes definition.

Basic Usage
===========

Basically, there are only two main features provided by the Yaml component:
dumping PHP arrays to Yaml string and parsing existing Yaml strings.

Parsing a Yaml String
----------------------

The ``Yaml::parse()`` method, when supplied with a YAML string will do its best
to convert a YAML string into a PHP array.

.. code-block:: php

    use Symfony\Component\Yaml\Yaml;
    use Symfony\Component\Yaml\Exception\ParseException;

    $yaml = <<<'EOS'
    food:
        fruits: [Apple, Pear, Banana]
        cake: Chocolate
    EOS;

    try {
        $array = Yaml::parse($yaml);
        print_r($array);
    } catch (ParseException $e) {
        // handle the exception...
    }

In case of parsing error, the ``Yaml::parse()`` method will raise a
``ParseException`` exception.

Parsing a Yaml File
----------------------

The ``Yaml::parse()`` method, when supplied with a YAML file will do its best to
convert a YAML in a file into a PHP array.

.. code-block:: php

    use Symfony\Component\Yaml\Yaml;
    use Symfony\Component\Yaml\Exception\ParseException;

    try {
        $array = Yaml::parse('config.yml');
        print_r($array);
    } catch (ParseException $e) {
        // handle the exception...
    }

In case of parsing error, the ``Yaml::parse()`` method will raise a
``ParseException`` exception.

Dumping a PHP Array to a YAML String
------------------------------------

The ``Yaml::dump()`` method, when supplied with an array, will do its best to convert the array into friendly YAML.
 
.. code-block:: php

    use Symfony\Component\Yaml\Yaml;

    $array = array(
        'food' => array(
            'fruits' => array('Apple', 'Pear', 'Banana'),
            'cake' => 'Chocolate'
        )
    );

    echo Yaml::dump($array, 2);

The second optional argument defines the level where you switch to inline YAML.

.. _`authors`: http://www.yaml.org/spec/
.. _`YAML 2.1 specification`: http://yaml.org/spec/1.2/spec.html
.. _`PEAR channel`: http://pear.symfony.com/