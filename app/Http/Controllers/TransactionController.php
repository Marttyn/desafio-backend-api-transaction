<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class TransactionController extends Controller
{
    /**
     * Transfer value between two users
     *
     * @param  Request  $request
     * @return Response
     */
    public function transaction(Request $request): Response
    {
        $request->validate([
            'value' => 'numeric|gt:0',
            'payer' => 'exists:App\Models\User,id',
            'payee' => 'exists:App\Models\User,id'
        ]);

        $value = $request['value'];
        $payer = User::find($request['payer']);
        $payee = User::find($request['payee']);

        if ($payer->isShopkeeper) {
            return response(
                ['error' => "It's not possible to complete the transaction, payer is a Shopkeeper."],
                401
            );
        }

        if ($payer->balance < $value) {
            return response(
                ['error' => "Payer don't have enough balance."],
                401
            );
        }

        try {
            $response = Http::get('https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6');

            if ($response->json()['message'] !== 'Autorizado') {
                return response(
                    ['error' => "Transaction not authorized."],
                    401
                );
            }

            DB::transaction(function () use ($value, $payer, $payee) {
                $payer->balance -= $value;
                $payee->balance += $value;

                $payer->save();
                $payee->save();

                $emailResponse = Http::get('http://o4d9z.mocklab.io/notify');

                if ($emailResponse->json()['message'] !== 'Success') {
                    throw new Exception('Timeout to send email.', 408);
                }
            });

            return response('Transaction was successfully.');
        } catch (Throwable $exception) {
            return response(
                ['error' => $exception->getMessage()],
                500
            );
        }
    }
}
