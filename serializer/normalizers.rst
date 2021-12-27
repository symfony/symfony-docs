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

Symfony includes several types of :ref:`built-in normalizers <component-serializer-normalizers>`
but you can also :doc:`create your own normalizer </serializer/custom_normalizer>`.
