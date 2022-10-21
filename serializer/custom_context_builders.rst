.. index::
   single: Serializer; Custom context builders

How to Create your Custom Context Builder
=========================================

.. versionadded:: 6.1

    Context builders were introduced in Symfony 6.1.

The :doc:`Serializer Component </components/serializer>` uses Normalizers
and Encoders to transform any data to any data-structure (e.g. JSON).
That serialization process can be configured thanks to a
:ref:`serialization context <serializer-context>`, which can be built thanks to
:ref:`context builders <component-serializer-context-builders>`.

Each built-in normalizer/encoder has its related context builder. However, you
may want to create a custom context builder for your
:doc:`custom normalizers </serializer/custom_normalizer>`.

Creating a new Context Builder
------------------------------

Let's imagine that you want to handle date denormalization differently if they
are coming from a legacy system, by converting dates to ``null`` if the serialized
value is ``0000-00-00``. To do that you'll first have to create your normalizer::

    // src/Serializer/ZeroDateTimeDenormalizer.php
    namespace App\Serializer;

    use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
    use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
    use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

    final class ZeroDateTimeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
    {
        use DenormalizerAwareTrait;

        public function denormalize($data, string $type, string $format = null, array $context = [])
        {
            if ('0000-00-00' === $data) {
                return null;
            }

            unset($context['zero_datetime_to_null']);

            return $this->denormalizer->denormalize($data, $type, $format, $context);
        }

        public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
        {
            return true === ($context['zero_datetime_to_null'] ?? false)
                && is_a($type, \DateTimeInterface::class, true);
        }
    }

Now you can cast zero-ish dates to ``null`` during denormalization::

    $legacyData = '{"updatedAt": "0000-00-00"}';
    $serializer->deserialize($legacyData, MyModel::class, 'json', ['zero_datetime_to_null' => true]);

Now, to avoid having to remember about this specific ``zero_date_to_null``
context key, you can create a dedicated context builder::

    // src/Serializer/LegacyContextBuilder
    namespace App\Serializer;

    use Symfony\Component\Serializer\Context\ContextBuilderInterface;
    use Symfony\Component\Serializer\Context\ContextBuilderTrait;

    final class LegacyContextBuilder implements ContextBuilderInterface
    {
        use ContextBuilderTrait;

        public function withLegacyDates(bool $legacy): static
        {
            return $this->with('zero_datetime_to_null', $legacy);
        }
    }

And finally, use it to build the serialization context::

    $legacyData = '{"updatedAt": "0000-00-00"}';

    $context = (new LegacyContextBuilder())
        ->withLegacyDates(true)
        ->toArray();

    $serializer->deserialize($legacyData, MyModel::class, 'json', $context);
