<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

use Psr\Http\Message\ResponseInterface;

class MoonPhaseCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['moon', 'moonphase'],
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $browser = Utility::createBrowserInstance();
        $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/moonphase')
            ->then(function (ResponseInterface $response) use ($message, $discord) {

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

                // Add whether or not full moon levels are available or visible or not
                $description = 'Full Moon levels: ';
                if ($is_full_moon){
                    $description .= '**AVAILABLE**';
                } else if ($is_near_full_moon) {
                    $description .= 'Visible';
                } else {
                    $description .= '_Not available_';
                }
                
                // Add next full moon
                if(!$is_full_moon){
                    $description .= PHP_EOL;
                    $description .= "Next Full Moon: <t:{$next_full_moon->getTimestamp()}:R>";
                }

                // Add whether or not new moon levels are available or visible or not
                $description .= PHP_EOL. PHP_EOL;
                $description .= 'New Moon levels: ';
                if ($is_new_moon){
                    $description .= '**AVAILABLE**';
                } else if ($is_near_new_moon) {
                    $description .= 'Visible';
                } else {
                    $description .= '_Not available_';
                }
                
                // Add next new moon
                if(!$is_new_moon){
                    $description .= PHP_EOL;
                    $description .= "Next New Moon: <t:{$next_new_moon->getTimestamp()}:R>";
                }
                
                // Add phase
                $description .= PHP_EOL . PHP_EOL;
                $description .= "Phase: `{$phase}`";

                echo $image;

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


















                