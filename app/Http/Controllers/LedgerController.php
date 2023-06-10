<?php

namespace App\Http\Controllers;

use App\DTO\LedgerData;
use App\Http\Requests\Store\StoreLedgerRequest;
use App\Http\Requests\Update\UpdateLedgerRequest;
use App\Http\Resources\LedgerResource;
use App\Models\Currency;
use App\Models\Ledger;
use App\Services\LedgerService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $ledgers = Ledger::with('currency:id,name,abbr,code,locale')
            ->orderBy('updated_at')->get();

        return $this->apiResponse([
            'data' => LedgerResource::collection($ledgers)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreLedgerRequest  $request
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(
        StoreLedgerRequest $request,
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $ledger = (new LedgerService())->store(
                new LedgerData(
                    name: $request->validated('name'),
                    date_format: $request->validated('date_format'),
                    user: auth()->user(),
                    currency: Currency::find($request->get('currency_id'))
                )
            );
            DB::commit();

            return $this->apiResponse([
                'data' => new LedgerResource($ledger)
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Ledger  $ledger
     * @return JsonResponse
     */
    public function show(Ledger $ledger): JsonResponse
    {
        return $this->apiResponse([
            'data' => new LedgerResource($ledger)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateLedgerRequest  $request
     * @param  Ledger  $ledger
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(
        UpdateLedgerRequest $request,
        Ledger $ledger,
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $ledger = (new LedgerService())->update(
                $ledger,
                new LedgerData(
                    name: $request->validated('name'),
                    date_format: $request->validated('date_format'),
                    user: auth()->user(),
                    currency: Currency::find($request->get('currency_id'))
                )
            );
            DB::commit();

            return $this->apiResponse([
                'data' => new LedgerResource($ledger)
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  Ledger  $ledger
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(Ledger $ledger): JsonResponse
    {
        DB::beginTransaction();

        try {
            (new LedgerService())->delete($ledger);

            DB::commit();

            return $this->apiResponse([
                'data' => new LedgerResource($ledger)
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
