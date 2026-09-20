<?php

namespace Tests\Feature;

use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

class MailtrapSendTest extends TestCase
{
    public function test_command_fails_before_sending_when_required_configuration_is_missing(): void
    {
        config([
            'services.mailtrap.key' => null,
            'services.mailtrap.from.address' => null,
            'services.mailtrap.to' => null,
        ]);

        $this->artisan('mailtrap:send-test')
            ->expectsOutput('Missing required Mailtrap configuration: MAILTRAP_API_KEY, MAILTRAP_FROM_ADDRESS, MAILTRAP_TO_ADDRESS.')
            ->assertExitCode(Command::FAILURE);
    }
}
