<?php

namespace App\Http\Controllers;

use App\Actions\Ledger\CreateLedger;
use App\Actions\Ledger\UpdateLedger;
use App\DataTransferObjects\LedgerData;
use App\Http\Requests\StoreLedgerRequest;
use App\Http\Requests\UpdateLedgerRequest;
use App\Http\Resources\LedgerResource;
use App\Models\Ledger;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreLedgerRequest  $request
     * @param  CreateLedger  $createLedgerAction
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(
        StoreLedgerRequest $request,
        CreateLedger $createLedgerAction
    ): JsonResponse {
        $ledger = $createLedgerAction->execute(
            new LedgerData(
                name: $request->name,
                user: auth()->user(),
            )
        );

        return response()->json(
            new LedgerResource($ledger),
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  Ledger  $ledger
     */
    public function show(Ledger $ledger)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateLedgerRequest  $request
     * @param  Ledger  $ledger
     * @param  UpdateLedger  $updateLedgerAction
     * @return JsonResponse
     */
    public function update(
        UpdateLedgerRequest $request,
        Ledger $ledger,
        UpdateLedger $updateLedgerAction
    ): JsonResponse {
        $ledger = $updateLedgerAction->execute(
            $ledger,
            new LedgerData(name: $request->name, user: auth()->user())
        );

        return response()->json(new LedgerResource($ledger));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Ledger  $ledger
     */
    public function destroy(Ledger $ledger)
    {
    }
}
