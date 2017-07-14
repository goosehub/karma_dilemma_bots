<?php
// Calling all bots
foreach (glob('*.php') as $filename) {
	// Don't call this file
	if ($filename === basename(__FILE__)) {
		continue;
	}
	echo $filename . ': ' . '<br>' . PHP_EOL;
    include $filename;
}