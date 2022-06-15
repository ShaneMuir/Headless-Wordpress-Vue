<?php

// We don't need a frontend, goodbye!
global $pagenow;
if($pagenow === "index.php") {
	wp_safe_redirect(get_site_url() . "/wp-admin");
}