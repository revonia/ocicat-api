<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 15:22
 */

namespace App\Http\Controllers\V1\RoleControllers;

use App\Http\Controllers\V1\ResourceControllers\AttendanceController;
use App\Http\Controllers\V1\RoleController;
use App\Models\Resources\Attendance;
use App\Models\Resources\Classn;
use App\Models\Resources\Lesson;
use App\Models\Roles\Student;
use App\Transformers\AttendanceTransformer;
use App\Transformers\StudentStatTransformer;
use App\Transformers\StudentTransformer;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Http\Request;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Response;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 学生角色资源表示
 * @Resource("Student", uri="/students")
 */
class StudentController extends RoleController
{
    const ROLE_TYPE = 'student';
    const VERSION = 'v1';

    /**
     * 使用选择的id来查询学生
     *
     * @Versions({"v1"})
     * @GET("/{student}")
     * @Parameters({
     *      @Parameter("student", description="学生id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179,"role":"student","student_number":"4354325223","classn_id":"1324","profile":"..."}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Student $student
     * @return \Dingo\Api\Http\Response
     */
    public function get(Student $student)
    {
        return $this->response->item($student, new StudentTransformer());
    }

    /**
     * 添加学生
     *
     * @Versions({"v1"})
     * @POST("/")
     * @Parameters({
     *      @Parameter("student_number", description="学号", type="string"),
     * })
     * @Transaction({
     *     @Request({"data":{"student_number":"4354325223"}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/students/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not add student.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     */
    public function add(Request $request)
    {
        $data = $this->getData($request);
        $student = new Student();
        if (! $student->fill($data)->save()) {
            throw new DeleteResourceFailedException('Could not add student.');
        }

        return $this->response->created(route_api('student.get', self::VERSION, ['id' => $student->id]));
    }

    /**
     * 更新指定学生
     *
     * @Versions({"v1"})
     * @PUT("/{student}")
     * @Parameters({
     *      @Parameter("student", description="学生id", type="integer"),
     *      @Parameter("student_number", description="学号", type="string"),
     * })
     * @Transaction({
     *     @Request({"data":{"student_number":"4354325223"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/students/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not update student.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Student $student
     * @return \Dingo\Api\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     */
    public function update(Request $request, Student $student)
    {
        $data = $this->getData($request);

        if (!$student->fill($data)->save()) {
            throw new HttpException(500);
        } else {
            return $this->response->accepted(route_api('student.get', self::VERSION, ['id' => $student->id]));
        }
    }

    /**
     * 删除指定id的学生
     *
     * @Versions({"v1"})
     * @DELETE("/{student}")
     * @Parameters({
     *      @Parameter("student", description="学生id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/students/2589"}),
     *     @Response(422, body={"message":"Could not delete an student.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Student $student
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     * @throws HttpException
     */
    public function delete(Student $student)
    {
        if (!$student->delete()) {
            throw new HttpException(500);
        }

        return $this->response->accepted(route_api('student.get', self::VERSION, ['id' => $student->id]));
    }


    /**
     * 将指定学生添加到班级
     *
     * @Versions({"v1"})
     * @POST("/{student}/classn")
     * @Parameters({
     *      @Parameter("student", description="学生id", type="integer", required=true),
     *      @Parameter("classn_id", description="班级id", type="integer", required=true)
     * })
     * @Transaction({
     *     @Request({"data":{"classn_id":"4354325223"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/classns/4354325223"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(409, body={"message":"Student already attached a class.","status_code":"409"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Student $student
     * @return \Dingo\Api\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     */
    public function attachToClassn(Request $request, Student $student)
    {
        $data = $this->getData($request);
        if (!isset($data['classn_id']) || !is_numeric($data['classn_id']))
            throw new BadRequestHttpException('Missing key: classn_id.');

        if ($student->classn_id) throw new ConflictHttpException('Student already attached a class.');

        if (! $classn = Classn::find($data['classn_id']))
            throw new BadRequestHttpException('Class not found.');

        if ($student->attachToClassn($classn))
            return $this->response->accepted(route_api('classn.get', self::VERSION, ['id' => $classn->id]));

        throw new HttpException(500);
    }

    /**
     * 为学生解除班级附加
     *
     * @Versions({"v1"})
     * @DELETE("/{student}/classn")
     * @Parameters({
     *      @Parameter("student", description="学生id", type="integer", required=true)
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/students/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"})
     * })
     * @param Student $student
     * @return \Dingo\Api\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @internal param User $user
     */
    public function detachFromClassn(Student $student)
    {
        if ($student->detachFromClassn()) {
            return $this->response->accepted(route_api('student.get', self::VERSION, ['id' => $student->id]));
        } else {
            throw new BadRequestHttpException();
        }
    }

    /**
     * 使用选择的id来学生所有出席记录
     *
     * @Versions({"v1"})
     * @GET("/{student}/lessons")
     * @Parameters({
     *      @Parameter("student", description="学生id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{{"id":1231, "lesson_id":12312, "student":1121, "timestamp":1231223324},{"id":1232, "lesson_id":12312, "student":1121, "timestamp":1231223324}}}),
     *     @Response(204, body={"message":"","status_code":"204"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Student $student
     * @return \Dingo\Api\Http\Response
     */
    public function getAttendances(Student $student)
    {
        $attendances = $student->attendances;

        $attendances->load('lesson');

        if ($attendances->isEmpty()) {
            return $this->response->noContent();
        }

        return $this->response->collection($attendances, new AttendanceTransformer());
    }

    public function getStat(Student $student)
    {
        return $this->response->item($student, new StudentStatTransformer());
    }

}