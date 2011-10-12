MaxLength
=========

Valida se o tamanho de uma string não é maior do que o limite atribuido.

.. code-block:: yaml

    properties:
        firstName:
            - MaxLength: 20

Options
-------

* ``limit`` (**padrão**, requirido): O limite
* ``message``: A mensagem de erro se a validação falhar
