<?php

namespace App\Actions\Wallet;

use App\DataObject\WalletData;
use App\Enums\WalletAccessTypeState;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateWallet
{
    public function __construct(
        protected WalletData $attributes,
        protected User|Authenticatable $user
    ) {
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        DB::transaction(function () {
            $wallet = Wallet::create([
                'name' => $this->attributes->name,
                'description' => $this->attributes->description,
                'current_balance' => $this->attributes->current_balance,
                'wallet_type' => $this->attributes->wallet_type->value,
            ]);

            $wallet->users()->attach($this->user->id, [
                'access_type' => WalletAccessTypeState::Owner->value,
            ]);
        });
    }
}
