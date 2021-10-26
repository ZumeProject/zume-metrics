<?php

class PluginTest extends TestCase
{
    public function test_plugin_installed() {
        activate_plugin( 'zume-metrics/zume-metrics.php' );

        $this->assertContains(
            'zume-metrics/zume-metrics.php',
            get_option( 'active_plugins' )
        );
    }
}
