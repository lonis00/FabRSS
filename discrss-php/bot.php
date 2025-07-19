<?php

include 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;

$discord = new Discord([
    'token' => $_ENV['DISCORD_BOT_TOKEN'],
    'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
]);

$discord->on('init', function(Discord $discord){
    echo "Bot is ready!", PHP_EOL;

    $discord->on('message', function(Message $message, Discord $discord) {
        if($message->author->bot) {
            // Do nothing
            return;
        }
        
        if($message->content == "!ping") {
            $message->reply('Pong !');
        }
    });
});

$discord->run();