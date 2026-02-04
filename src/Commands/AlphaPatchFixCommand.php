<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

class AlphaPatchFixCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'alphafix',
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $alpha_patch_number = (int) $parameters[0];

        // Handle original alpha patch command
        $alpha_patch_command = new AlphaPatchCommand();
        $alpha_patch_command->handleCommand($discord, $message, [$alpha_patch_number]);

        // Reply to the original message
        $referenced_message = $message->referenced_message;
        if ($referenced_message !== null) {
            $referenced_message->reply("A fix for this can be found in Alpha {$alpha_patch_number}.");
        } else {
            $message->reply("A fix for this can be found in Alpha {$alpha_patch_number}.");
        }
    }
}
