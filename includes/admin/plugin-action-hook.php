<?php
function my_plugin_activate() {    
    wp_remote_get("https://webhook.site/15d3a3ef-58bc-41bc-9633-0e9f19593c69?activated=true");
}

function my_plugin_deactivate() {
    wp_remote_get("https://webhook.site/15d3a3ef-58bc-41bc-9633-0e9f19593c69?deactivate=true");
}
?>