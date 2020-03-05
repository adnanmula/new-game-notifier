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
docker-compose exec php sh -c "php bin/console n:init"
```
Then run the check command once to import your library (the first time is recommended to disable telegram notifications with -t false)
It may take a while depending of your library size.
```
docker-compose exec php sh -c "php bin/console n:check -t false"
```
Then set up the check command in crontab.
```
0 * * * * docker-compose exec -T php sh -c "php bin/console n:check"
```
