<?php

use Illuminate\Database\Migrations\Migration;

class CreateTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE TRIGGER tr_User_Insert_Add_Uuid Before INSERT ON `users` 
	            FOR EACH ROW SET NEW.uuid = uuid();
        ');

        DB::unprepared('
            CREATE TRIGGER tr_Classn_Insert_Add_Uuid Before INSERT ON `classns` 
	            FOR EACH ROW SET NEW.uuid = uuid();
        ');

        DB::unprepared('
            CREATE TRIGGER tr_Course_Insert_Add_Uuid Before INSERT ON `courses` 
	            FOR EACH ROW SET NEW.uuid = uuid();
        ');

        DB::unprepared('
            CREATE TRIGGER tr_Lesson_Insert_Add_Uuid Before INSERT ON `lessons` 
	            FOR EACH ROW SET NEW.uuid = uuid();
        ');

        DB::unprepared('
            CREATE TRIGGER tr_Attendance_Insert_Add_Uuid Before INSERT ON `attendances` 
	            FOR EACH ROW SET NEW.uuid = uuid();
        ');

        DB::unprepared('
            CREATE TRIGGER tr_Admin_Deleted_Set_User_Role_to_Null After DELETE ON `admins` 
	            FOR EACH ROW UPDATE `users` SET `role_type`=NULL, `role_id`=NULL WHERE `id`=OLD.user_id AND `role_id`=OLD.id AND `role_type`="admin"
        ');

        DB::unprepared('
            CREATE TRIGGER tr_Student_Deleted_Set_User_Role_to_Null After DELETE ON `students` 
	            FOR EACH ROW UPDATE `users` SET `role_type`=NULL, `role_id`=NULL WHERE `id`=OLD.user_id AND `role_id`=OLD.id AND `role_type`="student"
        ');

        DB::unprepared('
            CREATE TRIGGER tr_Teacher_Deleted_Set_User_Role_to_Null After DELETE ON `teachers` 
	            FOR EACH ROW UPDATE `users` SET `role_type`=NULL, `role_id`=NULL WHERE `id`=OLD.user_id AND `role_id`=OLD.id AND `role_type`="teacher"
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER `tr_User_Insert_Add_Uuid`');
        DB::unprepared('DROP TRIGGER `tr_Classn_Insert_Add_Uuid`');
        DB::unprepared('DROP TRIGGER `tr_Course_Insert_Add_Uuid`');
        DB::unprepared('DROP TRIGGER `tr_Lesson_Insert_Add_Uuid`');
        DB::unprepared('DROP TRIGGER `tr_Attendance_Insert_Add_Uuid`');
        DB::unprepared('DROP TRIGGER `tr_Admin_Deleted_Set_User_Role_to_Null`');
        DB::unprepared('DROP TRIGGER `tr_Student_Deleted_Set_User_Role_to_Null`');
        DB::unprepared('DROP TRIGGER `tr_Teacher_Deleted_Set_User_Role_to_Null`');

    }
}
