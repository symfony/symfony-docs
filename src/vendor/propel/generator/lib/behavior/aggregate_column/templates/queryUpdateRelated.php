
protected function updateRelated<?php echo $relationName ?>s($con)
{
	foreach ($this-><?php echo $variableName ?>s as $<?php echo $variableName ?>) {
		$<?php echo $variableName ?>-><?php echo $updateMethodName ?>($con);
	}
	$this-><?php echo $variableName ?>s = array();
}
