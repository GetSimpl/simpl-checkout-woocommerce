<?php

	const SCRIPT_ATTRIBUTES = [
		"nowprocket"
	];

	function simpl_inject_script($script) {
		$attrs = implode(" ", SCRIPT_ATTRIBUTES);
		echo str_replace('{{attributes}}', $attrs, $script);
	}

