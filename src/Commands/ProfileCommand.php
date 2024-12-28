<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

use Psr\Http\Message\ResponseInterface;

class ProfileCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['profile', 'bio'],
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $browser = Utility::createBrowserInstance();
        $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/user/search?discord_id=' . $message->author->id)
            ->then(function (ResponseInterface $response) use ($message, $discord) {

                // Get body of response
                $body = (string)$response->getBody();
                if (empty($body)) {
                    $message->reply("Failed to connect to website API...");
                    return;
                }

                // Decode JSON
                $json = \json_decode($body, true);
                if (empty($json) || !is_array($json) || !isset($json['success'])) {
                    $message->reply("Invalid server response...");
                    return;
                }

                if($json['success'] === false || !isset($json['user'])){
                    $message->reply("You do not have your Discord and KeeperFX.net account linked. You can do so in your [Account Connections](<https://keeperfx.net/account/connections>).");
                    return;
                }

                // Description
                $description = null;
                if(\strlen($json['user']['bio']) > 200){
                    $description = \substr($json['user']['bio'], 0, 197) . '...';
                } else {
                    $description = $json['user']['bio'];
                }

                // Create embed for the alpha patch
                $embed = new Embed($discord, [
                    'title'       => $json['user']['username'],
                    'url'         => $_ENV['KEEPERFX_URL'] . '/workshop/user/' . \urlencode($json['user']['username']),
                    'description' => $description,
                    'color'       => 16777215,
                    'thumbnail'   => ['url' => $json['user']['avatar'] ? $_ENV['KEEPERFX_URL'] . '/avatar/' . \urlencode($json['user']['avatar']) : null],
                    'fields'      => [
                        ['name' => 'Country', 'value' => ':flag_' . \strtolower($json['user']['country']) . ':', 'inline' => true],
                        ['name' => 'Workshop Items', 'value' => $json['user']['item_count'], 'inline' => true],
                        ['name' => 'Ratings', 'value' => $json['user']['rating_count'], 'inline' => true],
                        ['name' => 'Difficulty Ratings', 'value' => $json['user']['difficulty_rating_count'], 'inline' => true],
                    ],
                ]);

                // Send the embed as a message to the user
                $message->channel->sendEmbed($embed);
            });
    }
}


















                