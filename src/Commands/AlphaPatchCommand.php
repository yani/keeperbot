<?php

namespace Yani\KeeperBot\Commands;

use function React\Async\await;

use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\Utility;
use Yani\KeeperBot\BackgroundTask;
use Yani\KeeperBot\BackgroundCommand;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\BackgroundCommandInterface;

class AlphaPatchCommand extends BackgroundCommand implements CommandInterface, BackgroundCommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command' => 'alpha'
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $alpha_patch = $this->getAlphaPatch($parameters);

        if ($alpha_patch) {
            $message->channel->sendEmbed($this->createEmbed($discord, $alpha_patch));
            return;
        }

        if (count($parameters) == 0) {
            $message->channel->sendMessage(MessageBuilder::new()->setContent(
                "Unable to find alpha patch..."
            ));
            return;
        }

        $build_id = (string)$parameters[0];

        // Tell the user the alpha build will come
        $message->channel->sendMessage(MessageBuilder::new()->setContent(
            "Waiting for alpha build **{$build_id}** to be ready..."
        ));

        // Add the background task
        $this->addBackgroundTask(new BackgroundTask(
            $discord,
            $message,
            [$build_id],
            $this,
            60,
            30,
            new \DateTime('now')
        ));
    }

    public function runBackgroundTask(Discord $discord, Message $message, array $parameters = []): bool
    {
        $alpha_patch = $this->getAlphaPatch($parameters);

        if ($alpha_patch) {
            $message->channel->sendEmbed($this->createEmbed($discord, $alpha_patch));
            return true;
        }

        return false;
    }

    public function runBackgroundTaskTimeout(Discord $discord, Message $message, array $parameters = []): void
    {
        $build_id = (string)$parameters[0];
        $message->channel->sendMessage(MessageBuilder::new()->setContent(
            "Alpha Build [{$build_id}]: Timed out..."
        ));
    }

    private function getApiUrlFromParameters(array $parameters = []): string
    {
        return $_ENV['KEEPERFX_URL'] . '/api/v1/release/alpha/' .
            ((count($parameters) === 0) ? 'latest' : ('build/' . (string)$parameters[0]));
    }

    private function createEmbed(Discord $discord, array $alpha_patch): Embed
    {
        // Handle description (add ellipsis and markdown)
        $description = $alpha_patch['workflow_title'];
        $description = \preg_replace('/\s*(\(.*?\…)/', '…', $description);
        $description = \preg_replace('/\(\#(\d{1,6})\)/', '([#$1](https://github.com/dkfans/keeperfx/issues/$1))', $description);

        // Create embed for the alpha patch
        return new Embed($discord, [
            'title'       => $alpha_patch['name'],
            'description' => $description,
            'url'         => $alpha_patch['download_url'],
            'timestamp'   => (new \DateTime($alpha_patch['timestamp']))->format('Y-m-d H:i'),
            'footer'      => ['text' => ((string) \round($alpha_patch['size_in_bytes'] / 1024 / 1024, 2)) . 'MiB'],
            'color'       => 11797236, // #b402f4
        ]);
    }

    private function getAlphaPatch(array $parameters = []): array|false
    {
        try {

            $browser = Utility::createBrowserInstance();
            $promise = $browser->get($this->getApiUrlFromParameters($parameters));

            $response = await($promise);

            // Make sure response is not NULL
            if (\is_null($response)) {
                return false;
            }

            // Handle non-200 response
            if ($response->getStatusCode() !== 200) {
                return false;
            }

            // Get the body
            $body = (string)$response->getBody();
            if (empty($body)) {
                return false;
            }

            $json = \json_decode($body, true);
            if (empty($json) || !is_array($json) || !isset($json['alpha_build'])) {
                return false;
            }
        } catch (\Exception $ex) {
            echo $ex;
            return false;
        }

        return $json['alpha_build'];
    }
}
