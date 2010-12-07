Min
===

Valida se o valor não é menor que o limite atribuido.

.. code-block:: yaml

    properties:
        age:
            - Min: 1

Opções
------

* ``limit`` (**padrão**, requirido): O limite
* ``message``: A mensagem de erro se a validação falhar
