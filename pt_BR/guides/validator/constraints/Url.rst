Url
===

Valida se o valor é uma URL válida.

.. code-block:: yaml

    properties:
        website:
            - Url: ~

Opções
------

* ``protocols``: Uma lista de protocolos permitidos. Padrão: "http", "https", "ftp"
  e "ftps".
* ``message``: A mensagem de erro se a validação falhar
