<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{

    public function __construct(protected TransactionService $transactionService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            //Will do this validation via FormRequest class in production environment.
            $data = $request->validate([
                'seller_id' => ['required', 'integer', 'min:1'],
                'amount' => ['required', 'integer', 'min:1'],
                'currency' => ['required', 'string', 'size:3'],
                'payment_provider' => ['required', 'string', Rule::in(['stripe', 'paypal', 'ideal'])],
                'customer_id' => ['required', 'integer', 'min:1'],
                'idempotency_key' => ['required', 'string', 'max:255'],
            ]);


            $transaction = $this->transactionService->createTransaction($data);
            return (new TransactionResource($transaction))
                ->response()
                ->setStatusCode(201);

        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $transactionId)
    {
        //
        $transaction = Transaction::where('public_id', $transactionId)->first();

        if (!$transaction) {
            return response()->json([
                'error' => 'Transaction not found',
                'message' => "Transaction with ID {$transactionId} does not exist",
            ], 404);
        }

        return new TransactionResource($transaction);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
