<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

const SCRIPT_ATTRIBUTES = [
	"nowprocket"
];

function simpl_inject_script($src) {
	$script_tag = array(
		'src' => esc_url($src)
	);
	foreach (SCRIPT_ATTRIBUTES as $attr) {
		$script_tag[$attr] = 'true';
	}
	wp_print_script_tag($script_tag);
}

function simpl_inject_inline_script($script) {
	$script_tag = array();
	foreach (SCRIPT_ATTRIBUTES as $attr) {
		$script_tag[$attr] = 'true';
	}
	wp_print_inline_script_tag($script, $script_tag);
}
