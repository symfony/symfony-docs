Max
===

Valida se o valor não é maior que o limite atribuido.

.. code-block:: yaml

    properties:
        age:
            - Max: 99

Opções
------

* ``limit`` (**padrão**, requirido): O limite
* ``message``: A mensagem de erro se a validação falhar
