<?php
/**
 * Auto generate source code header.
 * Original File Name: Test.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 10:05
 */

use App\Models\Resources\Attendance;
use App\Models\Resources\Classn;
use App\Models\Resources\Course;
use App\Models\Resources\Lesson;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ResourceBaseTest extends TestCase
{
    use DatabaseMigrations;

    public function resource_data_provider()
    {
        $ret = [
            'attendance'
                => ['attendance', Attendance::class, ['tmp' => 'tmp']],
            'classn'
                => ['classn', Classn::class, ['tmp' => 'tmp']],
            'course'
                => ['course', Course::class, ['tmp' => 'tmp']],
            'lesson'
                => ['lesson', Lesson::class, ['tmp' => 'tmp']],
        ];

        return $ret;
    }

    /**
     * @test
     * @dataProvider resource_data_provider
     * @param $res_name
     * @param $res_model
     */
    public function get_resource_by_id_should_success($res_name, $res_model)
    {
        $res = factory($res_model)->create();
        $this->get($this->u("$res_name.get", '$res_name', $res->id))
            ->seeJson([
                'id' => $res->id,
            ]);
        $this->assertResponseStatus(200);

    }

    /**
     * @test
     * @dataProvider resource_data_provider
     * @param $res_name
     * @param $res_model
     * @param $data
     */
    public function add_resource_should_success($res_name, $res_model, $data)
    {
        if ($res_name === 'lesson' || $res_name === 'attendance') return;

        $this->json('POST', $this->u("$res_name.add", '$res_name'), ['data' => $data]);

        $this->assertResponseStatus(201);

        $location = $this->response->headers->get('Location');
        $id = array_slice(explode('/', $location), -1, 1)[0];
        $this->seeInDatabase($res_model::TABLE_NAME, [
            'id' => $id,
        ]);

        $this->get($location)
            ->seeJson([
                'id' => (int) $id,
            ]);
    }

    /**
     * @test
     * @dataProvider resource_data_provider
     * @param $res_name
     * @param $res_model
     */
    public function delete_resource_should_success($res_name, $res_model)
    {
        $res = factory($res_model)->create();
        $this->delete($this->u("$res_name.delete", '$res_name', $res->id));

        $this->assertResponseStatus(202);

        $this->dontSeeInDatabase($res_model::TABLE_NAME, [
            'id' => $res->id,
        ]);
    }

    /**
     * @test
     * @dataProvider resource_data_provider
     * @param $res_name
     * @param $res_model
     * @param $data
     */
    public function update_resource_should_success($res_name, $res_model, $data)
    {
        if ($res_name === 'lesson') return;
        $res = factory($res_model)->create();
        $this->json('PUT', $this->u("$res_name.update", '$res_name', $res->id), ['data' => $data]);

        $this->assertResponseStatus(202);

        $this->seeInDatabase($res_model::TABLE_NAME, [
            'id' => $res->id,
        ]);
    }
}
