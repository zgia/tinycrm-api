<?php

declare(strict_types=1);

namespace App\Model;

class Cagtegory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cagtegory';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'cagtegory';

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
    protected $casts = [];
}
