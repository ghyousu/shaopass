This app is created to track student's bathroom/water break times.

check addons:
   * heroku addons

add postgres:
   * heroku addons:create heroku-postgresql:hobby-dev

some commands:
   heroku pg:info

To establish a psql session with your remote database, use
   heroku pg:psql


heroku pg:ps
heroku pg:kill 31912
heroku pg:kill --force 31912
heroku pg:killall


The postgreSQL user your database is assigned doesn’t have permission
to create or drop databases. To drop and recreate your database, do
   herokue pg:reset DATABASE


php code:
{
   type 1:
      $db = parse_url(getenv("DATABASE_URL"));
      $db["path"] = ltrim($db["path"], "/");
   type 2:
      $conn = pg_connect(getenv("DATABASE_URL"));
}

{
https://www.postgresql.org/docs/9.4/functions-datetime.html
   age(timestamp, timestamp) -> return "interval"
}
