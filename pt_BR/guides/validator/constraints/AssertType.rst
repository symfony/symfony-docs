AssertType
==========

Valida se o valor tem um tipo especifico

.. code-block:: yaml

    properties:
        age:
            - AssertType: integer

Opções
------

* ``type`` (**padrão**, requirido): O nome completo de uma classe ou um tipo datatype
 do PHP determinado pelas funções ``is_`` do PHP.

  * `array <http://php.net/is_array>`_
  * `bool <http://php.net/is_bool>`_
  * `callable <http://php.net/is_callable>`_
  * `float <http://php.net/is_float>`_ 
  * `double <http://php.net/is_double>`_
  * `int <http://php.net/is_int>`_ 
  * `integer <http://php.net/is_integer>`_
  * `long <http://php.net/is_long>`_
  * `null <http://php.net/is_null>`_
  * `numeric <http://php.net/is_numeric>`_
  * `object <http://php.net/is_object>`_
  * `real <http://php.net/is_real>`_
  * `resource <http://php.net/is_resource>`_
  * `scalar <http://php.net/is_scalar>`_
  * `string <http://php.net/is_string>`_
* ``message``: A mensagem de erro se a validação falhar
