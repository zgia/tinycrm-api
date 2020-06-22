<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $familyid
 * @property int $memberid
 * @property string $membername
 * @property string $birthday
 * @property string $mobile
 * @property string $description
 * @property int $relationship
 * @property int $relationshipid
 * @property int $deleted
 */
class Family extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'family';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'familyid';

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
    protected $casts = ['familyid' => 'integer', 'memberid' => 'integer', 'relationship' => 'integer', 'relationshipid' => 'integer', 'deleted' => 'integer'];
}
