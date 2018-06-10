<?php

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    const VERSION = '';
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $this->baseUrl = env('APP_URL');

        return $app;
    }

    /**
     * Assert that a given where condition does not matches a soft deleted record
     *
     * @param  string $table
     * @param  array  $data
     * @param  string $connection
     * @return $this
     */
    protected function seeIsNotSoftDeletedInDatabase($table, array $data, $connection = null)
    {
        $database = $this->app->make('db');

        $connection = $connection ?: $database->getDefaultConnection();

        $count = $database->connection($connection)
            ->table($table)
            ->where($data)
            ->whereNull('deleted_at')
            ->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

    /**
     * Assert that a given where condition matches a soft deleted record
     *
     * @param  string $table
     * @param  array  $data
     * @param  string $connection
     * @return $this
     */
    protected function seeIsSoftDeletedInDatabase($table, array $data, $connection = null)
    {
        $database = $this->app->make('db');

        $connection = $connection ?: $database->getDefaultConnection();

        $count = $database->connection($connection)
            ->table($table)
            ->where($data)
            ->whereNotNull('deleted_at')
            ->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

    /**
     * 生成uri
     *
     * @param string $name 命名路由
     * @param string $pn uri参数名
     * @param string|integer $i uri参数值
     * @return string
     */
    protected function u($name, $pn = '', $i = null)
    {
        if (!$pn) return route_api($name, static::VERSION);
        return route_api($name, static::VERSION, [$pn => $i ]);
    }

    /**
     * 生成uri 带参数表
     * @param $name
     * @param array $x
     * @return string
     */
    protected function ux($name, $x = [])
    {
        return route_api($name, static::VERSION, $x);
    }

    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        parent::tearDown();
    }
}
