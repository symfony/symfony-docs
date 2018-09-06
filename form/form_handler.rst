How to work with Form Handler
=============================

In your day to day application, you deal with controllers (or actions),
in order to ease the form handling process, it's a common practice to
use the FormFactory service and instantiate the Form directly in the controller.
Once the form is ready, it's common to use both `isValid()` and `isSubmitted()`
methods directly in the controller, 
as this is mainly used doesn't mean it's easy to maintain.

Here's a simple example of what is common to use to handle form::

    <?php

    // ...

    public function new(Request $request)
    {
        // build the form ...

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('admin_post_show', [
                'id' => $post->getId()
            ]);
        }

        // render the template
    }


As far as you read this documentation, this approach is valid but what if
you need to call multiples services or if you need to process
a long validation (in this case, a dedicated validator can be a better idea)
or even a multiple data transformation
(for this particular case, a dedicated DataTransformer could be a better idea) ?

Well, is this particular cases, handling the form logic inside the controller
is a pretty bad idea (even more if you want to follow the 5-10-20 rules).
In order to keep your controller thin while the rest of your application
continue to growth, a FormHandler can help you.

What is a FormHandler ?
-----------------------

In it's purest expression, a FormHandler is a service which wait for your form submission
and then call everything you need to manage the handling process, here's a example::

    <?php

    namespace App\Form\Handler;

    class PostTypeHandler
    {
        private $entityManager;

        public function __construct(EntityManagerInterface $entityManager)
        {
            $this->entityManager = $entityManager;
        }

        public function handle(FormInterface $postType): bool
        {
            if ($postType->isSubmitted() && $postType->isValid()) {
                $this->entityManager->persist($post);
                $this->entityManager->flush();

                return true;
            }

            return false;
        }
    }

.. note::

    Keep in mind that this example is purely made in order to demonstrate the usage,
    don't use this example as a strict guideline, the internal logic is up to you
    (as long as the handler stay inline with the SRP principle).

.. tip::

    You may ask : What if I need to return a response depending on the handler logic ?
    As you know, Symfony is a HTTP framework, this way, we highly encourage to keep the
    request/response usage inside the controller, if you really need to return a response,
    you can easily use the boolean and adapt your controller according to it.

Here, the FormHandler only wait for the EntityManager to persist the hydrated entity
but you can call every service that you need.
Once in the controller, the logic can be simplified by the call of the PostTypeHandler::

    <?php

    //...
    use App\Form\Handler\PostTypeHandler;

    // ...        
    
    public function __construct(PostTypeHandler $typeHandler)
    { 
        $this->typeHandler = $typeHandler;
    }

    public function new(Request $request)
    {
        $form = $this->get('form.factory')->create(FormType::class)->handleRequest($request);

        if ($this->handler->handle($form)) {
            return $this->redirectToRoute('admin_post_show', [
                'id' => $post->getId()
            ]);
        }

        // render the template
    }

.. tip::

    As the "action injection" is probably a bad idea if you need to keep
    your code clean, we recommend to use the constructor injection.

Keep in mind that a FormHandler is just a service, the goal here
is to keep the controller focused on what it need to do : Transform
a Request into a Response.
The FormHandler is not really coupled with the Form as the fact that 
it wait for a FormInterface, if most of the FormHandler use a similar
method signature, it can be a great idea to create a common interface
or use a "HandlerFactory" and retrieve the Handler linked to the submitted form.

.. tip::

    Last thing, thanks to the DIC, you can easily define a interface and call it,
    this way, you stay in line with the SOLID principles::

        <?php

        //...
        use App\Form\Handler\Interfaces\PostTypeHandlerInterface;

        // ...
        
        public function __construct(PostTypeHandlerInterface $typeHandler)
        { 
            $this->typeHandler = $typeHandler;
        }

        public function new(Request $request)
        {
            $form = $this->get('form.factory')->create(FormType::class)->handleRequest($request);

            if ($this->handler->handle($form)) {
                return $this->redirectToRoute('admin_post_show', [
                    'id' => $post->getId()
                ]);
            }

            // render the template
        }

Lastly, using a FormHandler can help to improve your testing experience,
as a FormHandler is a simple service, it can be way easier to maintain and unit test
rather than putting all the logic inside the controller.
