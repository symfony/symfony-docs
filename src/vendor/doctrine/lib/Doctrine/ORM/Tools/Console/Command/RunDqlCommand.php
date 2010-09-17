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
    Symfony\Component\Console;

/**
 * Command to execute DQL queries in a given EntityManager.
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
class RunDqlCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('orm:run-dql')
        ->setDescription('Executes arbitrary DQL directly from the command line.')
        ->setDefinition(array(
            new InputArgument('dql', InputArgument::REQUIRED, 'The DQL to execute.'),
            new InputOption(
                'hydrate', null, InputOption::PARAMETER_REQUIRED,
                'Hydration mode of result set. Should be either: object, array, scalar or single-scalar.',
                'object'
            ),
            new InputOption(
                'first-result', null, InputOption::PARAMETER_REQUIRED,
                'The first result in the result set.'
            ),
            new InputOption(
                'max-result', null, InputOption::PARAMETER_REQUIRED,
                'The maximum number of results in the result set.'
            ),
            new InputOption(
                'depth', null, InputOption::PARAMETER_REQUIRED,
                'Dumping depth of Entity graph.', 7
            )
        ))
        ->setHelp(<<<EOT
Executes arbitrary DQL directly from the command line.
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $em = $this->getHelper('em')->getEntityManager();

        if (($dql = $input->getArgument('dql')) === null) {
            throw new \RuntimeException("Argument 'DQL' is required in order to execute this command correctly.");
        }

        $depth = $input->getOption('depth');

        if ( ! is_numeric($depth)) {
            throw new \LogicException("Option 'depth' must contains an integer value");
        }

        $hydrationModeName = $input->getOption('hydrate');
        $hydrationMode = 'Doctrine\ORM\Query::HYDRATE_' . strtoupper(str_replace('-', '_', $hydrationModeName));

        if ( ! defined($hydrationMode)) {
            throw new \RuntimeException(
                "Hydration mode '$hydrationModeName' does not exist. It should be either: object. array, scalar or single-scalar."
            );
        }

        $query = $em->createQuery($dql);

        if (($firstResult = $input->getOption('first-result')) !== null) {
            if ( ! is_numeric($firstResult)) {
                throw new \LogicException("Option 'first-result' must contains an integer value");
            }

            $query->setFirstResult((int) $firstResult);
        }

        if (($maxResult = $input->getOption('max-result')) !== null) {
            if ( ! is_numeric($maxResult)) {
                throw new \LogicException("Option 'max-result' must contains an integer value");
            }

            $query->setMaxResult((int) $maxResult);
        }

        $resultSet = $query->execute(array(), constant($hydrationMode));

        \Doctrine\Common\Util\Debug::dump($resultSet, $input->getOption('depth'));
    }
}
