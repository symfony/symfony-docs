<?php

namespace Doctrine\ODM\MongoDB\Tools\Console\Command;

use Symfony\Components\Console\Command\Command;
use Symfony\Components\Console\Input;
use Symfony\Components\Console\Output;

/**
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class IndexesCommand extends Command
{
    const CREATE  = 'ensure';
    const DROP    = 'delete';
    const REPLACE = 'replace';

    protected function configure()
    {
        $this
            ->setName('odm:mongodb:indexes')
            ->setDescription('Update indexes for all classes or for a specific document')
            ->setDefinition(array(
                new Input\InputOption('mode', 'm', Input\InputOption::PARAMETER_REQUIRED, 'allows to \'' . self::CREATE . '\', \'' . self::DROP . '\', \'' . self::REPLACE . '\' all indexes for a document', self::CREATE),
                new Input\InputOption('class', 'c', Input\InputOption::PARAMETER_OPTIONAL, 'the class name to update indexes for', null),
            ))
        ;
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output)
    {
        try {
            $output->writeln('<info>' . $this->runIndexUpdates($input->getOption('mode'), $input->getOption('class')) . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @param string $mode
     * @param string $className
     * @return string
     */
    protected function runIndexUpdates($mode, $className = null)
    {
        $modes = array(self::CREATE, self::DROP, self::REPLACE);

        if (! in_array($mode, $modes)) {
            throw new \InvalidArgumentException(sprintf('Option "mode" must be one of %s. "%s" given.', implode(', ', $modes), $mode));
        }

        $dm = $this->getDocumentManager();

        if ($mode === self::DROP || $mode === self::REPLACE) {
            if (isset($className)) {
                $dm->deleteDocumentIndexes($className);
            } else {
                $dm->deleteIndexes();
            }
        }

        if ($mode === self::CREATE || $mode === self::REPLACE) {
            if (isset($className)) {
                $dm->ensureDocumentIndexes($className);
            } else {
                $dm->ensureIndexes();
            }
        }

        return sprintf('Successfully %sd %s', $mode, (isset($className) ? 'indexes for ' . $className : 'all indexes'));
    }

    /**
     * @return Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->getHelper('documentManager')->getDocumentManager();
    }

    /**
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory
     */
    protected function getMetadataFactory()
    {
        return $this->getDocumentManager()->getMetadataFactory();
    }
}