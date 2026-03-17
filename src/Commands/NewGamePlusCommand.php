<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

use Psr\Http\Message\ResponseInterface;

class NewGamePlusCommand implements CommandInterface
{
    private array $help_array = [
        [
            'level'    => ['4', 'flowerhat'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Flowerhat#New_Game_Plus',
        ],
        [
            'level'    => ['6', 'snuggledell'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Snuggledell#New_Game_Plus',
        ],
        [
            'level'    => ['7', 'wishvale'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Wishvale#New_Game_Plus',
        ],
        [
            'level'    => ['8', 'tickle'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Tickle#New_Game_Plus',
        ],
        [
            'level'    => ['9', 'moonbrush wood', 'moonbrush'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Moonbrush_Wood#New_Game_Plus',
        ],
        [
            'level'    => ['10', 'nevergrim'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Nevergrim#New_Game_Plus',
        ],
        [
            'level'    => ['11', 'hearth', 'heart'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Hearth#New_Game_Plus',
        ],
        [
            'level'    => ['12', 'elves dance', 'elf\'s dance'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Elf\'s_Dance#New_Game_Plus',
        ],
        [
            'level'    => ['13', 'buffy oak', 'buffy'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Buffy_Oak#New_Game_Plus',
        ],
        [
            'level'    => ['14', 'sleepiburgh', 'sleepyburgh', 'sleepiburg', 'sleepyburg'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Sleepiburgh#New_Game_Plus',
        ],
        [
            'level'    => ['15', 'woodly rhyme', 'woodly', 'woodly rhime'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Woodly_Rhyme#New_Game_Plus',
        ],
        [
            'level'    => ['16', 'tulipscent', 'tullipscent'],
            'wiki_url' => 'https://dungeonkeeper.fandom.com/wiki/Tulipscent#New_Game_Plus',
        ],
    ];

    public function getCommandConfig(): array
    {
        return [
            'command' => ['ng+', 'ng', 'ngp', 'newgameplus'],
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        if (\count($parameters) === 0) {
            $message->reply("**New Game Plus+** is a KeeperFX campaign which modifies the original campaign to be harder for veteran players while adding small little twists and puzzles.");
            return;
        }

        // Get the level
        $level = \strtolower(Utility::combineParameters($parameters));

        // Find the help
        foreach ($this->help_array as $help) {
            if (\in_array($level, $help['level'])) {

                $wiki_url = $help['wiki_url'];
                $url_parts = explode('#', $wiki_url);

                if (\count($url_parts) !== 2) {
                    $message->reply("Invalid help URL");
                    return;
                }

                $url = $url_parts[0];
                $hashbang = $url_parts[1];

                $browser = Utility::createBrowserInstance();
                $browser->get($url)
                    ->then(function (ResponseInterface $response) use ($message, $discord, $hashbang) {

                        // Get body of response
                        $body = (string)$response->getBody();
                        if (empty($body)) {
                            $message->reply("Failed to connect to Fandom wiki...");
                            return;
                        }

                        // Handle non-200 response
                        if ($response->getStatusCode() !== 200) {
                            $message->reply("Failed to connect to Fandom wiki...");
                            return;
                        }

                        // Get the page title
                        preg_match('~\"mw\-page\-title\-main\"\>(.+?)\<\/span\>~', $body, $match);
                        if (empty($match[1])) {
                            $message->reply("Unable to find wiki page title...");
                            return;
                        }
                        $title = $match[1];

                        // Grab the new game plus information from the wiki page
                        preg_match('~id="New_Game_Plus".*?</h[23]>(.*?)(?=<h[23]\b|$)~is', $body, $section);

                        // Get paragraphs
                        $paragraphs = [];
                        if (!empty($section[1])) {
                            preg_match_all('~<p\b[^>]*>(.*?)</p>~is', $section[1], $matches);
                            $paragraphs = $matches[1];
                        }

                        // Make sure we found paragraphs
                        if (count($paragraphs) === 0) {
                            $message->reply("Unable to find New Game Plus information for this level...");
                            return;
                        }

                        // Create output
                        $output = "**New Game Plus+** information for **{$title}**:";
                        foreach ($paragraphs as $i => $paragraph) {

                            $text = \html_entity_decode($paragraph, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $text = \strip_tags($text);
                            $text = \preg_replace('/\s+/', ' ', $text);
                            $text = \trim($text);

                            $output .= "\n> " . $text;

                            if ($i < (\count($paragraphs) - 1)) {
                                $output .= "\n> ";
                            }
                        }

                        $message->channel->sendMessage(MessageBuilder::new()->setContent($output));
                        return;
                    });

                return;
            }
        }

        // Unable to find the level in the level array
        $message->reply("I have no information for this level.");
        return;
    }
}
