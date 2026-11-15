<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;

class SurrenderCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['gg', 'surrender'],
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        // The victor
        $victor = null;

        // Check for a referenced message
        $referenced_message = $message->referenced_message;
        if ($referenced_message !== null) {
            $victor = $referenced_message->author;
        }

        // Send message
        if($victor) {
            $message->channel->sendMessage(MessageBuilder::new()->setContent("{$message->author} surrenders to {$victor}"));
        } else {
            $message->channel->sendMessage(MessageBuilder::new()->setContent("{$message->author} surrenders."));
        }
    }
}