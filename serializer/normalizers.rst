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
  normalize PHP object using the :doc:`PropertyAccess component </components/property_access>`;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeZoneNormalizer`
  for :phpclass:`DateTimeZone` objects;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeNormalizer` for
  objects implementing the :phpclass:`DateTimeInterface` interface;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateIntervalNormalizer`
  for :phpclass:`DateInterval` objects;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer` to
  transform :phpclass:`SplFileInfo` objects in `Data URIs`_;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\CustomNormalizer` to
  normalize PHP object using an object that implements
  :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizableInterface`;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\FormErrorNormalizer` for
  objects implementing the :class:`Symfony\\Component\\Form\\FormInterface` to
  normalize form errors;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer` to
  normalize PHP object using the getter and setter methods of the object;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer` to
  normalize PHP object using `PHP reflection`_;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ConstraintViolationListNormalizer` for objects implementing the :class:`Symfony\\Component\\Validator\\ConstraintViolationListInterface` interface;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ProblemNormalizer` for :class:`Symfony\\Component\\ErrorHandler\\Exception\\FlattenException` objects
* :class:`Symfony\\Component\\Serializer\\Normalizer\\JsonSerializableNormalizer`
  to deal with objects implementing the :phpclass:`JsonSerializable` interface;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\UidNormalizer` converts objects that implement :class:`Symfony\\Component\\Uid\\AbstractUid` into strings and denormalizes uuid or ulid strings to :class:`Symfony\\Component\\Uid\\Uuid` or :class:`Symfony\\Component\\Uid\\Ulid`.


.. _`Data URIs`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs
.. _`PHP reflection`: https://php.net/manual/en/book.reflection.php
