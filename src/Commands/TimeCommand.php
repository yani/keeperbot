<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Psr\Http\Message\ResponseInterface;

use Yani\KeeperBot\Utility;


class TimeCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['time', 'timezone', 'tz'],
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        // Get browser
        $browser = Utility::createBrowserInstance();

        // Get search time
        $search_term = Utility::combineParameters($parameters);

        // Async request
        $browser->get("https://nominatim.openstreetmap.org/search?format=json&limit=1&accept-language=en&q=" . \urlencode($search_term))
            ->then(function (ResponseInterface $response) use ($message, $discord) {

                // Get HTTP response body
                $body = (string)$response->getBody();
                if(empty($body)){
                    $message->reply("Failed to get timezone name");
                    return;
                }

                // Get JSON data
                $json = \json_decode((string) $response->getBody(), true);
                if (empty($json)) {
                    $message->reply('Failed to get timezone name');
                    return;
                }

                // Get display name of location
                $display_name = $json[0]['display_name'];

                // Get coordinates
                $latitude  = $json[0]['lat'];
                $longitude = $json[0]['lon'];
                echo "$display_name => Lat: $latitude, Lon: $longitude\n";
        
                // Get timezone
                $timezone = \p3k\Timezone::timezone_for_location($latitude, $longitude);
                if(!$timezone){
                    $message->reply('Failed to get timezone');
                    return;
                }

                // Get DateTime using timezone
                $date_time = (new \DateTime("now", new \DateTimeZone($timezone)));
                $time_string = $date_time->format("H:i");
                $date_string = $date_time->format("Y-m-d");

                // Send message
                $message->channel->sendMessage(MessageBuilder::new()->setContent(
                    "The time in **{$display_name}** is **`{$time_string}`** ({$date_string})"
                ));

        }, function (\Exception $e) use ($message) {
            $message->reply('Failed to get timezone');
        });
    }
}