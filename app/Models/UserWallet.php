<?php

namespace App\Models;

use App\Enums\WalletAccessTypeState;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property WalletAccessTypeState $access_type
 * @property Carbon                $start_date
 * @property Carbon|null           $end_date
 */
class UserWallet extends Pivot
{
    public $timestamps = false;

    protected $fillable = ['start_date', 'end_date', 'access_type'];

    protected $casts = [
        'access_type' => WalletAccessTypeState::class,
    ];

    protected $dates = ['start_date', 'end_date'];

    protected $dateFormat = 'Y-m-d';
}
