<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;
use Psr\Http\Message\ResponseInterface;
use Discord\Parts\Embed\Embed;

class LatestStableReleaseCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'stable',
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        
        $browser = Utility::createBrowserInstance();
        $browser
            ->get($_ENV['KEEPERFX_URL'] . '/api/v1/stable/latest')
            ->then(function (ResponseInterface $response) use ($discord, $message) {

                // Get body of response
                $body = (string)$response->getBody();
                if(empty($body)){
                    $message->reply("Failed to connect to website API...");
                    return;
                }

                // Decode JSON
                $json = \json_decode($body, true);
                if(empty($json) || !is_array($json) || !isset($json['release']) || !is_array($json['release'])){
                    $message->reply("Invalid server response...");
                    return;
                }

                // Create embed for the stable release
                $embed = new Embed($discord, [
                    'title'       => $json['release']['name'],
                    'description' => 'Full stable release for KeeperFX ' . $json['release']['tag'],
                    'url'         => $json['release']['download_url'],
                    'timestamp'   => (new \DateTime($json['release']['timestamp']))->format('Y-m-d H:i'),
                    'footer'      => ['text' => ((string) \round($json['release']['size_in_bytes'] / 1024 / 1024, 2)) . 'MiB'],
                    'color'       => 455682, // #06f402
                    'thumbnail'   => ['url' => $_ENV['KEEPERFX_URL'] . '/img/download.png'],
                ]);

                // Send the embed as a message to the user
                $message->channel->sendEmbed($embed);

            });
    }
}