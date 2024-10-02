<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\EmailUpdateService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSEController extends Controller
{

    public function sse($oauth_id, $provider)
    {
        $response = new StreamedResponse(function () use ($oauth_id, $provider) {

            ob_start();

            while (true) {

                $data = EmailUpdateService::getEmailUpdatesForAccount($oauth_id, $provider);

                if ($data) {
                    echo "data: " . json_encode($data) . "\n\n"; // Send data as SSE
                    ob_flush();
                    flush(); 
                }

                // Sleep for a certain period before the next iteration
                sleep(5);
            }
        });

        // Set headers for SSE
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }

}
