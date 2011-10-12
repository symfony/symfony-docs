NotBlank
========

Valida se o valor não é vazio (como determinado no construtor `empty
<http://php.net/empty>`_).

.. code-block:: yaml

    properties:
        firstName:
            - NotBlank: ~

Opções
------

* ``message``: A mensagem de erro se a validação falhar
