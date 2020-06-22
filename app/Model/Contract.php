<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $contractid
 * @property int $memberid
 * @property string $signed_day
 * @property string $insurance_title
 * @property int $insured_amount
 * @property int $premium
 * @property string $policy_holder
 * @property string $recognizee
 * @property int $dateline
 */
class Contract extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contract';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'contractid';

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
    protected $casts = ['contractid' => 'integer', 'memberid' => 'integer', 'insured_amount' => 'integer', 'premium' => 'integer', 'dateline' => 'integer'];
}
