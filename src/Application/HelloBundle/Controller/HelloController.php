<?php

namespace Application\HelloBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class HelloController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('HelloBundle:Hello:index', array('name' => $name));
    }
}
