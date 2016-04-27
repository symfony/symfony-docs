.. index::
   single: Security, Normalizers

Normalizers
===========

Normalizers turn **objects** into **arrays** and vice-versa.
They implement :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface` for normalizing (object to array) and :class:`Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface` for denormalizing (array to object).

You can add new normalizers to a Serializer instance by using its first constructor argument::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

    $normalizers = array(new ArrayDenormalizer(), new GetSetMethodNormalizer());
    $serializer = new Serializer($normalizers);

Built-in encoders
-----------------

The Serializer Component provides several normalizers for most use cases:
    *
