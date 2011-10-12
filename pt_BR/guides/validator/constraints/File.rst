File
====

Valida se o valor é um caminho para um arquivo existente.

.. code-block:: yaml

    properties:
        filename:
            - File: ~

Opções
------

* ``maxSize``: O tamanho máximo permitido para o arquivo. Pode ser em bytes, kilobytes
  (com o sufixo "k") ou megabytes (com o sufixo "M")
* ``mimeTypes``: Um ou mais mime types permitidos
* ``notFoundMessage``: A mensagem de erro caso o arquivo não seja encontrado
* ``notReadableMessage``: A mensagem de erro se o arquivo não puder ser lido
* ``maxSizeMessage``: A mensagem de erro caso a validação do ``maxSize`` falhar
* ``mimeTypesMessage``: A mensagem de erro caso a validação do ``mimeTypes`` falhar

Exemplo: Validando o taamanho do arquivo e o mime type
------------------------------------------------------

Nesse exemplo nos usamos a restrição ``File`` para se certificar de que o arquivo não
excedeu o tamanho máximo de 128 kilobytes e que ele é um documento PDF.

.. configuration-block::

    .. code-block:: yaml

        properties:
            filename:
                - File: { maxSize: 128k, mimeTypes: [application/pdf, application/x-pdf] }

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="filename">
                <constraint name="File">
                    <option name="maxSize">128k</option>
                    <option name="mimeTypes">
                        <value>application/pdf</value>
                        <value>application/x-pdf</value>
                    </option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:File(maxSize = "128k", mimeTypes = {
             *   "application/pdf",
             *   "application/x-pdf"
             * })
             */
            private $filename;
        }

Quando você valida o objeto com um arquivo que não satisfaz uma das 
restrições, a mensagem apropriada é retornada pelo validator:

.. code-block:: text

    Application\HelloBundle\Author.filename:
        The file is too large (150 kB). Allowed maximum size is 128 kB
