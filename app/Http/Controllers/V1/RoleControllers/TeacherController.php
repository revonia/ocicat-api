<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 15:22
 */

namespace App\Http\Controllers\V1\RoleControllers;

use App\Http\Controllers\V1\RoleController;
use App\Models\Roles\Teacher;
use App\Transformers\CourseTransformer;
use App\Transformers\TeacherTransformer;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Response;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use Dingo\Api\Http\Request;
use Illuminate\Foundation\Testing\HttpException;

/**
 * 老师角色资源表示
 * @Resource("Teacher", uri="/teachers")
 */
class TeacherController extends RoleController
{
    const ROLE_TYPE = 'teacher';
    const VERSION = 'v1';

    /**
     * 使用选择的id来查询老师
     *
     * @Versions({"v1"})
     * @GET("/{teacher}")
     * @Parameters({
     *      @Parameter("teacher", description="老师id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179,"role":"teacher","employee_number":"4354325223","profile":"..."}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Teacher $teacher
     * @return \Dingo\Api\Http\Response
     */
    public function get(Teacher $teacher) {
        return $this->response->item($teacher, new TeacherTransformer());
    }

    /**
     * 添加老师
     *
     * @Versions({"v1"})
     * @POST("/")
     * @Parameters({
     *      @Parameter("employee_number", description="工号", type="string"),
     * })
     * @Transaction({
     *     @Request({"data":{"employee_number":"4354325223"}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/teachers/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not add teacher.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     */
    public function add(Request $request)
    {
        $data = $this->getData($request);
        $teacher = new Teacher();
        if (! $teacher->fill($data)->save()) {
            throw new DeleteResourceFailedException('Could not add teacher.');
        } else {
            return $this->response->created(route_api('teacher.get', self::VERSION, ['id' => $teacher->id]));
        }
    }

    /**
     * 更新指定老师
     *
     * @Versions({"v1"})
     * @PUT("/{teacher}")
     * @Parameters({
     *      @Parameter("teacher", description="老师id", type="integer"),
     *      @Parameter("employee_number", description="工号", type="string"),
     * })
     * @Transaction({
     *     @Request({"data":{"employee_number":"4354325223"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/teachers/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not update teacher.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Teacher $teacher
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Foundation\Testing\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     */
    public function update(Request $request, Teacher $teacher)
    {
        $data = $this->getData($request);

        if (!$teacher->fill($data)->save()) {
            throw new HttpException(500);
        } else {
            return $this->response->accepted(route_api('teacher.get', self::VERSION, ['id' => $teacher->id]));
        }
    }

    /**
     * 删除指定id的老师
     *
     * @Versions({"v1"})
     * @DELETE("/{teacher}")
     * @Parameters({
     *      @Parameter("teacher", description="老师id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/teachers/2589"}),
     *     @Response(422, body={"message":"Could not delete an teacher.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Teacher $teacher
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     * @throws HttpException
     */
    public function delete(Teacher $teacher)
    {
        if (!$teacher->delete()) {
            throw new HttpException(500);
        } else {
            return $this->response->accepted(route_api('teacher.get', self::VERSION, ['id' => $teacher->id]));
        }
    }

    /**
     * 使用选择的id来查询老师所有课程
     *
     * @Versions({"v1"})
     * @GET("/{teacher}/courses")
     * @Parameters({
     *      @Parameter("classn", description="老师id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{{"id":1231,"name":"课程1", "teacher_id":12312},{"id":1232,"name":"课程2", "teacher_id":12312}}}),
     *     @Response(204, body={"message":"","status_code":"204"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Teacher $teacher
     * @return \Dingo\Api\Http\Response
     */
    public function getCourses(Teacher $teacher)
    {
        $courses = $teacher->courses;

        if ($courses->isEmpty()) {
            return $this->response->noContent();
        }

        return $this->response->collection($courses, new CourseTransformer());
    }
}