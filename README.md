# Requirements

 - PHP 5.3+
 - PostgreSQL (recent version?)
 - PostgreSQL pgcrypto module. This is typically packaged separately.
   In debian it may be found in the 'postgresql-contrib' package.
   This is needed as we currently use this for password hashing.


# Setup

 * Create a database and a database user.

 * Enable the pgcrypto postgresql module in the database.
   This must be done as the superuser.

        CREATE EXTENSION pgcrypto;

 * Set ``$DB_`` variables to match the above in src/Database.php

 * Import schema_postgres.sql to this database.

 * Import database functions to the database (functions_postgres.sql).

 * Place website/ directory in a webserver accesible location.

 * Visit http://your.website/music/register.php to register.

 * Begin updating plays to api.php.

In order to see the top artists/songs charts you will need to now use
run song_tracker2 which is a counterpart to this project.


# URLs

 By default, visiting a user page is
 http://your.website/music/index.php?user=username

 With URL rewriting you can have a URL such as
 http://your.website/music/username

 A rule to accomplish this in lighttpd is as follows:

    url.rewrite-once = (
      "^/music/([a-zA-Z]+)$" => "/music/index.php?user=$1"
    )

 Note: the mod_rewrite module must be loaded.
 A similar rule can be used with other webservers.


# Tracking plays in audacious

 * Enable the plugin 'Song Change' (under Plugins - General)

 * Go into the plugin's preferences and add a command for
  'command to run when Audacious starts a new song':

  This must include the path to a script to submit to the
  song tracker, such as song_submit.tcl:

    /path/to/song_submit.tcl "%a" "%b" "%T" "%l"

 * Create a song_submit configuration file. There should be a sample
   configuration available to you entitled song_submit.conf.sample.
   Update this sample and place it in the file ~/.config/song_submit

 * When you play a song, the play should now be recorded on your
   song tracker account.
