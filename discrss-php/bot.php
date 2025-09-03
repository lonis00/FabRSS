<?php

include 'vendor/autoload.php';
include './db_funcs.php';

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

$discord->registerCommand('addFeed', function($message, $params) {
    global $discord;
    $response = "";
    $errors = array();

    $feed_url = $params[0];
    $chanel_url = $params[1];

    if(!filter_var($feed_url, FILTER_VALIDATE_URL)){
        $errors[] = "Le premier paramètre n'est pas un lien correct.";
    }
    if(!str_starts_with($chanel_url, 'https://discord.com/channels/')){
        $errors[] = "Le lien du canal n'est pas correct.";
    }
    $guild_chanel_ids = trim(str_replace("https://discord.com/channels/", '', $chanel_url), " \t\n\r\0\x0B /");
    $splited_ids = explode('/', $guild_chanel_ids);
    $guild_id = $splited_ids[0];
    $chanel_id = $splited_ids[1];
    $chanel = $discord->getChannel($chanel_id);
    $response_status = add_feed($feed_url, $guild_id, $chanel_id);
    if($response_status == 404)
        $response = "Le fichier XML n'a pas été trouvé, verifiez votre URL";
    elseif($response_status == 201)
        $response = "Flux enregistré ✅, les nouveaux postes seront postés dans le canal <#".$chanel_id.">";
    elseif($response_status == 202)
        $response = "Flux déjà enregistré, aucun changement.";
    return $response;
});

$discord->registerCommand('removeFeed', function($message, $params) {
    global $discord;
    $response = "";
    $errors = array();

    $feed_url = $params[0];

    if(!filter_var($feed_url, FILTER_VALIDATE_URL)){
        $errors[] = "Le premier paramètre n'est pas un lien correct.";
    }
    $response_status = remove_feed($feed_url);
    if($response_status == 404)
        $response = "Le fichier XML ou le flux n'ont pas été trouvé, verifiez votre URL.";
    elseif($response_status == 200)
        $response = "Flux supprimé 🗑️.";
    return $response;
});

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