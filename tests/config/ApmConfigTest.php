<?php

namespace AG\Tests\config;

class ApmConfigTest extends \Codeception\Test\Unit
{
    private $configFilePath = __DIR__ . '/../../config/elastic-apm-laravel.php';

    protected function _after()
    {
        // Make sure all environment variables are unset after every spec
        putenv('APM_ACTIVE');
        putenv('APM_APPNAME');
        putenv('APM_APPVERSION');
        putenv('APM_ENVIRONMENT');
        putenv('APM_SERVERURL');
        putenv('APM_SECRETTOKEN');
        putenv('APM_USEROUTEURI');
        putenv('APM_MAXTRACEITEMS');
        putenv('APM_BACKTRACEDEPTH');
        putenv('APM_QUERYLOG');
        putenv('APM_THRESHOLD');
    }

    public function testDefaultValues()
    {
        $config = include $this->configFilePath;

        // Defer defaults to the Agent package in order to support configuration through environment variables
        $this->assertEmpty($config['active']);
        $this->assertEmpty($config['app']['appName']);
        $this->assertEmpty($config['env']['environment']);
        $this->assertEmpty($config['server']['serverUrl']);
        $this->assertEmpty($config['spans']['backtraceDepth']);

        // app block
        $this->assertEquals('', $config['app']['appVersion']);

        // env block
        $this->assertEquals(['DOCUMENT_ROOT', 'REMOTE_ADDR'], $config['env']['env']);

        // server block
        $this->assertNull($config['server']['secretToken']);

        // transactions block
        $this->assertTrue($config['transactions']['useRouteUri']);

        // spans block
        $this->assertEquals(1000, $config['spans']['maxTraceItems']);
        $this->assertEquals(25, $config['spans']['querylog']['enabled']);
        $this->assertEquals(200, $config['spans']['querylog']['threshold']);
    }

    public function testAppConfigEnvVariables()
    {
        putenv('APM_ACTIVE=false');
        putenv('APM_APPNAME="Codeception App"');
        putenv('APM_APPVERSION="1.0.0"');
        $config = include $this->configFilePath;

        $this->assertFalse($config['active']);
        $this->assertEquals('Codeception App', $config['app']['appName']);
        $this->assertEquals('1.0.0', $config['app']['appVersion']);
    }

    public function testAppNameSpecialCharacters()
    {
        putenv('APM_APPNAME="Codeception?App"');
        $config = include $this->configFilePath;

        $this->assertEquals('Codeception-App', $config['app']['appName']);
    }

    public function testEnvConfigVariables()
    {
        putenv('APM_ENVIRONMENT="production"');
        $config = include $this->configFilePath;

        $this->assertEquals('production', $config['env']['environment']);
    }

    public function testServerConfigEnvVariables()
    {
        putenv('APM_SERVERURL="https://cloud.elastic.io:8200"');
        putenv('APM_SECRETTOKEN="super_secret_value"');
        $config = include $this->configFilePath;

        $this->assertEquals('https://cloud.elastic.io:8200', $config['server']['serverUrl']);
        $this->assertEquals('super_secret_value', $config['server']['secretToken']);
    }

    public function testTransactionsConfigEnvVariables()
    {
        putenv('APM_USEROUTEURI=false');
        $config = include $this->configFilePath;

        $this->assertFalse($config['transactions']['useRouteUri']);
    }

    public function testSpansConfigEnvVariables()
    {
        putenv('APM_MAXTRACEITEMS=10');
        putenv('APM_BACKTRACEDEPTH=10');
        putenv('APM_QUERYLOG="auto"');
        putenv('APM_THRESHOLD=50');
        $config = include $this->configFilePath;

        $this->assertEquals(10, $config['spans']['maxTraceItems']);
        $this->assertEquals(10, $config['spans']['backtraceDepth']);
        $this->assertEquals('auto', $config['spans']['querylog']['enabled']);
        $this->assertEquals(50, $config['spans']['querylog']['threshold']);
    }
}
