
/**
 * Finds the related <?php echo $foreignTable->getPhpName() ?> objects and keep them for later
 *
 * @param PropelPDO $con A connection object
 */
protected function findRelated<?php echo $relationName ?>s($con)
{
	$criteria = clone $this;
	if ($this->useAliasInSQL) {
		$alias = $this->getModelAlias();
		$criteria->removeAlias($alias);
	} else {
		$alias = '';
	}
	$this-><?php echo $variableName ?>s = <?php echo $foreignQueryName ?>::create()
		->join<?php echo $refRelationName ?>($alias)
		->mergeWith($criteria)
		->find($con);
}
