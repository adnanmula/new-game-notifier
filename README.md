# Steam new game notifier
Do you share your steam library through family sharing and want your friends to know the new games you get?

## Set up
Create an .env file and fill your steam and telegram tokens.
```
cp .env.dist .env
```

Build and up the project.
```
make build
make up
```
Run the init command to create the database.

```
docker compose exec php sh -c "php bin/console environment:init"
```
Then run the check command once to import your library (the first time is recommended to not enable telegram notifications)
It may take a while depending of your library size.
```
docker compose exec php sh -c "php bin/console steam:import:games"
```
Then set up the check command in crontab.
```
0 * * * * docker compose exec -T php sh -c "php bin/console steam:import:games -trc"

-t enables telegram notifications
-r enables review score import
-c enables completion time info import from howlongtobeat.com
```

View imported data directly from the database or using the following command (it accepts multiple appids as argument) 

```
docker compose exec php sh -c "php bin/console steam:get:games 105600"
```

Example result:
```
> Terraria (105600)
 - Playtime: 13206m
 - Score: 97% (547965 reviews)
 - Completion time
  - Main: 52h
  - With extras: 93h
  - Avg: 105h
  - Full: 200h
```
