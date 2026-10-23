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
        $browser->get('https://freedictionaryapi.com/api/v1/entries/en/' . \urlencode($parameter_string))
            ->then(function (ResponseInterface $response) use ($message, $discord, $parameter_string) {

                if ($response->getStatusCode() === 404) {
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

                // Check if API returned an error message
                if (isset($json['message']) && \is_string($json['message'])) {
                    $message->reply($json['message']);
                    return;
                }

                if (empty($json['entries'])) {
                    $message->reply('No definition found');
                    return;
                }

                $description = '';

                // Try to get the first available IPA pronunciation text
                $phonetic = null;
                foreach ($json['entries'] as $entry) {
                    if (!empty($entry['pronunciations'])) {
                        foreach ($entry['pronunciations'] as $pron) {
                            if (!empty($pron['text'])) {
                                $phonetic = $pron['text'];
                                break 2;
                            }
                        }
                    }
                }

                if ($phonetic) {
                    $description .= '`' . $phonetic . '`' . \PHP_EOL . \PHP_EOL;
                }

                // Parse entries, parts of speech, and definitions (senses)
                foreach ($json['entries'] as $entry) {
                    if (empty($entry['senses'])) {
                        continue;
                    }

                    $partOfSpeech = $entry['partOfSpeech'] ?? 'definition';
                    $description .= '### ' . $partOfSpeech . \PHP_EOL;

                    foreach ($entry['senses'] as $sense) {
                        if (!empty($sense['definition'])) {
                            $description .= '- ' . $sense['definition'] . \PHP_EOL;
                        }
                    }

                    $description .= \PHP_EOL;
                }

                // Create embed
                $embed = new Embed($discord, [
                    'title'       => $json['word'] ?? $parameter_string,
                    'description' => \mb_strimwidth($description, 0, 4096, '...'), // Prevent Discord embed limits
                    'color'       => 16777215,
                ]);

                // Send the embed as a message to the user
                $message->channel->sendEmbed($embed);
            }, function (\Exception $e) use ($message) {

                if ($e->getCode() === 404) {
                    $message->reply('No definition found');
                    return;
                }

                echo "Error: " . $e->getMessage() . \PHP_EOL;

                $message->reply('Something went wrong...');

            });
    }
}