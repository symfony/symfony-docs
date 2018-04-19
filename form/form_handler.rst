How to work with Form Handler
=============================

Usually, handling a form submit usually follows a similar template::

.. code-block:: php

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
or even a multiple data transformation ?

Well, is this particular cases, handling the form logic inside the controller
is a pretty bad idea (even more if you want to follow the 5-10-20 rules).
In order to keep your controller thin while the rest of your application
continue to grow, a FormHandler can help you.

So, what is a FormHandler ?

In it's purest expression, a FormHandler is a service which wait for your form and
call everything you need to manage the handling process, here's a exemple:

.. code-block:: php

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
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($post);
                $entityManager->flush();

                return true;
            }

            return false;
        }
    }

Here's a simple exemple of what can do a FormHandler but you can inject every services
that you need.
Once in the controller, the logic can be simplified by the call of the PostTypeHandler:

.. code-block:: php

    <?php

    //...
    use App\Form\Handler\PostTypeHandler;

    // ...

    public function new(Request $request, PostTypeHandler $handler)
    {
        // build the form ...

        $form->handleRequest($request);

        if ($handler->handle($form)) {
            return $this->redirectToRoute('admin_post_show', [
                'id' => $post->getId()
            ]);
        }

        // render the template
    }

Keep in mind that a FormHandler is just a service, your controller can easily
call it using the ``controler.service_arguments`` tag and let it handle all the
heavy tasks, this way, your code stay easy to test and maintain.

.. tip::

    Last thing, thanks to the DIC, you can easily define a interface and call it,
    this way, you stay in line with the SOLID principles:

    .. code-block:: php

        <?php

        //...
        use App\Form\Handler\Interfaces\PostTypeHandlerInterface;

        // ...

        public function new(Request $request, PostTypeHandlerInterface $handler)
        {
            // build the form ...

            $form->handleRequest($request);

            if ($handler->handle($form)) {
                return $this->redirectToRoute('admin_post_show', [
                    'id' => $post->getId()
                ]);
            }

            // render the template
        }

Lastly, using a FormHandler can help to improve your testing experience,
as a FormHandler is a simple service, it can be way easier to maintain and unit test
rather than putting all the logic inside the controller.
