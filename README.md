# KeeperBot

A discord bot called **KeeperBot** for the Keeper Klan discord guild.

![image](https://github.com/yani/keeperbot/assets/6956790/39312d24-28f9-4c6e-8935-9c11c93f93f8)

### Public bot

Go to https://discord.com/oauth2/authorize?client_id=1240009518847365130 and add him to your server.

### Private bot

1. Run the following commands:

```
git clone https://github.com/yani/keeperbot
cd keeperbot
composer install --no-dev
```

2. Edit `.env` file and set the `DISCORD_BOT_TOKEN` to the token of your bot

3. Run the following command:

```
php keeper-bot.php
```

### Systemd service

```
nano /etc/systemd/system/keeperbot.service
```

```
[Unit]
Description=KeeperBot (Discord bot)
After=network.target

[Service]
ExecStart=php /home/yani/keeperbot/keeper-bot.php
Restart=always
```

```
service keeperbot start
```

### Docker image

```
docker run -d \
    --name keeperbot \
    --restart unless-stopped \
    -e DISCORD_BOT_TOKEN=XXXXXX \
    yanikore/keeperbot
```

### Docker build

```
docker build -t yanikore/keeperbot .
docker push yanikore/keeperbot
```

### License

MIT

