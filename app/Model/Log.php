<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $logid
 * @property string $type
 * @property int $objectid
 * @property string $script
 * @property string $action
 * @property string $ipaddress
 * @property int $userid
 * @property int $dateline
 */
class Log extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Log';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'logid';

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
    protected $casts = ['logid' => 'integer', 'objectid' => 'integer', 'userid' => 'integer', 'dateline' => 'integer'];
}
