.. index::
   single: Serializer; Custom context builders

How to Create your Custom Context Builder
=========================================

The :doc:`Serializer Component </components/serializer>` uses Normalizers
and Encoders to transform any data to any data-structure (e.g. JSON).
That serialization process could be configured thanks to a
:ref:`serialization context <serializer-context>`, which can be built thanks to
:ref:`context builders <component-serializer-context-builders>`.

Each built-in normalizer/encoder has its related context builder.
But, as an example, you may want to use custom context values
for your :doc:`custom normalizers </serializer/custom_normalizer>`
and create a custom context builder related to them.

Creating a new context builder
------------------------------

Let's imagine that you want to handle date denormalization differently if they
are coming from a legacy system, by converting them to ``null`` if the serialized
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

You'll therefore be able to cast zero-ish dates to ``null`` during denormalization::

    $legacyData = '{"updatedAt": "0000-00-00"}';

    $serializer->deserialize($legacyData, MyModel::class, 'json', ['zero_datetime_to_null' => true]);

Then, if you don't want other developers to have to remind the precise ``zero_date_to_null`` context key,
you can create a dedicated context builder::

    // src/Serializer/LegacyContextBuilder
    namespace App\Serializer;

    use Symfony\Component\Serializer\Context\ContextBuilderTrait;

    final class LegacyContextBuilder
    {
        use ContextBuilderTrait;

        public function withLegacyDates(bool $legacy): static
        {
            return $this->with('zero_datetime_to_null', $legacy);
        }
    }

And finally use it to build the serialization context::

    $legacyData = '{"updatedAt": "0000-00-00"}';

    $context = (new LegacyContextBuilder())
        ->withLegacyDates(true)
        ->toArray();

    $serializer->deserialize($legacyData, MyModel::class, 'json', $context);
