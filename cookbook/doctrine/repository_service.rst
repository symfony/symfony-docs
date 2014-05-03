.. index::
   single: Doctrine; Service

How to turn your Doctrine repository into a service
===================================================

Working with repositories can be a pretty messy task. Sometimes it is more
logic to perform certain things right in the repository instead of pulling
the data and handle it in other places.
Since this often requires to have other services at hand it is a good
idea to turn your standard Doctrine repository into a service.

Let's start with the repository itself. The process is pretty straight forward:
if you might need to log something, here is the how-to too::

    // src/Acme/BlogBundle/Entity/Repository.php;
    
    namespace Acme\BlogBundle\Entity\Repository;
	
    use Psr\Log\NullLogger;
    use Psr\Log\LoggerInterface;
    use Doctrine\Common\Persistence\Mapping\ClassMetadata;
    use Doctrine\ORM\EntityRepository;
    use Doctrine\ORM\EntityManager;
	
    class PostRepository extends EntityRepository
    {
	private $logger; /** @var LoggerInterface */
	
        public function __construct(EntityManager $em, ClassMetadata $class)
        {
            parent::__construct($em, $class);
	
            $this->logger = new NullLogger();
        }
	
        public function setLogger(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }
        
        // ...
    }
	 
Proceed with creating the service definition:

.. configuration-block::

    .. code-block:: yaml
    
   	# src/Acme/BlogBundle/Resources/config/config.yml
    	# todo

    .. code-block:: xml
    
    	<!-- src/Acme/BlogBundle/Resources/config/config.xml -->
	<container xmlns="http://symfony.com/schema/dic/services"
	           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	           xsi:schemaLocation="http://symfony.com/schema/dic/services/services-1.0.xsd">
		<services>
	        <service id="acme.blog.repository.event"
	            class="Acme\BlogBundle\Entity\Repository\PostRepository"
	            factory-service="doctrine.orm.entity_manager"
	            factory-method="getRepository">
	            <argument>Acme\BlogBundle\Entity\Blog</argument>
	            
	            <call method="setLogger">
			<argument type="service" id="logger" />
		    </call>
	        </service>
	    </services>
	</container>
	
    .. code-block:: php
    
    	// src/Acme/BlogBundle/Resources/config/config.php
    	// @todo
    	
    
Finally, use your repository in your standard controller::

    // src/Acme/BlogBundle/Controller/PostController.php
    
    namespace Acme\BlogBundle\Controller;
	
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Acme\BlogBundle\Entity\Post;
    use Acme\BlogBundle\Entity\Repository\PostRepository;
	
    class PostController extends Controller
    {
        public function indexAction()
        {
            /* @var $repository PostRepository */
            $repository = $this->get('acme.blog.repository.post');
            $entities = $repository->findAll();
	
            return $this->render('AcmeBlogBundle:Blog:index.html.twig', array(
                'entities' => $entities
            ));
        }
	    
        // ...
    }
	 
