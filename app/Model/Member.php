<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int    $memberid
 * @property int    $userid
 * @property string $membername
 * @property string $mobile
 * @property string $tel
 * @property string $birthday
 * @property int    $lastvisit
 * @property int    $dateline
 * @property int    $deleted
 * @property int    $signed_status
 * @property int    $saler_status
 * @property int    $gender
 * @property int    $marital_status
 * @property string $idcard
 * @property string $personality
 * @property string $financing_habit
 * @property string $hobby
 * @property string $home_address
 * @property string $home_address_postcode
 * @property string $company
 * @property string $company_title
 * @property string $company_address
 * @property string $company_address_postcode
 * @property int    $annual_income
 * @property string $description
 */
class Member extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'memberid';

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
    protected $casts = [
        'memberid' => 'integer',
        'userid' => 'integer',
        'lastvisit' => 'integer',
        'dateline' => 'integer',
        'deleted' => 'integer',
        'signed_status' => 'integer',
        'saler_status' => 'integer',
        'gender' => 'integer',
        'marital_status' => 'integer',
        'annual_income' => 'integer',
    ];

    public function freshTimestamp()
    {
        return 1;
    }

    /**
     * 新客户，ID为0
     *
     * @return array 用户基础信息
     */
    public static function emptyMember()
    {
        return [];
    }
}
