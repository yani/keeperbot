<?php

namespace Yani\KeeperBot\Commands;

use function React\Async\await;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Yani\KeeperBot\BackgroundTaskHandler;
use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;
use Yani\KeeperBot\BackgroundTask;
use Yani\KeeperBot\BackgroundCommandInterface;
use Yani\KeeperBot\BackgroundCommand;

class PrototypeCommand extends BackgroundCommand implements CommandInterface, BackgroundCommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'prototype',
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {

        $run_id = $parameters[0];

        if (!\is_numeric($run_id)) {
            $message->reply("Invalid workflow run ID");
            return;
        }

        $prototype = $this->getPrototype($run_id);

        if ($prototype) {
            // Return the prototype
            $rounded_size = \round($prototype['size_in_bytes'] / 1024 / 1024, 2);
            $message->channel->sendMessage(MessageBuilder::new()->setContent(
                "Prototype [{$prototype['workflow_run_id']}]: [**__{$prototype['workflow_title']}__**]({$_ENV['KEEPERFX_URL']}/download/prototype/{$prototype['filename']}) ({$rounded_size}MiB)"
            ));

            return;
        }

        // Tell the user the prototype will come
        $message->channel->sendMessage(MessageBuilder::new()->setContent(
            "Waiting for prototype **{$run_id}** to be ready..."
        ));

        // Add the background task
        $this->addBackgroundTask(new BackgroundTask(
            $discord,
            $message,
            [$run_id],
            $this,
            60,
            30,
            new \DateTime('now')
        ));
    }

    private function getPrototype($run_id): array|false
    {
        try {

            $browser = Utility::createBrowserInstance();
            $promise = $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/prototype/run/' . $run_id);

            $response = await($promise);

            // Make sure response is not NULL
            if (\is_null($response)) {
                return false;
            }

            // Handle non-200 response
            if ($response->getStatusCode() !== 200) {
                return false;
            }

            // Get the body
            $body = (string)$response->getBody();
            if (empty($body)) {
                return false;
            }

            $json = \json_decode($body, true);
            if (empty($json) || !is_array($json) || !isset($json['prototype']) || !is_array($json['prototype'])) {
                return false;
            }
        } catch (\Exception $ex) {

            return false;
        }

        return $json['prototype'];
    }

    public function runBackgroundTask(Discord $discord, Message $message, array $parameters = []): bool
    {
        $run_id = $parameters[0];
        $prototype = $this->getPrototype($run_id);

        if ($prototype) {
            // Return the prototype
            $rounded_size = \round($prototype['size_in_bytes'] / 1024 / 1024, 2);
            $message->channel->sendMessage(MessageBuilder::new()->setContent(
                "Prototype [{$prototype['workflow_run_id']}]: [**__{$prototype['workflow_title']}__**]({$_ENV['KEEPERFX_URL']}/download/prototype/{$prototype['filename']}) ({$rounded_size}MiB)"
            ));

            return true;
        }

        return false;
    }

    public function runBackgroundTaskTimeout(Discord $discord, Message $message, array $parameters = []): void
    {
        $run_id = $parameters[0];
        $message->channel->sendMessage(MessageBuilder::new()->setContent(
            "Prototype [{$run_id}]: Timed out..."
        ));
    }
}
