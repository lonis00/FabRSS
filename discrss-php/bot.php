<?php

include 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\DiscordCommandClient;

$discord = new DiscordCommandClient([
    'token' => $_ENV['DISCORD_BOT_TOKEN'],
    'prefix' => $_ENV['COMMAND_PREFIX'],
    'discordOptions' => [
        'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
    ],
]);

$discord->registerCommand('ping', function($message){
    return "Pong";
}, [
    'description' => 'Testing command to see if it runs',
]);

$discord->on('init', function(Discord $discord){
    echo "Bot is ready!", PHP_EOL;

    $discord->on('message', function(Message $message, Discord $discord) {
        if($message->author->bot) {
            // Do nothing
            return;
        }
    });
});

$discord->run();