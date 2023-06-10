<?php

namespace App\Http\Requests;

use App\Models\Ledger;
use Illuminate\Foundation\Http\FormRequest;
use Str;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read Ledger $ledger
 */
class CustomRequest extends FormRequest
{
    protected function passedValidation()
    {
        $ids = [];

        foreach ($this->request->all() as $key => $value) {
            if (Str::endsWith($key, '_id') && !empty($value)) {
                $ids[$key] = Hashids::decode($value)[0];
            }
        }

        if (count($ids)) {
            $this->merge($ids);
            $this->request->replace($ids);
        }
    }
}
