<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Psr\Http\Message\ResponseInterface;

use Yani\KeeperBot\Utility;

class WorkshopMapNumberCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'mapnumber',
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $map_number = (int) $parameters[0];

        if($map_number < 202 || $map_number > 32767){
            $message->reply("Mapnumber must be between 202 and 32767");
            return;
        }

        $browser = Utility::createBrowserInstance();

        $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/workshop/map_number/' . $map_number)->then(function (ResponseInterface $response) use ($message, $map_number) {

            $body = (string)$response->getBody();

            if(empty($body)){
                $message->reply("Failed to connect to workshop API...");
                return;
            }

            $json = \json_decode($body, true);
            if(empty($json) || !is_array($json) || !isset($json['available']) || !is_bool($json['available'])){
                $message->reply("Invalid server response...");
                return;
            }

            if($json['available'] === true){
                $message->reply(":white_check_mark: Map number **{$map_number}** is available!");
            } else {
                $message->reply(":no_entry_sign: Map number **{$map_number}** is NOT available!");
            }

            return;

        }, function (\Exception $e) {
            echo 'Error: ' . $e->getMessage() . PHP_EOL;
        });
    }
}