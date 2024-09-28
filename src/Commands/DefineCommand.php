<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

use Psr\Http\Message\ResponseInterface;

class DefineCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['define', 'definition'],
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $parameter_string = Utility::combineParameters($parameters);

        $browser = Utility::createBrowserInstance();
        $browser->get('https://api.dictionaryapi.dev/api/v2/entries/en/' . \urlencode($parameter_string))
            ->then(function (ResponseInterface $response) use ($message, $discord, $parameter_string) {

                if($response->getStatusCode() === 404)
                {
                    $message->reply('No definition found');
                    return;
                }

                // Get body of response
                $body = (string)$response->getBody();
                if (empty($body)) {
                    $message->reply("Failed to connect to website API...");
                    return;
                }

                // Decode JSON
                $json = \json_decode($body, true);
                if (empty($json) || !is_array($json)) {
                    $message->reply("Invalid server response...");
                    return;
                }

                // Check if there is a message to reply
                if(isset($json['message']) && \is_string($json['message']))
                {
                    $message->reply($json['message']);
                    return;
                }

                if(empty($json[0]))
                {
                    $message->reply('Something went wrong...');
                    return;
                }

                $description = '';

                // Check for phonetic
                $phonetic = $json[0]['phonetic'] ?? null;
                if($phonetic){
                    $description .= '`' . $phonetic . '`' . PHP_EOL;
                }

                // Check for meanings
                if(!empty($json[0]['meanings']))
                {
                    foreach($json[0]['meanings'] as $meaning)
                    {
                        $description .= '### ' . $meaning['partOfSpeech'] . PHP_EOL;

                        foreach($meaning['definitions'] as $definition)
                        {
                            $description .= '- ' . $definition['definition'] . \PHP_EOL;
                        }

                        $description . \PHP_EOL;
                    }
                }

                // Create embed
                $embed = new Embed($discord, [
                    'title'       => $json[0]['word'] ?? $parameter_string,
                    'description' => $description,
                    'color'       => 16777215,
                ]);

                // Send the embed as a message to the user
                $message->channel->sendEmbed($embed);
            }, function (\Exception $e) use ($message) {

                if($e->getCode() === 404)
                {
                    $message->reply('No definition found');
                    return;
                }

                $message->reply('Something went wrong...');

            });
    }
}


















                