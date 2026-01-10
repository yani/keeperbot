<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

class AlphaPatchCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'alpha',
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $alpha_patch_number = (int) $parameters[0];

        $message->reply("A fix for this can be found in [Alpha {$alpha_patch_number}](https://keeperfx.net/download/alpha/keeperfx-1_3_1_{$alpha_patch_number}_Alpha-patch.7z). See here: https://keeperfx.net/downloads/alpha");
    }
}
