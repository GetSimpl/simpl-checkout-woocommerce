<?php

class Test_Simpl_API_Files extends WP_UnitTestCase
{
    /** @test */
    public function apiFileExists()
    {
        $this->assertFileExists(PLUGIN_DIR . '/includes/endpoints/internal/checkout.php');
        $this->assertFileExists(PLUGIN_DIR . '/includes/endpoints/public/cart.php');
        $this->assertFileExists(PLUGIN_DIR . '/includes/endpoints/public/auth.php');
    }
}