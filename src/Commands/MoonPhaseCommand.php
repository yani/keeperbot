<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

use Psr\Http\Message\ResponseInterface;

class MoonPhaseCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command' => ['moon'],
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $browser = Utility::createBrowserInstance();
        $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/moonphase')
            ->then(function (ResponseInterface $response) use ($message, $discord, $parameters) {

                // Get body of response
                $body = (string)$response->getBody();
                if (empty($body)) {
                    $message->reply("Failed to connect to website API...");
                    return;
                }

                // Decode JSON
                $json = \json_decode($body, true);
                if (empty($json) || !is_array($json) || !isset($json['phase'])) {
                    $message->reply("Invalid server response...");
                    return;
                }

                // Get variables
                $title             = (string) ($json['name'] ?? 'Unknown');
                $phase             = \round((float)($json['phase'] ?? 0), 10);
                $image             = $_ENV['KEEPERFX_URL'] . (string) ($json['img'] ?? '');
                $is_full_moon      = (bool) $json['is_full_moon'];
                $is_near_full_moon = (bool) $json['is_near_full_moon'];
                $next_full_moon    = new \DateTime((string)($json['next_full_moon']['date']));
                $is_new_moon       = (bool) $json['is_new_moon'];
                $is_near_new_moon  = (bool) $json['is_near_new_moon'];
                $next_new_moon     = new \DateTime((string)($json['next_new_moon']['date']));

                // Check if the user has only asked for the moon phase
                if(\count($parameters) === 1 && \is_string($parameters[0]) && $parameters[0] === "phase") {
                    $message->reply("The current moon phase is `{$phase}` ({$title})");
                    return; 
                }

                // Start the message
                if($is_full_moon) {
                    $description = "It is currently a **full moon**. \nThe secret full moon levels are **available** and can be played.";
                } else if($is_near_full_moon) {
                    $description = "We are near a full moon. \nThe secret full moon levels are **visible but can not be played** at this time.";
                } else if($is_new_moon) {
                    $description = "It is currently a **new moon**. \nThe new moon levels are **available** and can be played.";
                } else if($is_near_new_moon) {
                    $description = "We are near a new moon. \nThe secret new moon levels are **visible but can not be played** at this time.";
                } else {
                    $description = "We are currently in between moons. \nNone of the secret levels are visible or playable.";
                }

                // Start next paragraph
                $description .= PHP_EOL . PHP_EOL;

                // Give information for when the next full moon and new moon is
                $description .= "The next full moon is <t:{$next_full_moon->getTimestamp()}:R>.";
                $description .= PHP_EOL;
                $description .= "The next new moon is <t:{$next_new_moon->getTimestamp()}:R>.";

                // Create embed for the alpha patch
                $embed = new Embed($discord, [
                    'title'       => $title,
                    'description' => $description,
                    'timestamp'   => (new \DateTime())->format('Y-m-d H:i'),
                    'color'       => 16777215,
                    'thumbnail'   => ['url' => $image],
                ]);

                // Send the embed as a message to the user
                $message->channel->sendEmbed($embed);
            });
    }
}


















                