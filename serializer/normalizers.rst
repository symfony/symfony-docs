.. index::
   single: Serializer, Normalizers

Normalizers
===========

Normalizers turn **objects** into **arrays** and vice versa. They implement
:class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface` for
normalizing (object to array) and
:class:`Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface` for
denormalizing (array to object).

Normalizers are enabled in the serializer passing them as its first argument::

    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $normalizers = [new ObjectNormalizer()];
    $serializer = new Serializer($normalizers);

Built-in Normalizers
--------------------

Symfony includes the following normalizers but you can also
:doc:`create your own normalizer </serializer/custom_normalizer>`:

* :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer` to
  normalize PHP object using the :doc:`PropertyAccessor component </components/property_access>`;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\CustomNormalizer` to
  normalize PHP object using an object that implements
  :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizableInterface`;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer` to
  normalize PHP object using the getter and setter methods of the object;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer` to
  normalize PHP object using `PHP reflection`_.

.. _`PHP reflection`: https://php.net/manual/en/book.reflection.php
