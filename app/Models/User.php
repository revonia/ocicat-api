<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/8
 * Time: 0:06
 */

namespace App\Models;

use App\Models\Roles\Admin;
use App\Models\Roles\Student;
use App\Models\Roles\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use \Exception;
use Validator;
use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * Class User
 *
 * @package App\Models
 * Note: uuid字段将在插入时在数据库内自动填写
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $role
 * @mixin \Eloquent
 * @property integer $id
 * @property string $uuid
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role_type
 * @property integer $role_id
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property UserProfile profile
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereUuid($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRoleType($value)
 * @method static Builder|User whereRoleId($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereUpdatedAt($value)
 */
class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract
{
    use Authenticatable, Authorizable;//, SoftDeletes;

    const TABLE_NAME = 'users';

    const IDENTIFIERS = ['uuid', 'username', 'email'];

    const ROLE_TYPES = [
        'admin' => Admin::class,
        'teacher' => Teacher::class,
        'student' => Student::class
    ];

    const QUERYABLE = ['id', 'username', 'email', 'uuid',
                    'status', 'role_type', 'role_id'];

    const UPDATABLE = ['username', 'email', 'status', 'password'];

    const STATUS = ['active', 'suspend'];

    /* @var \Illuminate\Validation\Validator */
    protected $validator;

    /**
     * 表明在创建和更新时是否产生冲突
     * @var bool
     */
    protected $conflicted = false;

    /**
     * 可以被批量赋值的属性。
     * @var array
     */
    protected $fillable = ['username', 'email','status', 'password'];


    /**
     * save前字段惟一性验证条件
     * @var array
     */
    protected $saveRules = [
        'username' => 'sometimes|unique:users',
        'email' => 'sometimes|unique:users',
    ];

    /**
     * create前的验证条件
     * @var array
     */
    protected $createRules = [
        'username' => 'required|username',
        'email' => 'required|email',
        'password' => 'required|bcrypt',
    ];

    /**
     * update前的验证条件
     * @var array
     */
    protected $updateRules = [
        'username' => 'sometimes|username',
        'email' => 'sometimes|email',
        'status' => 'sometimes|status',
        'password' => 'sometimes|bcrypt',
    ];

    /**
     * Return failed or not
     *
     * @return bool
     * @throws Exception
     */
    public function validatorFailed()
    {
        if (!$this->validator) throw new Exception('Try to get validator before validate.');
        return ! empty($this->validator->failed());
    }

    /**
     * Returns the data which was invalid.
     *
     * @return array
     * @throws Exception
     */
    public function invalid()
    {
        if (!$this->validator) throw new Exception('Try to get validator before validate.');
        return $this->validator->invalid();
    }

    /**
     * An alternative more semantic shortcut to the message container.
     *
     * @return \Illuminate\Support\MessageBag
     * @throws Exception
     */
    public function errors()
    {
        if (!$this->validator) throw new Exception('Try to get validator before validate.');
        return $this->validator->errors();
    }

    /**
     * change after save
     * @return boolean
     */
    public function isConflicted()
    {
        return $this->conflicted;
    }

    public static function boot()
    {
        parent::boot();

        Relation::morphMap(self::ROLE_TYPES);

        //创建前验证字段
        self::creating(function ($model) {
            /** @var User $model */
            $model->validator = Validator::make($model->getDirty(), $model->createRules);
            return $model->validator->passes();
        });

        //创建后附加profile
        self::created(function ($model) {
            /** @var User $model */
            $model->profile()->save(new UserProfile());
        });

        //创建后加载uuid字段到Model
        self::created(function ($model) {
            /** @var User $model */
            $model->freshUuid();
            $model->freshStatus();
        });

        //更新前验证字段
        self::updating(function ($model) {
            /** @var User $model */
            $model->validator = Validator::make($model->getDirty(), $model->updateRules);
            return $model->validator->passes();
        });

        //保存前验证字段
        self::saving(function ($model) {
            /** @var User $model */
            $model->validator = Validator::make($model->getDirty(), $model->saveRules);
            $model->conflicted = $model->validator->fails();
            return (! $model->conflicted);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function role()
    {
        return $this->morphTo('role');
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }


    /**
     * @param $email string
     * @return bool|int
     */
    public function updateEmail($email)
    {
        if (!$email) return false;
        if (!$this->exists) return false;

        return $this->update(['email' => $email]);
    }

    /**
     * @param $password string
     * @return bool|int
     */
    public function updatePassword($password)
    {
        if (!$password) return false;
        if (!$this->exists) return false;

        $password = bcrypt($password);

        return $this->update(['password' => $password]);
    }

    /**
     * 在当前对象上从数据库重新获取Uuid
     * @throws Exception
     */
    public function freshStatus()
    {
        if ( ! $this->exists) throw new Exception('Try to fresh status on a non-existent user.');

        $status = DB::table($this->getTable())
            ->select('status')
            ->find($this->getKey())
            ->status;

        if ( ! $status) throw new Exception('An exist user with no status when fresh status.');
        $this->original['status'] = $status;
        $this->attributes['status'] = $status;
    }

    /**
     * 在当前对象上从数据库重新获取Uuid
     * @throws \Exception
     */
    public function freshUuid()
    {
        if ( ! $this->exists) throw new Exception('Try to fresh uuid on a non-existent user.');

        $uuid = DB::table($this->getTable())
                    ->select('uuid')
                    ->find($this->getKey())
                    ->uuid;

        if ( ! $uuid) throw new Exception('An exist user with no uuid when fresh uuid.');
        $this->original['uuid'] = $uuid;
        $this->attributes['uuid'] = $uuid;
    }

    /**
     * $role must exists
     *
     * @param Role $role
     * @return bool
     */
    public function attachRole(Role $role)
    {

        if (!$role->exists) return false;

        if ($this->role_id) return false;
        if ($role->user_id) return false;

        if (! $role->user()->associate($this)->save()) return false;
        if (! $this->role()->associate($role)->save()) return false;

        return true;
    }

    public function detachRole()
    {
        if (! $this->role_id) return false;

        /** @var Role $role */
        $role = $this->role;

        if (! $role->user()->dissociate()->save()) return false;
        if (! $this->role()->dissociate()->save()) return false;

        return true;
    }
}