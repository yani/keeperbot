<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;

class InfoCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['keeperbot', 'keeperfxbot'],
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        // Get the message parts
        $split_line = "<--------------------------------- DISCORD-MESSAGE-SPLIT --------------------------------->";
        $message_strings = \explode($split_line, \file_get_contents(__DIR__ . '/../../info-message.txt'));

        // React on the message so other people know its handled
        $message->react("👍");

        // Send DMs
        foreach($message_strings as $string)
        {
            $text = '```' . PHP_EOL;
            $text .= $string . PHP_EOL;
            $text .= '```';

            $message->author->sendMessage(MessageBuilder::new()->setContent($text));
        }
    }
}