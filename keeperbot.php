<?php

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use React\Http\Browser;
use React\Promise\Promise;
use function React\Async\await;

use Symfony\Component\Dotenv\Dotenv;

// Load .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Load commands
$commands     = include __DIR__ . '/commands.php';
$command_info = include __DIR__ . '/commands.info.php';

if(!isset($_ENV['DISCORD_BOT_TOKEN']) || empty($_ENV['DISCORD_BOT_TOKEN'])){
    die('Invalid Discord bot token');
}

// Setup discord bot
$discord = new Discord([
    'token' => $_ENV['DISCORD_BOT_TOKEN'],
    'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT
//      | Intents::MESSAGE_CONTENT, // Note: MESSAGE_CONTENT is privileged, see https://dis.gd/mcfaq
]);

// Handle discord ready event (after connected and ready)
$discord->on('ready', function (Discord $discord) use ($commands, $command_info) {
    echo "Bot is ready!", PHP_EOL;

    // Listen for messages.
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($commands, $command_info) {

        echo "{$message->author->username}: {$message->content}", PHP_EOL;

        // Ignore if message is from a bot
        if ($message->author->bot) {
            return;
        }

        // Make sure this message starts with a exclamation mark
        if(\strpos($message->content, '!') !== 0){
            return;
        }

        // Variables
        $author_tag = '<@' . $message->author->id . '>';

        // Standard text commands
        foreach($commands as $command => $text){
            if($message->content === $command){
                $message->reply($text);
                return;
            }
        }

        // Help command
        if($message->content === '!keeperbot' || $message->content === '!keeperfxbot'){

            // Create command list
            $command_strings = \array_merge(\array_keys($commands),[
                // Dynamic commands
                '!keeperbot',
                '!workshop <search_term>',
                '!slap <person>',
                '!roll',
                '!roll <max>',
                '!randommap',
                '!randomcampaign',
                '!prototype <run_id>',
            ]);
            sort($command_strings);

            // Get longest command char count
            $command_char_count_max = 0;
            foreach($command_strings as $string){
                if(\strlen($string) > $command_char_count_max){
                    $command_char_count_max = \strlen($string);
                }
            }

            // Create text string
            $text = "Hello there Keeper!\n\nCommands:```\n";

            foreach($command_strings as $command_string)
            {
                if(isset($command_info[$command_string])){
                    $text .= \str_pad($command_string, $command_char_count_max +3) . $command_info[$command_string];
                } else {
                    $text .= $command_string;
                }
                $text .= "\n";
            }

            $text .= '```';

            // React on the message so other people know its handled
            $message->react("👍");

            // Send DM
            $message->author->sendMessage(MessageBuilder::new()->setContent($text));
            return;
        }

        // Slap command
        if(\strpos($message->content, '!slap ') === 0){
            if(strlen($message->content) > \strlen('!slap ')){
                $slapped_person = \substr($message->content, \strlen('!slap '));
                $message->channel->sendMessage(MessageBuilder::new()->setContent("**_{$author_tag} slaps {$slapped_person}!_**"));
            }
            return;
        }

        // Roll command
        if(\strpos($message->content, '!roll') === 0){
            if(\strlen($message->content) > \strlen('!roll ')){
                $max_roll_amount = (int) \substr($message->content, \strlen('!roll '));
                if($max_roll_amount <= 0){
                    return;
                }
            } else if($message->content === '!roll') {
                $max_roll_amount = 100;
            } else {
                return;
            }
            $roll = \random_int(1, $max_roll_amount);
            $message->channel->sendMessage(MessageBuilder::new()->setContent("{$author_tag} rolls a **{$roll}** (1-{$max_roll_amount})"));
            return;
        }

        // Workshop search command
        if(\strpos($message->content, '!workshop ') === 0){
            if(strlen($message->content) > \strlen('!workshop ')){
                $search_term = \substr($message->content, \strlen('!workshop '));

                $browser = new Browser(
                    new \React\Socket\Connector(array(
                        'tls' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        ],
                    ))
                );
                $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/workshop/search?q=' . \urlencode($search_term))->then(function (Psr\Http\Message\ResponseInterface $response) use ($message, $search_term) {

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

                }, function (Exception $e) {
                    echo 'Error: ' . $e->getMessage() . PHP_EOL;
                });
            }
            return;
        }

        // Workshop map number search command
        if(\strpos($message->content, '!mapnumber ') === 0){
            if(strlen($message->content) > \strlen('!mapnumber ')){
                $map_number = (int) \substr($message->content, \strlen('!mapnumber '));

                if($map_number < 202 || $map_number > 32767){
                    $message->reply("Mapnumber must be between 202 and 32767");
                    return;
                }

                $browser = new Browser(
                    new \React\Socket\Connector(array(
                        'tls' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        ],
                    ))
                );
                $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/workshop/map_number/' . $map_number)->then(function (Psr\Http\Message\ResponseInterface $response) use ($message, $map_number) {

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

                }, function (Exception $e) {
                    echo 'Error: ' . $e->getMessage() . PHP_EOL;
                });
            }
            return;
        }

        // Random map
        if($message->content === '!randommap'){

            (new Browser(new \React\Socket\Connector(array(
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ],
            ))))
                ->withFollowRedirects(false)
                ->get($_ENV['KEEPERFX_URL'] . '/workshop/random/map')
                ->then(function (Psr\Http\Message\ResponseInterface $response) use ($message) {

                    // Get the URL
                    $url = $response->getHeaderLine("Location");
                    if(empty($url)){
                        $message->reply("Invalid server response...");
                        return;
                    }

                    // Make absolute URL
                    $url = $_ENV['KEEPERFX_URL'] . '' . $url;
                    $url = explode('#', $url)[0];

                    // Send to user
                    $message->reply($url);

                });

            return;
        }

        // Random campaign
        if($message->content === '!randomcampaign'){

            (new Browser(new \React\Socket\Connector(array(
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ],
            ))))
                ->withFollowRedirects(false)
                ->get($_ENV['KEEPERFX_URL'] . '/workshop/random/campaign')
                ->then(function (Psr\Http\Message\ResponseInterface $response) use ($message) {

                    // Get the URL
                    $url = $response->getHeaderLine("Location");
                    if(empty($url)){
                        $message->reply("Invalid server response...");
                        return;
                    }

                    // Make absolute URL
                    $url = $_ENV['KEEPERFX_URL'] . '' . $url;
                    $url = explode('#', $url)[0];

                    // Send to user
                    $message->reply($url);

                });

            return;
        }

        if(\strpos($message->content, '!prototype ') === 0){
            if(strlen($message->content) > \strlen('!prototype ')){
                $param = \substr($message->content, \strlen('!prototype '));
                $run_id = (int)$param;
                if($run_id === 0){
                    $message->reply("Invalid workflow run ID");
                    return;
                }
                
                $promise = new Promise(function () use ($message, $run_id) {

                    // Try every 30 seconds for half an hour
                    // 60 x 30 seconds = 30 minutes
                    $max_tries = 60;
                    $seconds_between_tries = 30; 
                    
                    // We will try and get the prototype
                    $current_try = 0;
                    while($current_try < $max_tries)
                    {
                        // Get the prototype
                        $prototype = getPrototype($run_id);
        
                        // Return if a prototype is found
                        if($prototype){
                            new Promise(function() use ($message, $prototype){
                                $rounded_size = \round($prototype['size_in_bytes'] / 1024 / 1024, 2);
                                $message->reply(
                                    "Prototype [{$prototype['workflow_run_id']}]: {$prototype['workflow_title']} ({$rounded_size}MiB) -> " . 
                                    "{$_ENV['KEEPERFX_URL']}/download/prototype/" . $prototype['filename']
                                );
                            });
                            return;
                        }
    
                        // If the first try fails we'll tell the user that we start waiting for it
                        if($current_try === 0){
                            new Promise(function() use ($message){
                                $message->reply("Waiting for prototype to be ready... _(Do not request a new prototype in the meantime!)_");
                            });
                        }
    
                        // Sleep and go to next try
                        sleep($seconds_between_tries);
                        $current_try++;
                    }

                    // Timed out
                    new Promise(function() use ($message){
                        $message->reply("Prototype search timed out...");
                    });
                });

                return;
            }
        }

        // Latest alpha patch
        if($message->content === '!alpha'){

            (new Browser(new \React\Socket\Connector(array(
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ],
            ))))
                ->get($_ENV['KEEPERFX_URL'] . '/api/v1/alpha/latest')
                ->then(function (Psr\Http\Message\ResponseInterface $response) use ($discord, $message) {

                    // Get body of response
                    $body = (string)$response->getBody();
                    if(empty($body)){
                        $message->reply("Failed to connect to website API...");
                        return;
                    }

                    // Decode JSON
                    $json = \json_decode($body, true);
                    if(empty($json) || !is_array($json) || !isset($json['alpha_build']) || !is_array($json['alpha_build'])){
                        $message->reply("Invalid server response...");
                        return;
                    }

                    // Handle description (add ellipsis and markdown)
                    $description = $json['alpha_build']['workflow_title'];
                    $description = \preg_replace('/\s*(\(.*?\…)/', '…', $description);
                    $description = \preg_replace('/\(\#(\d{1,6})\)/', '([#$1](https://github.com/dkfans/keeperfx/issues/$1))', $description);

                    // Create embed for the alpha patch
                    $embed = new Embed($discord, [
                        'title'       => $json['alpha_build']['name'],
                        'description' => $description,
                        'url'         => $json['alpha_build']['download_url'],
                        'timestamp'   => (new \DateTime($json['alpha_build']['timestamp']))->format('Y-m-d H:i'),
                        'footer'      => ['text' => ((string) \round($json['alpha_build']['size_in_bytes'] / 1024 / 1024, 2)) . 'MiB'],
                        'color'       => 11797236, // #b402f4
                    ]);

                    // Send the embed as a message to the user
                    $message->channel->sendEmbed($embed);

                });

            return;
        }

        // Latest stable release
        if($message->content === '!stable'){

            (new Browser(new \React\Socket\Connector(array(
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ],
            ))))
                ->get($_ENV['KEEPERFX_URL'] . '/api/v1/stable/latest')
                ->then(function (Psr\Http\Message\ResponseInterface $response) use ($discord, $message) {

                    // Get body of response
                    $body = (string)$response->getBody();
                    if(empty($body)){
                        $message->reply("Failed to connect to website API...");
                        return;
                    }

                    // Decode JSON
                    $json = \json_decode($body, true);
                    if(empty($json) || !is_array($json) || !isset($json['release']) || !is_array($json['release'])){
                        $message->reply("Invalid server response...");
                        return;
                    }

                    // Create embed for the stable release
                    $embed = new Embed($discord, [
                        'title'       => $json['release']['name'],
                        'description' => 'Full stable release for KeeperFX ' . $json['release']['tag'],
                        'url'         => $json['release']['download_url'],
                        'timestamp'   => (new \DateTime($json['release']['timestamp']))->format('Y-m-d H:i'),
                        'footer'      => ['text' => ((string) \round($json['release']['size_in_bytes'] / 1024 / 1024, 2)) . 'MiB'],
                        'color'       => 455682, // #06f402
                        'thumbnail'   => ['url' => $_ENV['KEEPERFX_URL'] . '/img/download.png'],
                    ]);

                    // Send the embed as a message to the user
                    $message->channel->sendEmbed($embed);

                });

            return;
        }

        // Make sure this message was a command
        // People can write stuff like '!!!!!' which is not a command
        // We check at the end because some custom commands might not start with '!'
        if(\preg_match('/\!([a-zA-Z])/', $message->content) !== 1){
            return;
        }

        // React with a raised eyebrow emoji when the command is not understood
        $message->react("🤨");

    });
});

function getPrototype(int $run_id): array|false
{
    $browser = new Browser(new \React\Socket\Connector(array(
        'tls' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ],
    )));
    $promise = $browser->get($_ENV['KEEPERFX_URL'] . '/api/v1/prototype/run/' . $run_id);

    $response = await($promise);

    // Handle non-200 response
    if ($response->getStatusCode() !== 200) {
        return false;
    }

    // get the body
    $body = (string)$response->getBody();
    if(empty($body)){
        return false;
    }

    $json = \json_decode($body, true);
    if(empty($json) || !is_array($json) || !isset($json['prototype']) || !is_array($json['prototype'])){
        return false;
    }

    return $json['prototype'];
}

// Start discord bot
$discord->run();