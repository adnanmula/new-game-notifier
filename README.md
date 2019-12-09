# Steam new game notifier
Do you share your steam library through family sharing and want your friends to know the new games you get?

## Set up
Build and up the project
```
make build
make up
```
run the init command to create the database

```
docker-compose exec php sh -c "php bin/console dms:init"
```
then run the check command once to import your library (the first time is recommended to disable telegram notifications with -t false)
```
docker-compose exec php sh -c "php bin/console dms:game-check -t false"
```
then set up the check command in crontab
```bash
0 * * * * docker-compose exec php sh -c "php bin/console dms:game-check"
```