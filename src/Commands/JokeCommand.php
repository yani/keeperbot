<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Psr\Http\Message\ResponseInterface;

use Yani\KeeperBot\Utility;


class JokeCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'joke',
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        // Get browser
        $browser = Utility::createBrowserInstance();

        // Async request
        $browser->get("https://v2.jokeapi.dev/joke/Any?type=single")
            ->then(function (ResponseInterface $response) use ($message, $discord) {

                // Make sure this the page returned something
                if ($response->getStatusCode() === 404) {
                    $message->reply('Joke not found');
                    return;
                }

                // Get body of response
                $body = (string)$response->getBody();
                if (empty($body)) {
                    $message->reply("Failed to connect to joke API...");
                    return;
                }

                // Decode JSON
                $json = \json_decode($body, true);
                if (empty($json) || !is_array($json)) {
                    $message->reply("Invalid joke response...");
                    return;
                }

                // Send message
                $message->channel->sendMessage(MessageBuilder::new()->setContent(
                    $json['joke']
                ));
            });
    }
}
