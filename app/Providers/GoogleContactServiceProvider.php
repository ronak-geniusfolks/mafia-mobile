<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\GoogleContactService;
use Google\Client;
use Illuminate\Support\ServiceProvider;

class GoogleContactServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('GoogleContact', function (): GoogleContactService {
            $client = new Client;
            $jsonFile = env('APP_ENV') === 'local' ? 'client_secret_local.json' : 'client_secret_prod.json';
            $client->setAuthConfig(storage_path($jsonFile)); // Adjusted path
            $client->addScope('https://www.googleapis.com/auth/contacts');
            $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
            $client->setLoginHint(env('GOOGLE_GMAIL_ID')); // Force ronakrafaliya14@gmail.com
            $client->setAccessType('offline');             // Ensure refresh token is issued
            $client->setPrompt('consent select_account');

            return new GoogleContactService($client);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
