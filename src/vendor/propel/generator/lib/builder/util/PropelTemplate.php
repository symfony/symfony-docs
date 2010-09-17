<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Simple templating system to ease behavior writing
 *
 * @author     François Zaninotto
 * @version    $Revision: 1784 $
 * @package    propel.generator.builder.util
 */
class PropelTemplate
{
	protected $template, $templateFile;
	
	/**
	 * Set a string as a template.
	 * The string doesn't need closing php tags.
   *
	 * <code>
	 * $template->setTemplate('This is <?php echo $name ?>');
	 * </code>
	 * 
	 * @param string $template the template string
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}
	
	/**
	 * Set a file as a template. The file can be any regular PHP file.
	 * 
	 * <code>
	 * $template->setTemplateFile(dirname(__FILE__) . '/template/foo.php');
	 * </code>
	 *
	 * @param string $filePath The (absolute or relative to the include path) file path
	 */
	public function setTemplateFile($filePath)
	{
		$this->templateFile = $filePath;
	}
	
	/**
	 * Render the template using the variable provided as arguments.
	 *
	 * <code>
	 * $template = new PropelTemplate();
	 * $template->setTemplate('This is <?php echo $name ?>');
	 * echo $template->render(array('name' => 'Mike'));
	 * // This is Mike
	 * </code>
	 *
	 * @param array $vars An associative array of argumens to be rendered
	 *
	 * @return string The rendered template
	 */
	public function render($vars = array())
	{
	  if (null === $this->templateFile && null === $this->template) {
			throw new InvalidArgumentException('You must set a template or a template file before rendering');
		}
		
		extract($vars);
		ob_start();
		ob_implicit_flush(0);
			
		try
		{
			if (null !== $this->templateFile) {
				require($this->templateFile);
			} else {
				eval('?>' . $this->template . '<?php ');
			}
		}
		catch (Exception $e)
		{
			// need to end output buffering before throwing the exception #7596
			ob_end_clean();
			throw $e;
		}

		return ob_get_clean();
	}
}