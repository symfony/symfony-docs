How to Create your Custom Name Converter
========================================

The Serializer Component uses :ref:`name converters <serializer-name-conversion>`
to transform the attribute names (e.g. from snake_case in JSON to CamelCase
for PHP properties).

Imagine you have the following object::

    namespace App\Model;

    class Company
    {
        public string $name;
        public string $address;
    }

And in the serialized form, all attributes must be prefixed by ``org_`` like
the following:

.. code-block:: json

    {"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City"}

A custom name converter can handle such cases::

    namespace App\Serializer;

    use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

    class OrgPrefixNameConverter implements NameConverterInterface
    {
        public function normalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string
        {
            // during normalization, add the prefix
            return 'org_'.$propertyName;
        }

        public function denormalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string
        {
            // remove the 'org_' prefix on denormalizing
            return str_starts_with($propertyName, 'org_') ? substr($propertyName, 4) : $propertyName;
        }
    }

Then, configure the serializer to use your name converter:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/serializer.yaml
        framework:
            serializer:
                # pass the service ID of your name converter
                name_converter: 'App\Serializer\OrgPrefixNameConverter'

    .. code-block:: php

        // config/packages/serializer.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Serializer\OrgPrefixNameConverter;

        return App::config([
            'framework' => [
                'serializer' => [
                    'name_converter' => OrgPrefixNameConverter::class,
                ],
            ],
        ]);

Now, when using the serializer in the application, all attributes will be
prefixed by ``org_``::

    // ...
    $company = new Company('Acme Inc.', '123 Main Street, Big City');

    $json = $serializer->serialize($company, 'json');
    // {"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City"}
    $companyCopy = $serializer->deserialize($json, Company::class, 'json');
    // Same data as $company
