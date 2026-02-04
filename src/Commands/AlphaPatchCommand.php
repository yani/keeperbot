<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Psr\Http\Message\ResponseInterface;

use Yani\KeeperBot\Utility;

class AlphaPatchCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command' => 'alpha'
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        if (count($parameters) === 0) {
            $url = $_ENV['KEEPERFX_URL'] . '/api/v1/release/alpha/latest';
        } else {
            $url = $_ENV['KEEPERFX_URL'] . '/api/v1/release/alpha/build/' . $parameters[0];
        }

        $browser = Utility::createBrowserInstance();
        $browser
            ->get($url)
            ->then(function (ResponseInterface $response) use ($discord, $message) {

                // Get body of response
                $body = (string)$response->getBody();
                if (empty($body)) {
                    $message->reply("Failed to connect to website API...");
                    return;
                }

                // Decode JSON
                $json = \json_decode($body, true);
                if (empty($json) || !is_array($json) || !isset($json['alpha_build']) || !is_array($json['alpha_build'])) {
                    $message->reply("Invalid server response...");
                    return;
                }

                // Handle description (add ellipsis and markdown)
                $description = $json['alpha_build']['workflow_title'];
                $description = \preg_replace('/\s*(\(.*?\…)/', '…', $description);
                $description = \preg_replace('/\(\#(\d{1,6})\)/', '([#$1](https://github.com/dkfans/keeperfx/issues/$1))', $description);

                // Create embed for the alpha patch
                $embed = new Embed($discord, [
                    'title'       => $json['alpha_build']['name'],
                    'description' => $description,
                    'url'         => $json['alpha_build']['download_url'],
                    'timestamp'   => (new \DateTime($json['alpha_build']['timestamp']))->format('Y-m-d H:i'),
                    'footer'      => ['text' => ((string) \round($json['alpha_build']['size_in_bytes'] / 1024 / 1024, 2)) . 'MiB'],
                    'color'       => 11797236, // #b402f4
                ]);

                // Send the embed as a message to the user
                $message->channel->sendEmbed($embed);
            });
    }
}
