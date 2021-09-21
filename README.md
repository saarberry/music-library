# Music Library

Music Library is a place for me to store music that I physically own, because I keep forgetting.

## Requirements
- Git
- [Docker](https://www.docker.com/products/docker-desktop)
- A browser

## Getting started
**Step 1** - Navigate your terminal to wherever you keep your projects. Run `git clone git@github.com:saarberry/music-library.git`, which will create a `music-library` directory for you in the current directory. Run `cd music-library` to enter the directory for all the next steps.

**Step 2** - Copy the `docker/env.example` file in the root directory and call it `docker/.env`. It contains the credentials for MySQL, and configuration for nginx. Make sure to update these to your preferences. Note that the nginx host will be the domain where you can visit the app later.

**Step 3** - Build the project through docker with the `docker-compose up -d --build` command. Building is required the first time, but after that you can skip the `--build` flag to bring the containers back up. Note that the `-d` flag stands for daemon, you _can_ skip it, but then all containers will run in your CLI as a process until you ctrl+c to stop it. Can be helpful if you want to see the logs, but you can also view them in the docker dashboard.

**Step 4** - Install the PHP dependencies using `docker-compose run --rm composer install`.

**Step 5** - Install the JS dependencies using `docker-compose run --rm npm install`.

**Step 6** - Copy the `.env.example` file in the root directory and call it `.env`. We don't need most of the settings because this application doesn't do a whole lot for now, but you still need the file to exist. Most settings are already set to defaults that docker expects, but you can change the following if you feel like it:
- `APP_DEBUG` - Set this to false if you don't want to see the cool stack trace if something goes wrong.
- `DEBUGBAR_ENABLED` - If you want to see that nifty debugbar on every page, set this to `true`.
- `DB_*` - The database settings are mostly configured to connect with the docker mysql container, you only have to set the password. Change the other settings if you want to connect to a local database or something.

**Step 7** - Generate an application key using `docker-compose run --rm artisan key:generate`.

**Step 8** - Create and fill your database with `docker-compose run --rm artisan migrate --seed`.

**Step 9** - Go to http://music.lcl/ in your browser to see this cool website!

## Adding albums
Of course the whole point of this app is to have albums on display, which are then searchable to see if you already own them or not. To fill the library, make sure you fill out the LastFM API key and secret values in the `.env`. When those are set, you can run the artisan command `artisan lastfm:add-album "Some album title"` to search Last FM for an album. From the result set you can choose which of the albums you want to add, and the app will download the cover art and add the entry to the database.

It's a little tedious for now, but it works!

## Developing JS / CSS
**Step 0** - Make sure you've completed step 5 of the getting started section.

**Step 1** - Run `docker-compose run --rm npm run watch` to monitor files that should be built, when you change them they will automatically create new CSS and JS files in the `public` directory.

**Step 2** - You can find all the SCSS, JS and views in the `resources` folder. Modify whatever you like to your hearts' content.

## Testing
**Step 1** - Tests are run through the default artisan test suite, you can run it via `docker-compose run --rm artisan test`

## Troubleshooting
### Windows
Dnsmasq doesn't work on Windows because Windows. If you want to use a cool URL rather than `127.0.0.1`, edit your hosts file.

### Laravel Valet
If you usually run your projects through laravel valet, then the docker containers will conflict with the `nginx` and `dnsmasq` services. To fix the nginx conflict, simply stop the valet services with `valet stop`.

Valet sadly doesn't run dnsmasq as a process, but relies on homebrew to keep that running. If you run `brew services list` then you should see dnsmasq as an entry, and you can stop it via `brew services stop dnsmasq` (this might need sudo).

Note that the valet services are listed to be started on boot, so whenever you restart your computer, all the above _might_ be running again. You can also manually start the services again with `valet start` for nginx, and `brew services start dnsmasq` to manually start dnsmasq again (valet start might do this for you, I haven't checked).