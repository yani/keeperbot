<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

use Psr\Http\Message\ResponseInterface;

class WikipediaCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['wikipedia', 'wp'],
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        // Combine parameters into a full string
        $parameter_string = Utility::combineParameters($parameters);

        // Create browser
        $browser = Utility::createBrowserInstance();

        // Search the article
        $browser->get('https://en.wikipedia.org/w/rest.php/v1/search/title?limit=1&q=' . \urlencode($parameter_string))
            ->then(function (ResponseInterface $response) use ($browser, $message, $discord, $parameter_string) {

                // Make sure this the page returned something
                if($response->getStatusCode() === 404){
                    $message->reply('No Wikipedia article found');
                    return;
                }

                // Get body of response
                $body = (string)$response->getBody();
                if (empty($body)) {
                    $message->reply("Failed to connect to Wikipedia API...");
                    return;
                }

                // Decode JSON
                $json = \json_decode($body, true);
                if (empty($json) || !is_array($json)) {
                    $message->reply("Invalid server response...");
                    return;
                }

                // Check if an article is found
                if(!isset($json['pages']) || empty($json['pages'])) {
                    $message->reply('No Wikipedia article found');
                    return;
                }

                // Get page if for article
                $page_id = $json['pages'][0]['key'];

                // Now grab the article data
                $browser->get('https://en.wikipedia.org/api/rest_v1/page/summary/' . $page_id)
                    ->then(function (ResponseInterface $response) use ($message, $discord, $parameter_string) {

                        // Make sure this the page returned something
                        if($response->getStatusCode() === 404){
                            $message->reply('Wikipedia article not found');
                            return;
                        }

                        // Get body of response
                        $body = (string)$response->getBody();
                        if (empty($body)) {
                            $message->reply("Failed to connect to Wikipedia API...");
                            return;
                        }
        
                        // Decode JSON
                        $json = \json_decode($body, true);
                        if (empty($json) || !is_array($json)) {
                            $message->reply("Invalid server response...");
                            return;
                        }

                        // Create embed data
                        $embed_data = [
                            'title'       => $json['title'],
                            'description' => $json['extract'] . "\n\n" . $json['content_urls']['desktop']['page'],
                            'color'       => 16777215,
                        ];

                        // Add thumbnail
                        if(!empty($json['thumbnail'])){
                            $embed_data['thumbnail'] = ['url' => $json['thumbnail']['source']];
                        }

                        // Create embed
                        $embed = new Embed($discord, $embed_data);

                        // Send the embed as a message to the user
                        $message->channel->sendEmbed($embed);

                }, function (\Exception $e) use ($message) {

                    if($e->getCode() === 404)
                    {
                        $message->reply('No Wikipedia article found');
                        return;
                    }
    
                    $message->reply('Something went wrong...');
    
                });

            }, function (\Exception $e) use ($message) {

                if($e->getCode() === 404)
                {
                    $message->reply('No Wikipedia article found');
                    return;
                }

                $message->reply('Something went wrong...');

            });
    }
}


















                