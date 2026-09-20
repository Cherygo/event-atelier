<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Mime\Address;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mailtrap:send-test', function () {
    $requiredConfiguration = [
        'MAILTRAP_API_KEY' => (string) config('services.mailtrap.key'),
        'MAILTRAP_FROM_ADDRESS' => (string) config('services.mailtrap.from.address'),
        'MAILTRAP_TO_ADDRESS' => (string) config('services.mailtrap.to'),
    ];

    $missingEnvironmentVariables = array_keys(array_filter(
        $requiredConfiguration,
        fn (mixed $value): bool => blank($value),
    ));

    if ($missingEnvironmentVariables !== []) {
        $this->error('Missing required Mailtrap configuration: '.implode(', ', $missingEnvironmentVariables).'.');

        return Command::FAILURE;
    }

    $email = (new MailtrapEmail)
        ->from(new Address(
            address: $requiredConfiguration['MAILTRAP_FROM_ADDRESS'],
            name: (string) config('services.mailtrap.from.name'),
        ))
        ->to(new Address($requiredConfiguration['MAILTRAP_TO_ADDRESS']))
        ->subject('You are awesome!')
        ->category('Integration Test')
        ->text('Congrats for sending a test email with Mailtrap!');

    $response = MailtrapClient::initSendingEmails(
        apiKey: $requiredConfiguration['MAILTRAP_API_KEY'],
    )->send($email);

    $this->info('Mailtrap accepted the test email.');
    $this->line(json_encode(ResponseHelper::toArray($response), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

    return Command::SUCCESS;
})->purpose('Send a test email through the Mailtrap Email API');
