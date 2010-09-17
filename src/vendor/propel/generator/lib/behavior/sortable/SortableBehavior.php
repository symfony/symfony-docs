<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once dirname(__FILE__) . '/SortableBehaviorObjectBuilderModifier.php';
require_once dirname(__FILE__) . '/SortableBehaviorQueryBuilderModifier.php';
require_once dirname(__FILE__) . '/SortableBehaviorPeerBuilderModifier.php';

/**
 * Gives a model class the ability to be ordered
 * Uses one additional column storing the rank
 *
 * @author      Massimiliano Arione
 * @version     $Revision: 1612 $
 * @package     propel.generator.behavior.sortable
 */
class SortableBehavior extends Behavior
{
	// default parameters value
	protected $parameters = array(
		'rank_column'  => 'sortable_rank',
		'use_scope'    => 'false',
		'scope_column' => 'sortable_scope',
	);

	protected $objectBuilderModifier, $queryBuilderModifier, $peerBuilderModifier;

	/**
	 * Add the rank_column to the current table
	 */
	public function modifyTable()
	{
		if (!$this->getTable()->containsColumn($this->getParameter('rank_column'))) {
			$this->getTable()->addColumn(array(
				'name' => $this->getParameter('rank_column'),
				'type' => 'INTEGER'
			));
		}
		if ($this->getParameter('use_scope') == 'true' && 
			 !$this->getTable()->containsColumn($this->getParameter('scope_column'))) {
			$this->getTable()->addColumn(array(
				'name' => $this->getParameter('scope_column'),
				'type' => 'INTEGER'
			));
		}
	}

	public function getObjectBuilderModifier()
	{
		if (is_null($this->objectBuilderModifier)) {
			$this->objectBuilderModifier = new SortableBehaviorObjectBuilderModifier($this);
		}
		return $this->objectBuilderModifier;
	}

	public function getQueryBuilderModifier()
	{
		if (is_null($this->queryBuilderModifier)) {
			$this->queryBuilderModifier = new SortableBehaviorQueryBuilderModifier($this);
		}
		return $this->queryBuilderModifier;
	}
	
	public function getPeerBuilderModifier()
	{
		if (is_null($this->peerBuilderModifier)) {
			$this->peerBuilderModifier = new SortableBehaviorPeerBuilderModifier($this);
		}
		return $this->peerBuilderModifier;
	}
	
	public function useScope()
	{
		return $this->getParameter('use_scope') == 'true';
	}

}
