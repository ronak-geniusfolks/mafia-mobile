<?php
namespace App\Services;

use App\Models\Invoice;
use Google\Client;
use Google\Service\PeopleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GoogleContactService
{
    public function __construct(protected Client $client)
    {
    }

    private function getTokenPath(): string
    {
        $email = env('GOOGLE_GMAIL_ID', 'default');
        $safeEmail = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $email));
        return "google_access_token_{$safeEmail}.json";
    }

    public function getAuthUrl(string $state = ''): string
    {
        if ($state !== '' && $state !== '0') {
            $this->client->setState($state);
        }
        return $this->client->createAuthUrl();
    }

    public function authenticate(string $code): array
    {
        try {
            $this->client->authenticate($code);
            $accessToken = $this->client->getAccessToken();
            if (! empty($accessToken['refresh_token'])) {
                Storage::put($this->getTokenPath(), json_encode($accessToken));
            }
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Authentication failed: ' . $e->getMessage()];
        }
    }

    public function syncContact(Request $request): array | RedirectResponse
    {
        if (! Storage::exists($this->getTokenPath())) {
            $state = base64_encode(json_encode([
                'customer_name' => $request->customer_name,
                'customer_no'   => $request->customer_no,
                'invoice_id'    => $request->invoice_id,
            ]));
            return ['success' => false, 'redirect' => $this->getAuthUrl($state)];
        }

        $accessToken = json_decode(Storage::get($this->getTokenPath()), true);
        if (! $accessToken) {
            return ['success' => false, 'redirect' => route('google.redirect')];
        }

        $this->client->setAccessToken($accessToken);

        if ($this->client->isAccessTokenExpired()) {
            try {
                $refreshToken = $this->client->getRefreshToken();
                if (! $refreshToken) {
                    return ['success' => false, 'redirect' => route('google.redirect')];
                }
                $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                $newAccessToken = $this->client->getAccessToken();

                if (empty($newAccessToken['refresh_token']) && isset($accessToken['refresh_token'])) {
                    $newAccessToken['refresh_token'] = $accessToken['refresh_token'];
                }
                Storage::put($this->getTokenPath(), json_encode($newAccessToken));
            } catch (\Exception) {
                Storage::delete($this->getTokenPath());
                return ['success' => false, 'redirect' => route('google.redirect'), 'error' => 'Token refresh failed'];
            }
        }

        try {
            $peopleService = new PeopleService($this->client);

            $name        = $request->input('customer_name', 'John Doe');
            $phoneNumber = $request->input('customer_no', '+1234567890');

            // Check if contact already exists
            $existing = $this->findContactByNumberOrName($phoneNumber, $name);
            if ($existing) {
                $invoice = Invoice::find($request->invoice_id);
                if ($invoice) {
                    $invoice->sync_contact = 1;
                    $invoice->save();
                }
                return ['success' => true, 'message' => 'Contact already exists, sync skipped.'];
            }

            $person     = new \Google\Service\PeopleService\Person;
            $personName = new \Google\Service\PeopleService\Name;
            $personName->setGivenName($name);
            $person->setNames([$personName]);

            $phone = new \Google\Service\PeopleService\PhoneNumber;
            $phone->setValue($phoneNumber);
            $person->setPhoneNumbers([$phone]);

            $newContact = $peopleService->people->createContact($person, ['personFields' => 'names,phoneNumbers']);

            $invoice = Invoice::find($request->invoice_id);
            if ($invoice) {
                $invoice->sync_contact = 1;
                $invoice->save();
            }

            return ['success' => true, 'message' => 'Contact synced successfully!', 'contact' => $newContact->getResourceName()];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'unauthorized') || str_contains($e->getMessage(), 'invalid_grant')) {
                Storage::delete($this->getTokenPath());
                return ['success' => false, 'redirect' => route('google.redirect')];
            }
            return ['success' => false, 'error' => 'Sync failed: ' . $e->getMessage()];
        }
    }

    public function findContactByNumberOrName($number, $name)
    {
        $contacts = $this->getAllContacts();
        foreach ($contacts as $contact) {
            if (
                (isset($contact['phone']) && $contact['phone'] === $number) ||
                (isset($contact['name']) && strtolower($contact['name']) === strtolower($name))
            ) {
                return $contact;
            }
        }
        return null;
    }

    public function getAllContacts(): array
    {
        $contacts = [];

        if (! Storage::exists($this->getTokenPath())) {
            return $contacts;
        }

        $accessToken = json_decode(Storage::get($this->getTokenPath()), true);
        if (! $accessToken) {
            return $contacts;
        }

        $this->client->setAccessToken($accessToken);

        if ($this->client->isAccessTokenExpired()) {
            try {
                $refreshToken = $this->client->getRefreshToken();
                if ($refreshToken) {
                    $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    $newAccessToken = $this->client->getAccessToken();
                    if (empty($newAccessToken['refresh_token']) && isset($accessToken['refresh_token'])) {
                        $newAccessToken['refresh_token'] = $accessToken['refresh_token'];
                    }
                    Storage::put($this->getTokenPath(), json_encode($newAccessToken));
                }
            } catch (\Exception) {
                Storage::delete($this->getTokenPath());
                return $contacts;
            }
        }

        try {
            $peopleService = new PeopleService($this->client);
            $pageToken     = null;

            do {
                $response = $peopleService->people_connections->listPeopleConnections(
                    'people/me',
                    [
                        'pageSize'     => 1000,
                        'pageToken'    => $pageToken,
                        'personFields' => 'names,phoneNumbers',
                    ]
                );

                if ($response->getConnections()) {
                    foreach ($response->getConnections() as $person) {
                        $personName = $person->getNames()[0]->getDisplayName() ?? null;
                        $phone      = $person->getPhoneNumbers()[0]->getValue() ?? null;

                        $contacts[] = [
                            'name'  => $personName,
                            'phone' => $phone,
                        ];
                    }
                }

                $pageToken = $response->getNextPageToken();
            } while ($pageToken);

            return $contacts;
        } catch (\Exception) {
            return [];
        }
    }
}