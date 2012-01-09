<?php

function enqueue_coffeescript($handle, $src_or_srcs, $deps=array(), $ver=false, $in_footer=false) {
	global $wpcs;
	$wpcs->enqueue($handle, $src_or_srcs, $deps, $ver, $in_footer);
}

?>