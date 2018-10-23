.. index::
   single: Serializer, Normalizers

Normalizers
===========

Normalizer basically turn **objects** into **array** and vice versa.
They implement
:class:`Symfony\\Component\\Serializer\\Normalizers\\NormalizerInterface` for
normalizing (object to array) and
:class:`Symfony\\Component\\Serializer\\Normalizers\\DenormalizerInterface` for
denormalizing (object to array).

You can add new normalizers to a Serializer instance by using its first constructor argument::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    $normalizers = array(new ObjectNormalizer());
    $serializer = new Serializer($normalizers);

Built-in Normalizers
--------------------

* :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer` to normalizer PHP object using the PropertyAccessor component;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\CustomNormalizer` to normalizer PHP object using object that implements ``:class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizableInterface``;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer` to normalizer PHP object using getter and setter of the object;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer` to normalizer PHP object using PHP reflection.
