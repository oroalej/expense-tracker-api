<?php

namespace App\Listeners\Registered;

use App\Actions\Wallet\CreateWallet;
use App\DTO\WalletData;
use App\Enums\WalletTypeState;
use Illuminate\Auth\Events\Registered;
use Throwable;

class CreateCashWallet
{
    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     *
     * @throws Throwable
     */
    public function handle(Registered $event): void
    {
        $walletData = new WalletData('Cash', null, 0, WalletTypeState::Cash);

        (new CreateWallet($walletData, $event->user))->execute();
    }
}
