.. index::
    single: Controller; Rest API

How to extract JSON data from POST, PATCH and PUT Request
=========================================================

There are several ways of  building a RESTfull API in Symfony. And especially in receiving JSON payload.
One of them is using directly Symfony :doc:`Serializer Component </serializer>`

For example someone is sending this JSON payload to your API:

.. code-block:: json

    {
      "id": "fabd5e92-02e7-43f7-a962-adab8ec88e94",
      "name": "Product1"
    }


If you create a ``Data Transfer Object`` representing your JSON payload. You can have the received JSON automatically mapped to your DTO::

    final class CreateProductRequestPayload
    {
        public string $id;
        public string $name;
    }


By using in your :doc:`Controller </controller>`::

    /**
     * @Route("products.json", methods={"POST"})
     */
    public function new(Request $request): JsonResponse
    {
        /** @var CreateProductRequestPayload $requestPayload */
        $requestPayload = $this->serializer->deserialize(
            $request->getContent(),
            CreateProductRequestPayload::class, // Your DTO
            'json',
        );

        // Your Logic
    }


This uses ``deserialize()`` method provided by the
:class:`Symfony\\Component\\Serializer\\SerializerInterface`
class.

.. caution::

    You could also directly map your JSON to your ``Entity``.
    However keep in mind that you would then have a tight coupling between
    - the way your data is received by your API
    - and the way it is stored in your database.
    This might reduce your application maintainability on the long run.

The example is on the ``POST`` HTTP method.
It works for ``PATCH`` and ``PUT`` method too.
