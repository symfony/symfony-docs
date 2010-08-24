<?php

namespace Doctrine\ODM\MongoDB\Tools\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * @author Bulat Shakirzyanov <bulat@theopenskyproject.com>
 */
class DocumentManagerHelper extends Helper
{
    protected $dm;
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }
    public function getDocumentManager()
    {
        return $this->dm;
    }
    public function getName()
    {
        return 'documentManager';
    }
}