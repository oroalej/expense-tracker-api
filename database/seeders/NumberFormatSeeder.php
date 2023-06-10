<?php

namespace Database\Seeders;

use App\Models\NumberFormat;
use Illuminate\Database\Seeder;

class NumberFormatSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->getData() as $data) {
            NumberFormat::create($data);
        }
    }

    public function getData(): array
    {
        return [
            [
                'example'            => '123,456.78',
                'decimal_digits'     => 2,
                'decimal_separator'  => ".",
                'thousand_separator' => ",",
            ],
            [
                'example'            => '123,456',
                'decimal_digits'     => 0,
                'decimal_separator'  => ".",
                'thousand_separator' => ",",
            ],
            [
                'example'            => '123 456 78',
                'decimal_digits'     => 2,
                'decimal_separator'  => " ",
                'thousand_separator' => " ",
            ],
            [
                'example'            => '123 456',
                'decimal_digits'     => 0,
                'decimal_separator'  => " ",
                'thousand_separator' => " ",
            ]
        ];
    }
}
