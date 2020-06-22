<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $logid
 * @property string $fromcontent
 * @property string $tocontent
 */
class Logcontent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Logcontent';

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
    protected $casts = ['logid' => 'integer'];
}
