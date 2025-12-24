<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Psr\Http\Message\ResponseInterface;

use Yani\KeeperBot\Utility;

class WeatherCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['weather'],
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? null;
        if (!$apiKey) {
            $message->reply("OpenWeather API key not set. Please add it to your .env file.");
            return;
        }

        $cityQuery = Utility::combineParameters($parameters);
        if (!$cityQuery) {
            $message->reply("Please provide a city name. Usage: !weather <city>");
            return;
        }

        $browser = Utility::createBrowserInstance();
        $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cityQuery) . "&appid={$apiKey}&units=metric";

        $browser->get($url)->then(function (ResponseInterface $response) use ($message) {
            $json = json_decode((string)$response->getBody(), true);

            if (!$json || $json['cod'] != 200) {
                $message->reply("Could not fetch weather for that location.");
                return;
            }

            $city        = $json['name'] ?? 'Unknown';
            $country     = $json['sys']['country'] ?? 'Unknown';
            $temp        = $json['main']['temp'] ?? 'N/A';
            $feelsLike   = $json['main']['feels_like'] ?? 'N/A';
            $description = $json['weather'][0]['description'] ?? 'N/A';
            $humidity    = $json['main']['humidity'] ?? 'N/A';
            $windSpeed   = $json['wind']['speed'] ?? 'N/A';
            $timezoneOffset = $json['timezone'] ?? 0;
            $sunrise = isset($json['sys']['sunrise']) 
                ? gmdate("H:i", $json['sys']['sunrise'] + $timezoneOffset) 
                : 'N/A';
            $sunset = isset($json['sys']['sunset']) 
                ? gmdate("H:i", $json['sys']['sunset'] + $timezoneOffset) 
                : 'N/A';

            $messageText = "**Weather in {$city}, {$country}:**\n" .
                           "🌡️ Temp: {$temp}°C (Feels like {$feelsLike}°C)\n" .
                           "☁️ Condition: {$description}\n" .
                           "💧 Humidity: {$humidity}%\n" .
                           "💨 Wind: {$windSpeed} m/s\n" .
                           "🌅 Sunrise: {$sunrise}\n" .
                           "🌇 Sunset: {$sunset}";

            $message->channel->sendMessage(MessageBuilder::new()->setContent($messageText));

        }, function (\Exception $e) use ($message) {
            $message->reply("Failed to fetch weather data.");
        });
    }
}
