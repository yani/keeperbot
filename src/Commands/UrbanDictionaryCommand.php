<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

use Psr\Http\Message\ResponseInterface;

class UrbanDictionaryCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['urban', 'ud'],
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $term = Utility::combineParameters($parameters);

        $browser = Utility::createBrowserInstance();
        $browser->get('https://www.urbandictionary.com/define.php?term=' . \urlencode($term))
            ->then(function (ResponseInterface $response) use ($message, $discord, $term) {

                // Get body of response
                $body = (string)$response->getBody();
                if (empty($body)) {
                    $message->reply("Failed to connect to Urban Dictionary...");
                    return;
                }

                $regex = '/<div class\=\"definition .+?\<h[12] .+?flex.+?\>\<.+?\>(.+?)\<\/a\>.+?\<div.+? meaning .+?\>(.+?)\<\/div/s';
                if (\preg_match_all($regex, $body, $matches) === false) {
                    $message->reply("No definition found");
                    return;
                }

                if (!isset($matches[1]) || empty($matches[1])) {
                    $message->reply("No definition found");
                    return;
                }

                $definitions = [];

                $i = 0;
                foreach ($matches[1] as $i => $name) {

                    $definition = $matches[2][$i];

                    if (empty($definition)) {
                        continue;
                    }

                    $definition = \html_entity_decode($definition, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $definition = \strip_tags($definition);
                    $definition = \preg_replace('/\s+/', ' ', $definition);
                    $definition = \trim($definition);

                    $definitions[] = '- ' . $definition;

                    if (++$i >= 5) {
                        break;
                    }
                }

                if (empty($definitions)) {
                    $message->reply("No definition found");
                    return;
                }

                // Send the embed as a message to the user
                $message->channel->sendEmbed(new Embed($discord, [
                    'title'       => $term,
                    'description' => \implode("\n", $definitions),
                    'url'         => 'https://www.urbandictionary.com/define.php?term=' . \urlencode($term),
                ]));
            });
    }
}
