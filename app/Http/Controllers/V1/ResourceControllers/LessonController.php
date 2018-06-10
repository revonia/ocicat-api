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
use App\Transformers\AttendanceTransformer;
use App\Transformers\LessonStatTransformer;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Response;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use Dingo\Api\Http\Request;
use App\Models\Resources\Lesson;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 课时资源表示
 * @Resource("Lesson", uri="/lessons")
 */
class LessonController extends ResourceController
{
    const RESOURCE_MODEL = Lesson::class;

    /**
     * 使用选择的id来查询课时
     *
     * @Versions({"v1"})
     * @GET("/{lesson}")
     * @Parameters({
     *      @Parameter("lesson", description="课时id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179,"course_id":12312, "timestamp":1231223324}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Lesson $lesson
     * @return \Dingo\Api\Http\Response
     */
    public function get(Lesson $lesson)
    {
        return parent::presetGet($lesson);
    }

//    /**
//     * 添加课时
//     *
//     * @Versions({"v1"})
//     * @POST("/")
//     * @Transaction({
//     *     @Request({"data":{"...":"..."}}),
//     *     @Response(201, headers={"location": "http://api.ocicat.dev/lessons/2589"}),
//     *     @Response(400, body={"message":"","status_code":"400"}),
//     *     @Response(422, body={"message":"Could not add lesson.","status_code":422}),
//     *     @Response(500, body={"message":"","status_code":"500"})
//     * })
//     * @param Request $request
//     * @param Lesson $lesson
//     * @return \Dingo\Api\Http\Response
//     */
//    public function add(Request $request, Lesson $lesson)
//    {
//        return parent::presetAdd($request, $lesson);
//    }

    /**
     * 删除指定id的课时
     *
     * @Versions({"v1"})
     * @DELETE("/{lesson}")
     * @Parameters({
     *      @Parameter("lesson", description="课时id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/lessons/2589"}),
     *     @Response(422, body={"message":"Could not delete lesson.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Lesson $lesson
     * @return \Dingo\Api\Http\Response
     */
    public function delete(Lesson $lesson)
    {
        return parent::presetDelete($lesson);
    }

    /**
     * 更新指定课时
     *
     * @Versions({"v1"})
     * @PUT("/{lesson}")
     * @Parameters({
     *      @Parameter("lesson", description="课时id", type="integer"),
     * })
     * @Transaction({
     *     @Request({"data":{"...":"..."}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/lessons/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not update lesson.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Lesson $lesson
     * @return \Dingo\Api\Http\Response
     */
    public function update(Request $request, Lesson $lesson)
    {
        return parent::presetUpdate($request, $lesson);
    }

    /**
     * 使用选择的id来查询课时所有出席记录
     *
     * @Versions({"v1"})
     * @GET("/{lesson}/lessons")
     * @Parameters({
     *      @Parameter("lesson", description="课时id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{{"id":1231, "lesson_id":12312, "student":1121, "timestamp":1231223324},{"id":1232, "lesson_id":12312, "student":1121, "timestamp":1231223324}}}),
     *     @Response(204, body={"message":"","status_code":"204"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Lesson $lesson
     * @return \Dingo\Api\Http\Response
     */
    public function getAttendances(Lesson $lesson)
    {
        $attendances = $lesson->attendances;

        $attendances->load('lesson');

        if ($attendances->isEmpty()) {
            return $this->response->noContent();
        }

        return $this->response->collection($attendances, new AttendanceTransformer());
    }

    /**
     * 使用选择的id来查询课时的签到验证码pin
     *
     * @Versions({"v1"})
     * @GET("/{lesson}/pin")
     * @Parameters({
     *      @Parameter("lesson", description="课时id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"pin":11111}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Lesson $lesson
     * @return \Dingo\Api\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getPin(Lesson $lesson)
    {
        if (!$pin = $lesson->getPin()) {
            throw new NotFoundHttpException('Pin not found.');
        }

        return $this->response->array(['data' => ['pin' => $pin]]);

    }

    public function getStat(Lesson $lesson)
    {
        return $this->response->item($lesson, new LessonStatTransformer());
    }

}