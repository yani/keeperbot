<?php

namespace Yani\KeeperBot\Commands;

use Yani\KeeperBot\Utility;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Psr\Http\Message\ResponseInterface;


class InspireCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'inspire',
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $browser = Utility::createBrowserInstance();

        $browser->get("https://inspirobot.me/api?generate=true")
            ->then(function (ResponseInterface $response) use ($message, $discord, $browser) {

                if ($response->getStatusCode() === 404) {
                    $message->reply('I failed to get inspired');
                    return;
                }

                $url = (string)$response->getBody();
                if (empty($url)) {
                    $message->reply("I failed to get inspired");
                    return;
                }

                if (preg_match('#^https://generated\.inspirobot\.me/a/([A-Za-z0-9]+)\.jpg$#', $url, $matches) == false) {
                    $message->reply("Invalid inspiration");
                    return;
                }

                $browser->get($url)->then(function (ResponseInterface $response) use ($message, $discord, $browser) {

                    if ($response->getStatusCode() === 404) {
                        $message->reply('I failed to get inspired');
                        return;
                    }

                    $body = (string)$response->getBody();
                    if (empty($body)) {
                        $message->reply("I failed to get inspired");
                        return;
                    }

                    // Create a local temp image file
                    $temp_file_path = tempnam(sys_get_temp_dir(), 'inspire_') . '.jpg';
                    \file_put_contents($temp_file_path, $body);

                    // Send message
                    $message->channel->sendMessage(
                        MessageBuilder::new()->addFile($temp_file_path)
                    );
                });
            });
    }
}
