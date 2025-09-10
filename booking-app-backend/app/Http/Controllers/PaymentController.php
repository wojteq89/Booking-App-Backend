<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Pobiera Access Token OAuth z PayU
     */
    private function getAccessToken(): string
    {
        $client = new Client();

        $response = $client->post(env('PAYU_OAUTH_URL'), [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('PAYU_CLIENT_ID'),
                'client_secret' => env('PAYU_CLIENT_SECRET'),
            ],
            'allow_redirects' => false,
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['access_token'])) {
            throw new \Exception('Nie udało się pobrać tokena PayU');
        }

        return $data['access_token'];
    }

    /**
     * Tworzy zamówienie w PayU i zwraca link do płatności
     */
    public function pay(Request $request, Appointment $appointment)
    {
        try {
            $accessToken = $this->getAccessToken();

            // Pobranie danych kupującego
            $buyerEmail = optional($request->user())->email ?? $request->input('email', 'test@example.com');
            $buyerName = optional($request->user())->name ?? $request->input('firstName', 'Klient');

            // Kwota w groszach
            $amount = $appointment->serviceItem->price * 100;

            $order = [
                'notifyUrl' => route('payment.notify'),
                'continueUrl' => route('payment.success'),
                'customerIp' => $request->ip(),
                'merchantPosId' => env('PAYU_POS_ID'),
                'description' => 'Wizyta: ' . $appointment->serviceItem->name,
                'currencyCode' => 'PLN',
                'totalAmount' => $amount,
                'extOrderId' => uniqid('order_'),
                'buyer' => [
                    'email' => $buyerEmail,
                    'firstName' => $buyerName,
                ],
                'products' => [
                    [
                        'name' => $appointment->serviceItem->name,
                        'unitPrice' => $amount,
                        'quantity' => 1,
                    ]
                ],
            ];

            $client = new Client();
            $response = $client->post(env('PAYU_ORDER_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $order,
                'allow_redirects' => false,
            ]);

            $result = json_decode($response->getBody(), true);

            if (!isset($result['redirectUri'])) {
                return response()->json([
                    'error' => 'Nie udało się wygenerować linku do płatności',
                    'result' => $result
                ], 500);
            }

            return response()->json(['redirectUri' => $result['redirectUri']]);

        } catch (\Exception $e) {
            Log::error('PAYU Payment Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Wystąpił błąd podczas tworzenia płatności',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Strona sukcesu płatności
     */
    public function success()
    {
        return view('payment.success'); // np. "Płatność zakończona"
    }

    /**
     * Webhook z PayU - aktualizacja statusu wizyty
     */
    public function notify(Request $request)
    {
        $data = $request->all();

        $extOrderId = data_get($data, 'order.extOrderId', null);

        if ($extOrderId) {
            $appointment = Appointment::where('ext_order_id', $extOrderId)->first();

            if ($appointment) {
                $appointment->status = 'Zakończona';
                $appointment->save();

                Log::info('PAYU notify - wizyta zaktualizowana', [
                    'appointment_id' => $appointment->id,
                    'extOrderId' => $extOrderId,
                    'payload' => $data
                ]);
            } else {
                Log::warning('PAYU notify - brak wizyty dla extOrderId', [
                    'extOrderId' => $extOrderId,
                    'payload' => $data
                ]);
            }
        } else {
            Log::warning('PAYU notify - brak pola order.extOrderId', $data);
        }

        return response()->json(['status' => 'OK']);
    }
}
