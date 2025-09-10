# FabRSS (PHP)

FabRSS (you can call it Fabrice) is a Discord bot that can save RSS feeds and periodically publish them in text, media or forum channels. This version is coded in PHP but you can code in whatever you want.

## INSTALL

### 0. First and foremost 
You will need :
- [php-cli (at least 8.0)](https://www.php.net/downloads.php?usage=cli)
- [composer](https://getcomposer.org/download/)
- PHP packages for the [DiscordPHP](https://github.com/discord-php/DiscordPHP) library :
    - [ext-json](https://www.php.net/manual/en/book.json.php)
    - [ext-zlib](https://www.php.net/manual/en/book.zlib.php)
    - [ext-uv](https://github.com/amphp/ext-uv)
    - [ext-mbstring](https://www.php.net/manual/en/book.mbstring.php)
    
### 1. Clone the repository

Be carefull to get the **`php` branch**:

```bash
#bash
git clone https://github.com/lonis00/FabRSS.git -b php
```

### 2. Dependencies
```bash
#bash
cd discrss-php
composer install
```

### 3. Discord bot creation

Go to the [Discord Developer Portal](https://discord.com/developers/applications), click on 'New Application' and give it a cool name.

Go to the 'Bot' menu, click on `Reset Token` button and copy the unreadable text. It's *your* bot pass to exchange with the Discord API. Now paste the token in your `.env` file as the `DISCORD_BOT_TOKEN` value.

Time to add the bot to your Discord server (kwown as "*Guild*"). On the **OAuth2** menu, scroll down to the **OAuth2 URL Generator** section, check the **`bot`** scope, scroll down again and check these cases:

- View Channels
- Send Messages
- Embed Links
- Use Slash Commands

Select the `Guild Install` integration type, copy the generated URL and paste it in a new tab on your browser. Select the server where you want to add your bot. That's all !

## Run it

Everything is perfectly installed so let's try !

Go to your `discrss-php` folder and run `php bot.php`

**Tada !** Your bot is running !

## Commands

### /addfeed

The command `/addfeed` takes two parameters. First, the `url` of the feed you want to subscribe and second, the `channel` where you want the notification for new feed posts.

### /removefeed

This command takes only one parameter : the `url` of the feed you want to unsubscribe.

> Note : This will not remove the Discord messages already posted.

### /ping

This is to test for any issues with your installation.
