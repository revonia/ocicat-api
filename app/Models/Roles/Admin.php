<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/9
 * Time: 16:40
 */

namespace App\Models\Roles;

use App\Models\Role;
use App\Transformers\AdminTransformer;
use Illuminate\Database\Query\Builder;

/**
 * App\Models\Roles\Admin
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User $morphUser
 * @property-read \App\Models\User $user
 * @method static Builder|Admin whereId($value)
 * @method static Builder|Admin whereUserId($value)
 * @method static Builder|Admin whereDeletedAt($value)
 * @method static Builder|Admin whereCreatedAt($value)
 * @method static Builder|Admin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Admin extends Role
{
    const TABLE_NAME = 'admins';

    const TRANSFORMER = AdminTransformer::class;

    protected $morphClass = 'admin';

    protected $fillable = ['group'];


}