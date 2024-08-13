<?php

include __DIR__.'/vendor/autoload.php';

use Yani\KeeperBot\KeeperBot;

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Discord\Parts\Channel\Message;

use Symfony\Component\Dotenv\Dotenv;

// Load .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

if(!isset($_ENV['DISCORD_BOT_TOKEN']) || empty($_ENV['DISCORD_BOT_TOKEN'])){
    die('Invalid Discord bot token');
}

// Setup discord bot
$discord = new Discord([
    'token' => $_ENV['DISCORD_BOT_TOKEN'],
    'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT
//      | Intents::MESSAGE_CONTENT, // Note: MESSAGE_CONTENT is privileged, see https://dis.gd/mcfaq
]);

// Create our own bot instance
$keeperbot = new KeeperBot();

// Handle discord ready event (after connected and ready)
$discord->on('ready', function (Discord $discord) use ($keeperbot) {
    echo "Bot is ready!", PHP_EOL;

    // Handle messages
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($keeperbot) {

        // Show message in log
        echo "{$message->author->username}: {$message->content}", PHP_EOL;

        // Handle the message with our custom bot
        $keeperbot->handleMessage($discord, $message);
    });
});

// Get list of events that trigger the BackgroundTasks
$reflectionClass = new ReflectionClass(Event::class);
$events = $reflectionClass->getConstants();
$events[] = 'heartbeat-ack';

// Run background task handler
foreach($events as $event) {
    $discord->on($event, function ($data) use ($keeperbot) {
        $keeperbot->getBackgroundTaskHandler()->runTasks();
    });
}

// Start discord bot
$discord->run();