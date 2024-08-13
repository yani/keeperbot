<?php

namespace Yani\KeeperBot;

use Discord\Discord;
use Discord\Parts\Channel\Message;

class BackgroundTask
{
    public function __construct(
        private Discord $discord,
        private Message $message,
        private array $parameters,
        private BackgroundCommandInterface $command,
        private int $max_tries,
        private int $interval, // In seconds (approx)
        private ?\DateTime $last_run_timestamp = null,
        private int $current_try = 0,
    ) {}

        public function runTask(): bool
        {
            $result = $this->command->runBackgroundTask($this->discord, $this->message, $this->parameters);

            $this->last_run_timestamp = new \DateTime('now');
            $this->current_try++;

            return $result;
        }

        public function runTaskTimeout(): void
        {
            $this->command->runBackgroundTaskTimeout($this->discord, $this->message, $this->parameters);
        }

        public function getLastRunTimestamp(): ?\DateTime
        {
            return $this->last_run_timestamp;
        }

        public function getInterval(): int
        {
            return $this->interval;
        }

        public function getMaxTries(): int
        {
            return $this->max_tries;
        }

        public function getCurrentTry(): int
        {
            return $this->current_try;
        }
}