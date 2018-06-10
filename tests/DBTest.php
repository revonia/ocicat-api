<?php

class DBTest extends TestCase
{

    public static $tables = [
        'migrations',  //自动生成的迁移表
        'users', 'admins', 'teachers',
        'students', 'classns', 'attendances',
        'courses', 'lessons', 'course_classn'
    ];
    /**
     * 迁移测试
     *
     * @return void
     */
    public function testMigrate()
    {
        $tables = self::$tables;

        $this->artisan('migrate');

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }

        $this->artisan('migrate:rollback');

        array_shift($tables);

        foreach ($tables as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }
    }

}
