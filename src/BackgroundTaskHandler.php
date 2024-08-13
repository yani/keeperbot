<?php

namespace Yani\KeeperBot;

class BackgroundTaskHandler
{

    private array $tasks = [];

    public function addTask(BackgroundTask $task): void
    {
        $this->tasks[] = $task;
    }

    public function runTasks()
    {
        /** @var BackgroundTask $task */
        foreach($this->tasks as $index => $task)
        {
            $result = null;

            $last_run_timestamp = $task->getLastRunTimestamp();

            // Check if this is the first run of this task
            if($last_run_timestamp === null) {
                $result = $task->runTask();
            } else {

                // Check if this task needs to be ran
                $difference = (new \DateTime('now'))->getTimestamp() - $last_run_timestamp->getTimestamp();
                if ($difference >= $task->getInterval()) {
                    $result = $task->runTask();
                }
            }

            // Task is not ready yet
            if($result === null) {
                continue;
            }

            // Task is successful
            if ($result === true) {
                unset($this->tasks[$index]);
                continue;
            }

            // Task was unsuccessful
            if($result === false)
            {
                // Task has reached its timeout
                if($task->getCurrentTry() >= $task->getMaxTries()){
                    $task->runTaskTimeout();
                    unset($this->tasks[$index]);
                }
            }
        }
    }

}