<?php

namespace App\Http\Requests;

use App\Models\Ledger;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Str;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read Ledger $ledger
 */
class CustomRequest extends FormRequest
{
    /**
     * Get the validator instance for the request.
     *
     * @return Validator
     */
    public function getValidatorInstance(): Validator
    {
        $this->transformHashIds();

        return parent::getValidatorInstance();
    }

    protected function transformHashIds()
    {
        $ids = [];

        foreach ($this->all() as $key => $value) {
            if (Str::endsWith($key, '_id') && ! empty($value)) {
                $decoded = Hashids::decode($value);

                if (count($decoded)) {
                    $ids[$key] = $decoded[0];
                }
            }
        }

        if (count($ids)) {
            $this->merge($ids);
//            $this->request->replace($ids);
        }
    }
}
