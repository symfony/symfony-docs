.. index::
    single: Validation; Validating request inputs

How to Validate Request Inputs
==============================

Most of the time, you first validate a request to see if the data
sent is valid. To end the call early with a "BadRequestResponse"
in case of incorrect data.

With the attribute RequestValidator in front of your action method
you can fill a previously defined request class with the sent data
and validate it automatically. If the data is incorrect, an exception
is thrown before the request arrives in the controller method.

RequestClass
------------

A very simple example of a GetUserRequest would look like this:

    // src/Request/GetUserRequest.php
    namespace App\Request;

    use Symfony\Component\Validator\Constraints as Assert;

    class GetUserRequest
    {
        #[Assert\Type(type: 'digit')]
        #[Assert\NotBlank]
        public $id;

        public function getId(): int
        {
            return intval($this->id);
        }
    }

ControllerClass
---------------

And the controller would look like this in the above example:

    // src/Controller/GetUserAction.php
    namespace App\Controller;

    use App\Request\GetUserRequest;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Validator\Attribute\RequestValidator;

    class GetUserAction
    {
        #[Route(path: '/user', methods: ['GET']]
        #RequestValidator[class: GetUserRequest::class]
        public __invoke(GetUserRequest $request)
        {
            $userId = $request->getId();
            ...
        }
    }
