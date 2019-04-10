.. index::
    single: Secrets

How to Keep Sensitive Information Secret
========================================

.. versionadded:: 4.4

    The Secrets management was introduced in Symfony 4.4.

In :doc:`/configuration` and :doc:`/configuration/environment_variables`, you
learned how to manage your application configuration. In this article you'll
learn how to safely configure your application with sensitive information such
as credentials, passwords, tokens, API keys without exposing them.

.. note::

    In order to use Symfony's secrets, you will need the sodium PHP extension
    bundled in PHP 7.2, but if you use an earlier PHP version, you can
    install the `libsodium`_ PHP extension or use the
    `paragonie/sodium_compat`_ package.

.. _secrets-configuration:

Configuration
-------------

By Default, secrets are enabled by the Framework. The default behaviors can be
configured:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            secrets:
                #vault_directory: '%kernel.project_dir%/config/secrets/%kernel.environment%'
                #local_dotenv_file: '%kernel.project_dir%/.env.local'
                #decryption_env_var: 'base64:default::SYMFONY_DECRYPTION_SECRET'

    .. code-block:: xml

            <!-- config/packages/framework.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:framework="http://symfony.com/schema/dic/framework"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/framework https://symfony.com/schema/dic/framework/framework-1.0.xsd"
            >
                <framework:config secret="%env(APP_SECRET)%">
                    <framework:secrets
                        vault_directory="%kernel.project_dir%/config/secrets/%kernel.environment%"
                        local_dotenv_file="%kernel.project_dir%/.env.local"
                        decryption_env_var="base64:default::SYMFONY_DECRYPTION_SECRET"
                    />
                </framework:config>
            </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', [
            'secrets' => [
                // 'vault_directory' => '%kernel.project_dir%/config/secrets/%kernel.environment%',
                // 'local_dotenv_file' => '%kernel.project_dir%/.env.local',
                // 'decryption_env_var' => 'base64:default::SYMFONY_DECRYPTION_SECRET',
            ],
        ]);

.. _secrets-generate-keys:

Generate Cryptographic Keys
---------------------------

In order to encrypt and decrypt **secrets**, symfony needs **cryptographic keys**.
This can be done with the provided command ``secrets:generate-keys``.

.. code-block:: terminal

    $ APP_ENV=prod php bin/console secrets:generate-keys

This command generates a new pair of asymetric **cryptographic keys** in
``%kernel.project_dir%/config/secrets/%kernel.environment%``.
The **encryption key** is stored in the ``%kernel.environment%.sodium.encrypt.public``
file, it is used to encrypt secrets when developers add or update secrets. This
key can be committed. The **decryption key** stored in the
``%kernel.environment%.sodium.decrypt.private`` file, it is used to decrypt
secrets and provide the revealed values to services. The number of people who
have access this key should be as small as possible.

.. caution::

    The ``prod.sodium.decrypt.private`` file is sensitive and **should not** be
    committed nor publicly shared. Your team developers and Continuous
    Integration services don't need that key. If the **decryption key** has
    been exposed (ex-employee leaving for instance), you should consider
    generating a new one with the command ``secrets:generate-keys --rotate``.

.. _secrets-set:

Create or Update Secrets
------------------------

You can add new secrets with the command ``secrets:set``. Symfony will ask you,
in a hidden prompt, to enter the value. Symfony will encrypt this value and
store it in a file located by default in the folder
``%kernel.project_dir%/config/secrets/%kernel.environment%``.
This file should be committed along side the other project's files.

.. code-block:: terminal

    # create a "DATABASE_PASSWORD" secret interactively
    $ php bin/console secrets:set DATABASE_PASSWORD

    # create secret for the "prod" environment
    $ APP_ENV=prod php bin/console secrets:set DATABASE_PASSWORD

    # provide a file where to read the secret from
    $ php bin/console secrets:set APPLICATION_CREDENTIAL ~/Download/key.json

    # or contents passed to STDIN
    $ echo -n "$AWS_SECRET_ACCESS_KEY" | php bin/console secrets:set AWS_KEY -

    # or let Symfony generate a random one for you
    $ php bin/console secrets:set REMEMBER_ME --random

If the secret already exists, its value will be overridden by the new one.

.. tip::

    The ``--random`` flag will display the generated value in the output.
    On Linux, you can use the ``xclip`` command to store it directly in your
    clipboard:

    .. code-block:: terminal

        $ php bin/console secrets:set REMEMBER_ME --random | xclip -selection c

.. _secrets-reference:

Referencing Secrets in Configuration Files
------------------------------------------

You can reference the secrets in any configuration files by prefixing their names
using the **secret** :ref:`environment variable processors <env-var-processors>`.
Their actual values will be resolved at runtime, so that container compilation
and cache warmup don't need the **decryption key**.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                password: '%env(secret:DATABASE_PASSWORD)%'
                # ...
            # ...

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal
                    password="%env(secret:DATABASE_PASSWORD)%"
                />
            </doctrine:config>

        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'password' => '%env(secret:DATABASE_PASSWORD)%',
            ]
        ]);

This configuration requires that all environments uses secrets. Each
environment would have its own **cryptographic keys** and their own encrypted
secrets.

You can also use parameters to configure different strategies per environnement,
by defining a default plain text secret:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                password: '%database_password%'
                # ...
            # ...

        parameters:
            database_password: 'not a secret'

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal
                    password="%env(secret:DATABASE_PASSWORD)%"
                />
            </doctrine:config>

            <parameters>
                <parameter key="database_password">not a secret</parameter>
            </parameters>

        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'password' => '%env(secret:DATABASE_PASSWORD)%',
            ]
        ]);
        $container->setParameter('database_password', 'not a secret');

Then overriding it in production environment:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/doctrine.yaml
        parameters:
            database_password: '%env(secret:DATABASE_PASSWORD)%'

    .. code-block:: xml

        <!-- config/packages/prod/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <parameters>
                <parameter key="database_password">%env(secret:DATABASE_PASSWORD)%</parameter>
            </parameters>

        </container>

    .. code-block:: php

        // config/packages/prod/doctrine.php
        $container->setParameter('database_password', '%env(secret:DATABASE_PASSWORD)%');

.. _secrets-list:

List Existing Secrets
---------------------

Everybody is allowed to list the Secrets' names with the command
``secrets:list``.
If you have the **decryption key** you can also reveal the secrets' values by
passing the option ``--reveal`` to the command:

.. code-block:: terminal

    $ php bin/console secrets:list --reveal

     ------------------- ------------
      Name                Value
     ------------------- ------------
      DATABASE_PASSWORD   "my secret"
     ------------------- ------------

Remove Secrets
--------------

Symfony provides a convenient command to remove a Secret:

.. code-block:: terminal

    $ php bin/console secrets:remove DATABASE_PASSWORD

Rotate Secrets
--------------

The ``secrets:generate-keys`` command provides an ``--rotate`` option to
regenerate the **cryptographic keys**.
Symfony will decrypt previous secrets with the old key, generate new
**cryptographic keys** and re-encrypt secrets with the new key.
In order to decrypt previous secrets, the developper must have the
**decryption key**.

Local secrets
-------------

It's common for developpers to use their own privates secrets (for
instance a Github token, an Ldap password, or a personnal AWS access key, ...).

The ``secrets:set`` and ``secrets:remove`` commands provide an ``--local``
option that stores the secrets in the local ``.env.local`` file like a standard
environment variable suffixed with ``_SECRET``.

This environment variable will take precedence over the original secret (if
exists).

.. code-block:: terminal

    $ echo -n "root" | php bin/console secrets:set DATABASE_PASSWORD -

The ``.env.local`` file will look like:

.. code-block:: bash

    DATABASE_PASSWORD_SECRET=root

Listing the secrets will now display the local variable too.

.. code-block:: terminal

    $ php bin/console secrets:remove DATABASE_PASSWORD
     ------------------- ------------- -------------
      Name                Value         Local Value
     ------------------- ------------- -------------
      DATABASE_PASSWORD   "my secret"   "root"
     ------------------- ------------- -------------

In addition, Symfony provides the ``secrets:decrypt-to-local``command, it's
decrypts all secrets and stores them in the local vault. Symfony also provides
the ``secrets:encrypt-from-local`` command, it's encrypts all local secrets to
the vault.

.. _secrets-deploy

Deploy secrets to production
----------------------------

As the **decryption key** is not committed, during development, you'll have to
manually deploy it (once and for all). You have 2 ways to do it.

1) uploading the file

The first way, is to copy the **decryption key** file stored in
``%kernel.project_dir%/config/secrets/%kernel.environment%/%kernel.environment%.sodium.decrypt.private``
on the servers.

2) Using env variable

The second way is to set the ``SYMFONY_DECRYPTION_SECRET`` environment variable
with the base64 encoded value of the **encryption key**.

A fancy way to fetch the value of the key is:

.. code-block:: terminal

    $ php -r 'echo base64_encode(require "config/secrets/prod/prod.sodium.decrypt.private");'

.. _`libsodium`: https://pecl.php.net/package/libsodium
.. _
`sodium_compatparagonie/sodium_compat https://packagist.org/packages/paragonie/sodium_compat

To improve performance, you can also decrypt all secrets and store them in the
local vault with the command:

.. code-block:: terminal

    $ php bin/console secrets:decrypt-to-local --force
