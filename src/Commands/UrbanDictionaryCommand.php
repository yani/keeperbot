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
    private const MAX_RESULTS = 5;

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

                // Try and get the definitions on the page
                $regex = '/<div class\=\"definition .+?\<h[12] .+?flex.+?\>\<.+?\>(.+?)\<\/a\>.+?\<div.+? meaning .+?\>(.+?)\<\/div/s';
                if (\preg_match_all($regex, $body, $matches) === false) {
                    $message->reply("No definition found");
                    return;
                }

                // Make sure the original word is found
                if (!isset($matches[1]) || empty($matches[1])) {
                    $message->reply("No definition found");
                    return;
                }

                $definitions = [];
                $definitions_discarded = 0;

                // Loop trough all found definitions and handle them
                // We'll make sure they're valid and we'll filter them for better display in Discord
                foreach ($matches[1] as $i => $name) {

                    $definition = $matches[2][$i];

                    // Make sure definition is valid
                    if (empty($definition) || strlen($definition) > 1000) {
                        $definitions_discarded++;
                        continue;
                    }

                    // Decode weird entities and convert them all to UTF8
                    $definition = \html_entity_decode($definition, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    // Strip HTML tags
                    $definition = \strip_tags($definition);

                    // Strip excessive whitespace
                    $definition = \preg_replace('/[ \t]+/', ' ', $definition);

                    // Trim any preceding or appending whitespace (and newlines)
                    $definition = \trim($definition);

                    // Make sure definition is not empty after filtering
                    if (empty($definition)) {
                        $definitions_discarded++;
                        continue;
                    }

                    // Replace newlines
                    // We do this after the previous check because we add an extra character here
                    $definition = \preg_replace('/\s*?\R+\s*?/', ' - ', $definition);

                    // Add markdown list character
                    $definitions[] = '- ' . $definition;

                    // Break if this is the last allowed result
                    if (\count($definitions) >= self::MAX_RESULTS) {
                        break;
                    }
                }

                if (empty($definitions)) {
                    $message->reply("No Urban Dictionary definition found for {$term}");
                    return;
                }

                // Check if there were more definitions on the page
                if (\count($definitions) + $definitions_discarded < \count($matches[1])) {
                    $definitions[] = '- ...';
                    // $hidden_definition_count = \count($matches[1]) - (\count($definitions) + $definitions_discarded);
                    // $definitions[] = "-# ... _{$hidden_definition_count} more definition(s) omitted_";
                }

                // Send the embed as a message to the user
                $message->channel->sendEmbed(new Embed($discord, [
                    'title'       => $term,
                    'description' => \implode("\n", $definitions),
                    'url'         => 'https://www.urbandictionary.com/define.php?term=' . \urlencode($term),
                    'footer'      => ['text' => 'Urban Dictionary'],
                ]));
            });
    }
}
