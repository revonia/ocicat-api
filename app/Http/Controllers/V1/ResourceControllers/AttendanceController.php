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
use App\Models\Resources\Attendance;
use App\Models\Resources\Lesson;
use App\Models\Roles\Student;
use App\Transformers\AttendanceFlatTransformer;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Response;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use Dingo\Api\Http\Request;
use Illuminate\Foundation\Testing\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * 出勤记录资源表示
 * @Resource("Attendance", uri="/attendances")
 */
class AttendanceController extends ResourceController
{
    /**
     * 使用选择的id来查询出勤记录
     *
     * @Versions({"v1"})
     * @GET("/{attendance}")
     * @Parameters({
     *      @Parameter("attendance", description="出勤记录id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179,"student_id":1123,"lesson_id":122, "timestamp":1231223324}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Attendance $attendance
     * @return \Dingo\Api\Http\Response
     */
    public function get(Attendance $attendance)
    {
        $attendance->load('lesson');
        return parent::presetGet($attendance);
    }

    /**
     * 添加出勤记录
     *
     * @Versions({"v1"})
     * @POST("/")
     * @Transaction({
     *     @Request({"data":{"student_id":1231, "lesson_id":11}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/attendances/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not add attendance.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Foundation\Testing\HttpException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function add(Request $request)
    {
        $data  = $this->data($request);
        if (!isset($data['student_id'], $data['lesson_id'])
            || !is_numeric($data['student_id'])
            || !is_numeric($data['lesson_id'])
        ) {
            throw new BadRequestHttpException('Missing key: student_id or lesson_id.');
        }

        $student = Student::findOrFail($data['student_id']);
        $lesson = Lesson::findOrFail($data['lesson_id']);

        if ($attendance = Attendance::createWithStudentAndLesson($student, $lesson)) {
            return $this->response->created(route_api('attendance.get', self::VERSION, ['id' => $attendance->id]));
        }

        throw new HttpException(500);
    }

    /**
     * 删除指定id的出勤记录
     *
     * @Versions({"v1"})
     * @DELETE("/{attendance}")
     * @Parameters({
     *      @Parameter("attendance", description="出勤记录id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/attendances/2589"}),
     *     @Response(422, body={"message":"Could not delete attendance.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Attendance $attendance
     * @return \Dingo\Api\Http\Response
     */
    public function delete(Attendance $attendance)
    {
        return parent::presetDelete($attendance);
    }

    /**
     * 更新指定出勤记录
     *
     * @Versions({"v1"})
     * @PUT("/{attendance}")
     * @Parameters({
     *      @Parameter("attendance", description="出勤记录id", type="integer"),
     * })
     * @Transaction({
     *     @Request({"data":{"...":"..."}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/attendances/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not update attendance.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Attendance $attendance
     * @return \Dingo\Api\Http\Response
     */
    public function update(Request $request, Attendance $attendance)
    {
        return parent::presetUpdate($request, $attendance);
    }

    /**
     * 获取出勤记录的平铺信息
     *
     * 响应中将展开课时和学生
     *
     * @Versions({"v1"})
     * @get("/{attendance}")
     * @Parameters({
     *      @Parameter("attendance", description="出勤记录id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179,"student":{"...":"..."},"lesson":{"...":"..."}, "timestamp":1231223324}}),
     *     @Response(404, body={"message":"404 Not Found.","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param $id
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getFlat($id)
    {
        $attendance = Attendance::where('id', $id)->with('lesson', 'student')->firstOrFail();
        return $this->response->item($attendance, new AttendanceFlatTransformer());
    }

}