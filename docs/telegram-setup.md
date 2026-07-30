# Telegram lead notifications — setup

The landing page's lead-capture form sends every submission (name, phone, service,
description and photo) straight to a Telegram chat via a bot. Nothing is stored in a
database — set this up before the form will actually deliver anything.

## 1. Create a bot

1. Open Telegram and start a chat with **@BotFather**.
2. Send `/newbot` and follow the prompts (choose a display name, then a unique
   username ending in `bot`).
3. BotFather replies with a **bot token** that looks like
   `123456789:AAExampleTokenStringHere`. Copy it.

## 2. Decide where leads should land

- **Personal chat**: message the new bot once (any text) so it's allowed to message
  you back.
- **Group**: add the bot to the group as a member. If the group requires admins to
  post, promote the bot or disable that restriction for it.

## 3. Get the chat ID

1. Send any message to the bot (in the DM or the group).
2. In a browser, open:
   ```
   https://api.telegram.org/bot<TOKEN>/getUpdates
   ```
   (replace `<TOKEN>` with your bot token).
3. In the JSON response, find `"chat":{"id": ...}`. For a personal chat this is a
   positive number; for a group it's negative (e.g. `-1001234567890`). Copy it.

## 4. Configure the app

1. Copy the example config:
   ```
   cp config/params-local.php.example config/params-local.php
   ```
2. Edit `config/params-local.php` and fill in the real values:
   ```php
   return [
       'telegramBotToken' => '123456789:AAExampleTokenStringHere',
       'telegramChatId' => '-1001234567890',
   ];
   ```
   This file is gitignored — it will never be committed.

## 5. Test it

Submit the lead form on the running site (with and without a photo attached) and
confirm the message arrives in the configured chat. If nothing arrives, check
`runtime/logs/app.log` for a `TelegramNotifier` warning/error entry.
