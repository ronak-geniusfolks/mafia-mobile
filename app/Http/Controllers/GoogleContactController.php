<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleContactController extends Controller
{
    protected $googleContactService;

    public function __construct()
    {
        $this->googleContactService = app('GoogleContact');
    }

    // Redirect to Google for authentication
    public function redirectToGoogle()
    {
        return redirect($this->googleContactService->getAuthUrl());
    }

    // Handle callback from Google
    public function handleGoogleCallback(Request $request)
    {
        $result = $this->googleContactService->authenticate($request->get('code'));
        if ($result['success']) {
            // Retrieve and decode the state parameter
            $state = $request->get('state');
            $customerData = $state ? json_decode(base64_decode((string) $state), true) : [];

            // Return a view with a form that auto-submits to sync.contact
            return view('google.google_redirect', [
                'customer_name' => $customerData['customer_name'] ?? 'John Doe',
                'customer_no' => $customerData['customer_no'] ?? '+1234567890',
                'sync_url' => route('sync.contact'),
            ]);
        }

        return response()->json(['error' => $result['error']], 500);
    }

    // Sync contact (for standalone testing)
    public function syncContact(Request $request)
    {
        $result = $this->googleContactService->syncContact($request);
        if ($result['success']) {
            return redirect()->route('allinvoices')->withStatus('Contact Synced Successfully..');
        }
        if (isset($result['redirect'])) {
            return redirect($result['redirect']);
        }

        return response()->json(['error' => $result['error']], 500);
    }
}
