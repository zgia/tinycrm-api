<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $fileid
 * @property int $memberid
 * @property string $url
 * @property string $description
 * @property int $dateline
 * @property int $deleted
 */
class File extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'fileid';

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
    protected $casts = ['fileid' => 'integer', 'memberid' => 'integer', 'dateline' => 'integer', 'deleted' => 'integer'];
}
