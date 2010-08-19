<?php

xhprof_enable();

require_once __DIR__.'/../hello/HelloKernel.php';

$kernel = new HelloKernel('prod', false);
$kernel->handle()->send();

$xhprof_data = xhprof_disable();
include_once "/Users/fabien/work/symfony/2_0/blog/web/xhprof-0.9.2/xhprof_lib/utils/xhprof_lib.php";
include_once "/Users/fabien/work/symfony/2_0/blog/web/xhprof-0.9.2/xhprof_lib/utils/xhprof_runs.php";
$xhprof_runs = new XHProfRuns_Default();
$run_id = $xhprof_runs->save_run($xhprof_data, "PR2", 1);
echo "http://<xhprof-ui-address>/index.php?run=$run_id&source=products\n";
