<?php

namespace Yani\KeeperBot;

use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

class KeeperBot
{
    private const COMMAND_CHAR = '!';

    private array $simple_commands = [];

    private array $commands = [];

    private BackgroundTaskHandler $task_handler;

    public function __construct()
    {
        $this->task_handler = new BackgroundTaskHandler();

        $this->loadSimpleCommands();
        $this->loadCommands();
    }

    public function handleMessage(Discord $discord, Message $message)
    {
        // Ignore if message is from a bot
        if ($message->author->bot) {
            return;
        }

        // Make sure this message is long enough to be a command
        if (\strlen($message->content) < 2) {
            return;
        }

        // Funny reply to this comment
        if ($message->content == "bad bot") {
            $message->reply(
                MessageBuilder::new()->addFile(__DIR__ . '/../files/robot-attack.gif')
            );
            return;
        }

        if ($message->content === "good bot") {
            $message->reply("Thanks");
            return;
        }

        // Make sure command starts with the command prefix
        if (self::COMMAND_CHAR !== \substr($message->content, 0, 1)) {
            return;
        }

        $full_command = \trim($message->content);
        $full_command = \substr($full_command, 1);

        $command_parts = $this->parseCommand($full_command);
        $command_name = \array_shift($command_parts);

        // Make sure a command is given
        if ($command_name === null) {
            return;
        }

        // Simple text commands
        foreach ($this->simple_commands as $simple_command_name => $text) {
            if ($full_command === $simple_command_name) {
                $message->reply($text);
                return;
            }
        }

        // Dynamic commands
        /** @var CommandInterface $command */
        foreach ($this->commands as $command) {
            $command_found = false;

            $config = $command->getCommandConfig();

            if (\is_string($config['command']) && $config['command'] === $command_name) {
                $command_found = true;
            }

            if (\is_array($config['command']) && \in_array($command_name, $config['command'])) {
                $command_found = true;
            }

            if ($command_found === true) {
                if (
                    !isset($config['has_parameters']) ||
                    ($config['has_parameters'] === false && \count($command_parts) == 0) ||
                    ($config['has_parameters'] === true && \count($command_parts) >= 1)
                ) {
                    $command->handleCommand($discord, $message, $command_parts);
                    return;
                }
            }
        }

        // Make sure this message was a command
        // People can write stuff like '!!!!!' which is not a command
        // We check at the end because some custom commands might not start with '!'
        if (\preg_match('/\!([a-zA-Z0-9])/', $message->content) !== 1) {
            return;
        }

        // React with a raised eyebrow emoji when the command is not understood
        Utility::reactRaisedEyebrowFaceToMessage($message);
    }

    private function loadSimpleCommands()
    {
        $this->simple_commands = include __DIR__ . '/../simple-commands.list.php';
    }

    private function loadCommands()
    {
        $command_filepaths = \glob(__DIR__ . '/Commands/*Command.php');

        foreach ($command_filepaths as $command_filepath) {
            // Get variables
            $command_filename        = \basename($command_filepath);
            $command_class_name      = \substr($command_filename, 0, -4);
            $command_full_class_name = '\\Yani\\KeeperBot\\Commands\\' . $command_class_name;

            // Create the command class
            $command = new $command_full_class_name();

            // Check if this task uses the background tasks
            if ($command instanceof BackgroundCommandInterface) {
                $command->setBackgroundTaskHandler($this->task_handler);
            }

            // Add command to list
            $this->commands[] = $command;
        }
    }

    private function parseCommand($command)
    {
        // Use preg_match_all to extract all quoted strings or standalone words
        preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $command, $matches);

        // Remove surrounding quotes from the matches
        return array_map(function ($match) {
            return trim($match, '"');
        }, $matches[0]);
    }

    public function getBackgroundTaskHandler(): BackgroundTaskHandler
    {
        return $this->task_handler;
    }
}
