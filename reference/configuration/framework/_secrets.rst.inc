secrets
~~~~~~~

decryption_env_var
..................

**type**: ``string`` **default**: ``base64:default::SYMFONY_DECRYPTION_SECRET``

The env var name that contains the vault decryption secret. By default, this
value will be decoded from base64.

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable or not secrets managements.

local_dotenv_file
.................

**type**: ``string`` **default**: ``%kernel.project_dir%/.env.%kernel.environment%.local``

The path to the local ``.env`` file. This file must contain the vault
decryption key, given by the ``decryption_env_var`` option.

vault_directory
...............

**type**: ``string`` **default**: ``%kernel.project_dir%/config/secrets/%kernel.runtime_environment%``

The directory to store the secret vault. By default, the path includes the value
of the :ref:`kernel.runtime_environment <configuration-kernel-runtime-environment>`
parameter.
