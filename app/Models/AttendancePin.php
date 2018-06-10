<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendancePin extends Model
{
    const TABLE_NAME = 'attendance_pin';

    protected $table = self::TABLE_NAME;
    protected $fillable = ['lesson_id', 'classn_id', 'pin'];

    public static function verify($classn_id, $pin)
    {
        $now = Carbon::now()->toDateTimeString();
        $query = static::where('classn_id', $classn_id);
        $query->where('pin', '=', $pin);
        $query->whereRaw(sprintf('updated_at BETWEEN DATE_SUB(\'%s\', INTERVAL 15 MINUTE) AND \'%s\'', $now, $now));

        $sql = $query->toSql();
        $result = $query->first();

        if ($result) {
            return $result->lesson_id;
        }

        return false;
    }
}
