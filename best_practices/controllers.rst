Controllers
===========

Symfony follows the philosophy of *"thin controllers and fat models"*. This
means that controllers should hold just the thin layer of *glue-code*
needed to coordinate the different parts of the application.

As a rule of thumb, you should follow the 5-10-20 rule, where controllers should
only define 5 variables or less, contain 10 actions or less and include 20 lines
of code or less in each action. This isn't an exact science, but it should
help you realize when code should be refactored out of the controller and
into a service.

.. best-practice::

    Make your controller extend the FrameworkBundle base controller and use
    annotations to configure routing, caching and security whenever possible.

Coupling the controllers to the underlying framework allows you to leverage
all of its features and increases your productivity.

And since your controllers should be thin and contain nothing more than a
few lines of *glue-code*, spending hours trying to decouple them from your
framework doesn't benefit you in the long run. The amount of time *wasted*
isn't worth the benefit.

In addition, using annotations for routing, caching and security simplifies
configuration. You don't need to browse tens of files created with different
formats (YAML, XML, PHP): all the configuration is just where you need it
and it only uses one format.

Overall, this means you should aggressively decouple your business logic
from the framework while, at the same time, aggressively coupling your controllers
and routing *to* the framework in order to get the most out of it.

Routing Configuration
---------------------

To load routes defined as annotations in your controllers, add the following
configuration to the main routing configuration file:

.. code-block:: yaml

    # app/config/routing.yml
    app:
        resource: '@AppBundle/Controller/'
        type:     annotation

This configuration will load annotations from any controller stored inside the
``src/AppBundle/Controller/`` directory and even from its subdirectories.
So if your application defines lots of controllers, it's perfectly ok to
reorganize them into subdirectories:

.. code-block:: text

    <your-project>/
    ├─ ...
    └─ src/
       └─ AppBundle/
          ├─ ...
          └─ Controller/
             ├─ DefaultController.php
             ├─ ...
             ├─ Api/
             │  ├─ ...
             │  └─ ...
             └─ Backend/
                ├─ ...
                └─ ...

Template Configuration
----------------------

.. best-practice::

    Don't use the ``@Template()`` annotation to configure the template used by
    the controller.

The ``@Template`` annotation is useful, but also involves some magic. We
don't think its benefit is worth the magic, and so recommend against using
it.

Most of the time, ``@Template`` is used without any parameters, which makes
it more difficult to know which template is being rendered. It also makes
it less obvious to beginners that a controller should always return a Response
object (unless you're using a view layer).

How the Controller Looks
------------------------

Considering all this, here is an example of how the controller should look
for the homepage of our app:

.. code-block:: php

    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    class DefaultController extends Controller
    {
        /**
         * @Route("/", name="homepage")
         */
        public function indexAction()
        {
            $posts = $this->getDoctrine()
                ->getRepository('AppBundle:Post')
                ->findLatest();

            return $this->render('default/index.html.twig', array(
                'posts' => $posts
            ));
        }
    }

.. _best-practices-paramconverter:

Using the ParamConverter
------------------------

If you're using Doctrine, then you can *optionally* use the `ParamConverter`_
to automatically query for an entity and pass it as an argument to your controller.

.. best-practice::

    Use the ParamConverter trick to automatically query for Doctrine entities
    when it's simple and convenient.

For example:

.. code-block:: php

    use AppBundle\Entity\Post;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    /**
     * @Route("/{id}", name="admin_post_show")
     */
    public function showAction(Post $post)
    {
        $deleteForm = $this->createDeleteForm($post);

        return $this->render('admin/post/show.html.twig', array(
            'post'        => $post,
            'delete_form' => $deleteForm->createView(),
        ));
    }

Normally, you'd expect a ``$id`` argument to ``showAction``. Instead, by
creating a new argument (``$post``) and type-hinting it with the ``Post``
class (which is a Doctrine entity), the ParamConverter automatically queries
for an object whose ``$id`` property matches the ``{id}`` value. It will
also show a 404 page if no ``Post`` can be found.

When Things Get More Advanced
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The above example works without any configuration because the wildcard name ``{id}`` matches
the name of the property on the entity. If this isn't true, or if you have
even more complex logic, the easiest thing to do is just query for the entity
manually. In our application, we have this situation in ``CommentController``:

.. code-block:: php

    /**
     * @Route("/comment/{postSlug}/new", name = "comment_new")
     */
    public function newAction(Request $request, $postSlug)
    {
        $post = $this->getDoctrine()
            ->getRepository('AppBundle:Post')
            ->findOneBy(array('slug' => $postSlug));

        if (!$post) {
            throw $this->createNotFoundException();
        }

        // ...
    }

You can also use the ``@ParamConverter`` configuration, which is infinitely
flexible:

.. code-block:: php

    use AppBundle\Entity\Post;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
    use Symfony\Component\HttpFoundation\Request;

    /**
     * @Route("/comment/{postSlug}/new", name = "comment_new")
     * @ParamConverter("post", options={"mapping": {"postSlug": "slug"}})
     */
    public function newAction(Request $request, Post $post)
    {
        // ...
    }

The point is this: the ParamConverter shortcut is great for simple situations.
But you shouldn't forget that querying for entities directly is still very
easy.

Pre and Post Hooks
------------------

If you need to execute some code before or after the execution of your controllers,
you can use the EventDispatcher component to
:doc:`set up before and after filters </cookbook/event_dispatcher/before_after_filters>`.

.. _`ParamConverter`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
