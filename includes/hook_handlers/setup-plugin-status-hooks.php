<?php

// Simpl plugin activate/de-activate hooks
register_activation_hook( SIMPL_PLUGIN_FILE_URL, 'plugin_status_activate_hook' );
register_deactivation_hook( SIMPL_PLUGIN_FILE_URL, 'plugin_status_deactivate_hook' );
