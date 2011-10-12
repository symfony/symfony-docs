Regex
=====

Valida se o valor casa com a expressão regular.

.. code-block:: yaml

    properties:
        title:
            - Regex: /\w+/

Opções
------

* ``pattern`` (**padrão**, requirido): A expressão regular
* ``match``: Se o padrão deve casar ou não. Padrão: ``true``
* ``message``: A mensagem de erro se a validação falhar
