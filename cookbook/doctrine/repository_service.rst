.. index::
   single: Doctrine; Service

How to turn your Doctrine repository into a service
===================================================

Working with repositories can be a pretty messy task. Sometimes it is easier to
perform certain things right in the repository instead of pulling
the data and handle it in other places.
Since this often requires to have other services at hand it is a good
idea to turn your standard Doctrine repository into a service.

First, you need to create a repository. The process is pretty straight forward.
Assume you need to log something, your repository will look like::

    // src/Acme/BlogBundle/Entity/PostRepository.php
    namespace Acme\BlogBundle\Entity;
	
    use Doctrine\Common\Persistence\Mapping\ClassMetadata;
    use Doctrine\ORM\EntityRepository;
    use Doctrine\ORM\EntityManager;
	
    class PostRepository extends EntityRepository
    {
        public function __construct(EntityManager $em, ClassMetadata $class)
        {
            parent::__construct($em, $class);
	
            // your own logic...
        }
        
        // ...
    }
	 
Normally, you would retrieve the repository using the ``getRepository()`` method of the
entity manager. You can simulate this behaviour by configuring a factory method for the
repository service. See :doc:`/components/dependency_injection/factories` for more
information about factories.

To configure the PostRepository, use something like:

.. configuration-block::

    .. code-block:: yaml
    
   	    # src/Acme/BlogBundle/Resources/config/config.yml
        services:
        acme.blog.repository.event:
            class: Acme\BlogBundle\Entity\PostRepository
            factory_service: doctrine.orm.entity_manager
            factory_method: getRepository
            arguments: ['Acme\BlogBundle\Entity\Blog']

    .. code-block:: xml
    
        <!-- src/Acme/BlogBundle/Resources/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <service id="acme.blog.repository.post"
                    class="Acme\BlogBundle\Entity\PostRepository"
                    factory-service="doctrine.orm.entity_manager"
                    factory-method="getRepository">
                    <argument>Acme\BlogBundle\Entity\Blog</argument>
                </service>
            </services>
        </container>
	
    .. code-block:: php
    
    	// src/Acme/BlogBundle/Resources/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $definition = new Definition('Acme\BlogBundle\Entity\PostRepository', array(
            'Acme\BlogBundle\Entity\Blog',
        ));
        $definition->setFactoryService('doctrine.orm.entity_manager');
        $definition->setFactoryMethod('getRepository');

        $container->setDefinition('acme.blog.repository.post', $definition);
    	
    
Finally, use your repository in your standard controller::

    // src/Acme/BlogBundle/Controller/PostController.php
    namespace Acme\BlogBundle\Controller;
	
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Acme\BlogBundle\Entity\Post;
    use Acme\BlogBundle\Entity\PostRepository;
	
    class PostController extends Controller
    {
        public function indexAction()
        {
            /* @var $repository PostRepository */
            $repository = $this->get('acme.blog.repository.post');
            $posts = $repository->findAll();
	
            return $this->render('AcmeBlogBundle:Blog:index.html.twig', array(
                'posts' => $posts
            ));
        }
	    
        // ...
    }
