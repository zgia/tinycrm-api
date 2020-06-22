<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $userid
 * @property string $openid
 * @property string $unionid
 * @property int $usergroupid
 * @property string $username
 * @property string $realname
 * @property string $email
 * @property string $password
 * @property string $salt
 * @property string $mobile
 * @property int $actived
 * @property string $lastip
 * @property string $lastrealip
 * @property int $lastactivity
 * @property int $dateline
 * @property int $deleted
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'userid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['userid' => 'integer', 'usergroupid' => 'integer', 'actived' => 'integer', 'lastactivity' => 'integer', 'dateline' => 'integer', 'deleted' => 'integer'];

    /**
     * 新用户，ID为0
     *
     * @return array 用户基础信息
     */
    public static function emptyUser()
    {
        return [];
    }
}
