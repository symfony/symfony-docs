Email
=====

Valida se o valor é um endereço válido de e-mail.

.. code-block:: yaml

    properties:
        email:
            - Email: ~

Options
-------

* ``checkMX``: Se os registros MX devem ser verificados para o dominio. Padrão: ``false``
* ``message``: A mensagem de erro se a validação falhar
