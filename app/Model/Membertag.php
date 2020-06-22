<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $memberid
 * @property int $tagid
 * @property int $userid
 */
class Membertag extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'membertag';

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
    protected $casts = ['id' => 'integer', 'memberid' => 'integer', 'tagid' => 'integer', 'userid' => 'integer'];
}
