<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;
use Psr\Http\Message\ResponseInterface;

class RandomMapCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'randommap',
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $browser = Utility::createBrowserInstance();

        $browser->withFollowRedirects(false)
        ->get($_ENV['KEEPERFX_URL'] . '/workshop/random/map')
        ->then(function (ResponseInterface $response) use ($message) {

            // Get the URL
            $url = $response->getHeaderLine("Location");
            if(empty($url)){
                $message->reply("Invalid server response...");
                return;
            }

            // Make absolute URL
            $url = $_ENV['KEEPERFX_URL'] . '' . $url;
            $url = explode('#', $url)[0];

            // Send to user
            $message->reply($url);

        });
    }
}