DateTime
========

Valida se o valor é uma data-hora no formato "YYYY-MM-DD HH:MM:SS".

.. code-block:: yaml

    properties:
        createdAt:
            - DateTime: ~

Opções
------

* ``message``: A mensagem de erro se a validação falhar
