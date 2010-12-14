MinLength
=========

Valida se o tamanho de uma string não é menor do que o limite atribuido.

.. code-block:: yaml

    properties:
        firstName:
            - MinLength: 3

Opções
------

* ``limit`` (**padrão**, requirido): O limite
* ``message``: A mensagem de erro se a validação falhar
