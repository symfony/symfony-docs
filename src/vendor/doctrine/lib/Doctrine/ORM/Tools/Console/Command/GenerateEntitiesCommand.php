<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Tools\Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console,
    Doctrine\ORM\Tools\Console\MetadataFilter,
    Doctrine\ORM\Tools\EntityGenerator,
    Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;

/**
 * Command to generate entity classes and method stubs from your mapping information.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class GenerateEntitiesCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('orm:generate-entities')
        ->setDescription('Generate entity classes and method stubs from your mapping information.')
        ->setDefinition(array(
            new InputOption(
                'filter', null, InputOption::PARAMETER_REQUIRED | InputOption::PARAMETER_IS_ARRAY,
                'A string pattern used to match entities that should be processed.'
            ),
            new InputArgument(
                'dest-path', InputArgument::REQUIRED, 'The path to generate your entity classes.'
            ),
            new InputOption(
                'generate-annotations', null, InputOption::PARAMETER_OPTIONAL,
                'Flag to define if generator should generate annotation metadata on entities.', false
            ),
            new InputOption(
                'generate-methods', null, InputOption::PARAMETER_OPTIONAL,
                'Flag to define if generator should generate stub methods on entities.', true
            ),
            new InputOption(
                'regenerate-entities', null, InputOption::PARAMETER_OPTIONAL,
                'Flag to define if generator should regenerate entity if it exists.', false
            ),
            new InputOption(
                'update-entities', null, InputOption::PARAMETER_OPTIONAL,
                'Flag to define if generator should only update entity if it exists.', true
            ),
            new InputOption(
                'extend', null, InputOption::PARAMETER_OPTIONAL,
                'Defines a base class to be extended by generated entity classes.'
            ),
            new InputOption(
                'num-spaces', null, InputOption::PARAMETER_OPTIONAL,
                'Defines the number of indentation spaces', 4
            )
        ))
        ->setHelp(<<<EOT
Generate entity classes and method stubs from your mapping information.
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $em = $this->getHelper('em')->getEntityManager();
        
        $cmf = new DisconnectedClassMetadataFactory($em);
        $metadatas = $cmf->getAllMetadata();
        $metadatas = MetadataFilter::filter($metadatas, $input->getOption('filter'));
        
        // Process destination directory
        $destPath = realpath($input->getArgument('dest-path'));

        if ( ! file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Entities destination directory '<info>%s</info>' does not exist.", $destPath)
            );
        } else if ( ! is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Entities destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        if (count($metadatas)) {
            // Create EntityGenerator
            $entityGenerator = new EntityGenerator();

            $entityGenerator->setGenerateAnnotations($input->getOption('generate-annotations'));
            $entityGenerator->setGenerateStubMethods($input->getOption('generate-methods'));
            $entityGenerator->setRegenerateEntityIfExists($input->getOption('regenerate-entities'));
            $entityGenerator->setUpdateEntityIfExists($input->getOption('update-entities'));
            $entityGenerator->setNumSpaces($input->getOption('num-spaces'));

            if (($extend = $input->getOption('extend')) !== null) {
                $entityGenerator->setClassToExtend($extend);
            }

            foreach ($metadatas as $metadata) {
                $output->write(
                    sprintf('Processing entity "<info>%s</info>"', $metadata->name) . PHP_EOL
                );
            }

            // Generating Entities
            $entityGenerator->generate($metadatas, $destPath);

            // Outputting information message
            $output->write(PHP_EOL . sprintf('Entity classes generated to "<info>%s</INFO>"', $destPath) . PHP_EOL);
        } else {
            $output->write('No Metadata Classes to process.' . PHP_EOL);
        }
    }
}
