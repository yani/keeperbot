<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\Utility;

use Yani\KeeperBot\CommandInterface;
use Psr\Http\Message\ResponseInterface;

class WorkshopSearchCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'workshop',
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $browser = Utility::createBrowserInstance();

        $search_term = Utility::combineParameters($parameters);

        $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/workshop/search?q=' . \urlencode($search_term))->then(function (ResponseInterface $response) use ($message, $search_term) {

            $body = (string)$response->getBody();

            if(empty($body)){
                $message->reply("Failed to connect to workshop...");
                return;
            }

            $json = \json_decode($body, true);
            if(empty($json) || !is_array($json) || !isset($json['workshop_items']) || !is_array($json['workshop_items'])){
                $message->reply("Invalid server response...");
                return;
            }

            $workshop_items = $json['workshop_items'];
            $workshop_items_count = \count($json['workshop_items']);
            
            if($workshop_items_count <= 0){
                $message->channel->sendMessage(MessageBuilder::new()->setContent("No workshop items found for \"**{$search_term}**\""));
                return;
            }
            
            if($workshop_items_count >= 2){

                // Create list of workshop items
                $titles = [];
                $count = 0;
                foreach($workshop_items as $item){
                    if($count >= 25){ // 25 is absolute max in a discord massage
                        break;
                    }
                    $titles[] = '[**' . $item['name'] . '**](<' . $_ENV['KEEPERFX_URL'] . '/workshop/item/' . $item['id'] . '>)';
                    $count++;
                }

                // Add [+x more]
                if($workshop_items_count > $count){
                    $leftover_count = $workshop_items_count - $count;
                    $break_char = " "; // <- Beware! This string contains a non breaking space
                    $titles[] = "... [**[+{$leftover_count}{$break_char}more]**](<{$_ENV['KEEPERFX_URL']}/workshop/browse?search=" . \urlencode($search_term) . ">)";
                }

                // Create the string and send it
                $titles_string = implode(' - ', $titles);
                $message->channel->sendMessage(MessageBuilder::new()->setContent(
                    "**{$workshop_items_count}** workshop items found for \"**{$search_term}**\": {$titles_string}"
                ));
                return;
            }

            if($workshop_items_count == 1){
                $url = $_ENV['KEEPERFX_URL'] . '/workshop/item/' . $workshop_items[0]['id'];
                $message->reply($url);
                return;
            }

        }, function (\Exception $e) {
            echo 'Error: ' . $e->getMessage() . PHP_EOL;
        });
    }
}