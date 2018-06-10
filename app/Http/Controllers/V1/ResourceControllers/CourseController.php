<?php
/**
 * Auto generate source code header.
 * Original File Name: CourseController.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 9:54
 */

namespace App\Http\Controllers\V1\ResourceControllers;

use App\Http\Controllers\V1\ResourceController;
use App\Models\Resources\Classn;
use App\Models\Roles\Teacher;
use App\Transformers\ClassnTransformer;
use App\Transformers\CourseFlatTransformer;
use App\Transformers\CourseStatTransformer;
use App\Transformers\LessonTransformer;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Blueprint\Annotation\Method\Put;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Response;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use App\Models\Resources\Course;
use Dingo\Api\Http\Request;
use Illuminate\Foundation\Testing\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * 课程资源表示
 * @Resource("Course", uri="/courses")
 */
class CourseController extends ResourceController
{
    const RESOURCE_MODEL = Course::class;

    /**
     * 使用选择的id来查询课程
     *
     * @Versions({"v1"})
     * @GET("/{course}")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179, "teacher_id":12312, "name":"课程1"}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     */
    public function get(Course $course)
    {
        return parent::presetGet($course);
    }

    /**
     * 使用选择的id来查询课程详细信息
     *
     * @Versions({"v1"})
     * @GET("/{course}.flat")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179, "teacher":{"...":"..."}, "name":"课程1", "lessons":{"...":"..."}}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param $id
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getFlat($id)
    {
        $course = Course::where('id', $id)->with('lessons', 'teacher')->firstOrFail();
        return $this->response->item($course, new CourseFlatTransformer());
    }

    /**
     * 添加课程
     *
     * @Versions({"v1"})
     * @POST("/")
     * @Transaction({
     *     @Request({"data":{"...":"..."}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/courses/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not add course.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     */
    public function add(Request $request, Course $course)
    {
        return parent::presetAdd($request, $course);
    }

    /**
     * 删除指定id的课程
     *
     * @Versions({"v1"})
     * @DELETE("/{course}")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/courses/2589"}),
     *     @Response(422, body={"message":"Could not delete course.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     */
    public function delete(Course $course)
    {
        return parent::presetDelete($course);
    }

    /**
     * 更新指定课程
     *
     * @Versions({"v1"})
     * @PUT("/{course}")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer"),
     * })
     * @Transaction({
     *     @Request({"data":{"name":"课程1"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/courses/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not update course.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        return parent::presetUpdate($request, $course);
    }

    /**
     * 将指定课程添加到老师
     *
     * @Versions({"v1"})
     * @POST("/{course}/teacher")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true),
     *      @Parameter("teacher_id", description="老师id", type="integer", required=true)
     * })
     * @Transaction({
     *     @Request({"data":{"teacher_id":"4354325223"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/teachers/4354325223"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(409, body={"message":"Course already attached a teacher.","status_code":"409"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Foundation\Testing\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @internal param Teacher $teacher
     * @internal param Student $student
     */
    public function attachToTeacher(Request $request, Course $course)
    {
        $data = $this->getData($request);
        if (!isset($data['teacher_id']) || !is_numeric($data['teacher_id']))
            throw new BadRequestHttpException('Missing key: teacher_id.');

        if ($course->classn_id) throw new ConflictHttpException('Course already attached a teacher.');

        if (! $teacher = Teacher::find($data['teacher_id']))
            throw new BadRequestHttpException('Teacher not found.');

        if ($course->attachToTeacher($teacher))
            return $this->response->accepted(route_api('teacher.get', self::VERSION, ['id' => $teacher->id]));

        throw new HttpException(500);
    }

    /**
     * 为课程解除老师附加
     *
     * @Versions({"v1"})
     * @DELETE("/{course}/teacher")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true)
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/courses/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"})
     * })
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     * @internal param Student $student
     * @internal param User $user
     */
    public function detachFromTeacher(Course $course)
    {
        if ($course->detachFromTeacher()) {
            return $this->response->accepted(route_api('course.get', self::VERSION, ['id' => $course->id]));
        } else {
            throw new BadRequestHttpException();
        }
    }

    /**
     * 为课程添加课时
     *
     * @Versions({"v1"})
     * @POST("/{course}/lessons")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Request({"data":{}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/lessons/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not add lesson.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function addLessonAndAttachToCourse(Course $course)
    {
        if (!$result = $course->addLesson()) {
            throw new DeleteResourceFailedException('Could not add lesson.');
        }
        return $this->response->created(route_api('lesson.get', self::VERSION, ['id' => $result[0]->id]), ['data' => ['pin' => $result[1], 'lesson_id' => $result[0]->id]]);
    }

    /**
     * 使用选择的id来查询课程所有班级
     *
     * @Versions({"v1"})
     * @GET("/{course}/classns")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{{"id":1231,"name":"班级1"},{"id":1232,"name":"班级2"}}}),
     *     @Response(204, body={"message":"","status_code":"204"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     */
    public function getClassns(Course $course)
    {
        $classns = $course->classns;

        if ($classns->isEmpty()) {
            return $this->response->noContent();
        }

        return $this->response->collection($classns, new ClassnTransformer());
    }

    /**
     * 使用选择的id来查询课程所有课时
     *
     * @Versions({"v1"})
     * @GET("/{course}/lessons")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{{"id":1231, "course_id":12312, "timestamp":1231223324},{"id":1232, "course_id":12312, "timestamp":1231223324}}}),
     *     @Response(204, body={"message":"","status_code":"204"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     */
    public function getLessons(Course $course)
    {
        $lessons = $course->lessons;

        if ($lessons->isEmpty()) {
            return $this->response->noContent();
        }

        return $this->response->collection($lessons, new LessonTransformer());
    }

    /**
     * 为课程连接班级
     *
     * @Versions({"v1"})
     * @Put("/{course}/classns")
     * @Parameters({
     *      @Parameter("course", description="课程id", type="integer", required=true),
     *      @Parameter("classn_id", description="班级id", type="integer", required=true)
     * })
     * @Transaction({
     *     @Request({"data":{"classn_id":11123}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/classns/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not add lesson.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Course $course
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Foundation\Testing\HttpException
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function associateClassn(Request $request, Course $course)
    {
        $data = $this->getData($request);
        if (!isset($data['classn_id']) || !is_numeric($data['classn_id']))
            throw new BadRequestHttpException('Missing key: classn_id.');

        if (! $classn = Classn::find($data['classn_id']))
            throw new BadRequestHttpException('Class not found.');

        if ($course->associateClassn($classn))
            return $this->response->accepted(route_api('classn.get', self::VERSION, ['id' => $classn->id]));

        throw new HttpException(500);

    }

    public function getStat(Course $course)
    {
        return $this->response->item($course, new CourseStatTransformer());
    }
}