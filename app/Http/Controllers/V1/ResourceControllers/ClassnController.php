<?php
/**
 * Auto generate source code header.
 * Original File Name: ClassnController.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 9:54
 */

namespace App\Http\Controllers\V1\ResourceControllers;


use App\Http\Controllers\V1\ResourceController;
use App\Models\Roles\Student;
use App\Transformers\CourseTransformer;
use App\Transformers\StudentTransformer;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Response;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use App\Models\Resources\Classn;
use Dingo\Api\Http\Request;
use App\Models\Resources\Course;
use Illuminate\Foundation\Testing\HttpException;

/**
 * 班级资源表示
 * @Resource("Classn", uri="/classns")
 */
class ClassnController extends ResourceController
{
    const RESOURCE_MODEL = Classn::class;

    /**
     * 使用选择的id来查询班级
     *
     * @Versions({"v1"})
     * @GET("/{classn}")
     * @Parameters({
     *      @Parameter("classn", description="班级id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179,"name":"classn name"}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Classn $classn
     * @return \Dingo\Api\Http\Response
     */
    public function get(Classn $classn)
    {
        return parent::presetGet($classn);
    }

    /**
     * 添加班级
     *
     * @Versions({"v1"})
     * @POST("/")
     * @Transaction({
     *     @Request({"data":{"name":"classn name"}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/classns/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not add classn.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Classn $classn
     * @return \Dingo\Api\Http\Response
     */
    public function add(Request $request, Classn $classn)
    {
        return parent::presetAdd($request, $classn);
    }

    /**
     * 删除指定id的班级
     *
     * @Versions({"v1"})
     * @DELETE("/{classn}")
     * @Parameters({
     *      @Parameter("classn", description="班级id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/classns/2589"}),
     *     @Response(422, body={"message":"Could not delete classn.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Classn $classn
     * @return \Dingo\Api\Http\Response
     */
    public function delete(Classn $classn)
    {
        return parent::presetDelete($classn);
    }

    /**
     * 更新指定班级
     *
     * @Versions({"v1"})
     * @PUT("/{classn}")
     * @Parameters({
     *      @Parameter("classn", description="班级id", type="integer"),
     * })
     * @Transaction({
     *     @Request({"data":{"name":"classn name"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/classns/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not update classn.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Classn $classn
     * @return \Dingo\Api\Http\Response
     */
    public function update(Request $request, Classn $classn)
    {
        return parent::presetUpdate($request, $classn);
    }

    /**
     * 使用选择的id来查询班级所有课程
     *
     * @Versions({"v1"})
     * @GET("/{classn}/courses")
     * @Parameters({
     *      @Parameter("classn", description="班级id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{{"id":1231,"name":"课程1", "teacher_id":12312},{"id":1232,"name":"课程2", "teacher_id":12312}}}),
     *     @Response(204, body={"message":"","status_code":"204"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Classn $classn
     * @return \Dingo\Api\Http\Response
     */
    public function getCourses(Classn $classn)
    {
        $courses = $classn->courses;

        if ($courses->isEmpty()) {
            return $this->response->noContent();
        }

        return $this->response->collection($courses, new CourseTransformer());
    }

    /**
     * 使用选择的id来查询班级所有学生
     *
     * @Versions({"v1"})
     * @GET("/{classn}/students")
     * @Parameters({
     *      @Parameter("classn", description="班级id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{{"id":1231,"role":"student","classn_id":1112, "profile":"..."},{"id":1232,"role":"student","classn_id":1112, "profile":"..."}}}),
     *     @Response(204, body={"message":"","status_code":"204"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Classn $classn
     * @return \Dingo\Api\Http\Response
     */
    public function getStudents(Classn $classn)
    {
        $students = $classn->students;

        if ($students->isEmpty()) {
            return $this->response->noContent();
        }

        return $this->response->collection($students, new StudentTransformer());
    }
}