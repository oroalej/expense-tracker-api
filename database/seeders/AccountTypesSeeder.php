<?php

namespace Database\Seeders;

use App\Enums\AccountGroupTypeState;
use App\Enums\AccountTypeState;
use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypesSeeder extends Seeder
{
    public function run(): void
    {
        $this->insertData($this->getBudgets(), AccountGroupTypeState::Budget);
        $this->insertData($this->getDebts(), AccountGroupTypeState::Debt);
        $this->insertData($this->getTracking(), AccountGroupTypeState::Tracking);
    }

    public function insertData(array $list, AccountGroupTypeState $group): void
    {
        foreach ($list as $item) {
            if (! is_array($item)) {
                $item = [
                    'name' => $item,
                ];
            }

            $accountType = new AccountType($item);
            $accountType->accountGroupType()->associate($group->value);
            $accountType->save();
        }
    }

    public function getBudgets(): array
    {
        return [
            [
                'id' => AccountTypeState::Cash->value,
                'name' => 'Cash',
            ],
            'Joint',
            'Savings',
            'Payroll',
            'Checking',
        ];
    }

    public function getDebts(): array
    {
        return [
            'Auto Loan',
            'Educational Loan',
            'Personal Loan',
            'Medical Debt',
            'Mortgage',
            'Credit Card',
            'Line of Credit',
            'Other Debt',
        ];
    }

    public function getTracking(): array
    {
        return [
            'Cryptocurrency',
            'Stocks',
            'Bonds',
        ];
    }
}
