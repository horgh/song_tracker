#!/usr/bin/env perl
#
# 05/12/2011
#
# Migrate data from MySQL to Postgres
#
# Encoding issue solution:
# - Extract from MySQL as latin1
# - but actual data is in utf8? so decode

use warnings;
use strict;
use DBI ();
use Encode ();
#use open IO => ":utf8",":std";
use utf8;

# copy users table from mysql to postgres
sub copy_users {
  my ($dbh_mysql, $dbh_postgres) = @_;

  my $sth_mysql = $dbh_mysql->prepare("SELECT * FROM users")
    or die $dbh_mysql->errstr;
  $sth_mysql->execute()
    or die $sth_mysql->errstr;

  my $href = $sth_mysql->fetchall_hashref('id');
  if ($dbh_mysql->err) {
    die $dbh_mysql->errstr;
  }

  # Postgres statement
  my $sth_postgres = $dbh_postgres->prepare("INSERT INTO users (id, name, pass, email, create_time) VALUES(?, ?, ?, ?, ?)")
    or die $dbh_postgres->errstr;

  foreach my $id (keys %$href) {
    #my $id <- loop var
    my $user = Encode::decode("utf8", $href->{$id}->{user});
    my $date = $href->{$id}->{date};
    my $pass = Encode::decode("utf8", $href->{$id}->{pass});
    my $email = Encode::decode("utf8", $href->{$id}->{email});

    print "Got id [$id] user [$user] date [$date] pass [$pass] email [$email]\n";

    my @params = ($id, $user, $pass, $email, $date);
    $sth_postgres->execute(@params)
      or die $sth_postgres->errstr;
    die unless $sth_postgres->rows == 1;
  }
  return 1;
}

# copy songs table from mysql to postgres
sub copy_songs {
  my ($dbh_mysql, $dbh_postgres) = @_;

  my $sth_mysql = $dbh_mysql->prepare("SELECT * FROM songs")
    or die $dbh_mysql->errstr;
  $sth_mysql->execute()
    or die $sth_mysql->errstr;

  my $href = $sth_mysql->fetchall_hashref('id');
  if ($dbh_mysql->err) {
    die $dbh_mysql->errstr;
  }

  # Postgres statement
  my $sth_postgres = $dbh_postgres->prepare("INSERT INTO songs (id, title, artist, album, length) VALUES(?, ?, ?, ?, ?)")
    or die $dbh_postgres->errstr;

  # Put each songs row into Postgres
  foreach my $id (keys %$href) {
    #my $id <- loop var

    my $title = $href->{$id}->{title};
    my $title_utf8 = Encode::decode("utf8", $title);

    my $artist = $href->{$id}->{artist};
    my $artist_utf8 = Encode::decode("utf8", $artist);

    # album is optional
    my $album_utf8 = '';
    if (defined($href->{$id}->{album})) {
      my $album = $href->{$id}->{album};
      $album_utf8 = Encode::decode("utf8", $album);
    }

    # length is optional
    my $length_utf8 = '';
    if (defined($href->{$id}->{length})) {
      my $length = $href->{$id}->{length};
      $length_utf8 = Encode::decode("utf8", $length);
    }

    print "Got id [$id] title [$title_utf8] artist [$artist_utf8]"
      . " album [$album_utf8] length [$length_utf8]\n";

    my @params = ($id, $title_utf8, $artist_utf8, $album_utf8, $length_utf8);
    $sth_postgres->execute(@params)
      or die $sth_postgres->errstr;
    die unless $sth_postgres->rows == 1;
  }
  return 1;
}

# copy plays table from mysql to postgres
sub copy_plays {
  my ($dbh_mysql, $dbh_postgres) = @_;

  my $sth_mysql = $dbh_mysql->prepare("SELECT * FROM plays")
    or die $dbh_mysql->errstr;
  $sth_mysql->execute()
    or die $sth_mysql->errstr;

  my $href = $sth_mysql->fetchall_hashref('id');
  if ($dbh_mysql->err) {
    die $dbh_mysql->errstr;
  }

  # Postgres statement
  my $sth_postgres = $dbh_postgres->prepare("INSERT INTO plays (id, song_id, user_id, create_time) VALUES(?, ?, ?, ?)")
    or die $dbh_postgres->errstr;

  foreach my $id (keys %$href) {
    #my $id <- loop var
    my $songid = $href->{$id}->{songid};
    my $userid = $href->{$id}->{userid};
    my $date = $href->{$id}->{date};

    print "Got id [$id] songid [$songid] userid [$userid] date [$date]\n";

    my @params = ($id, $songid, $userid, $date);
    $sth_postgres->execute(@params)
      or die $sth_postgres->errstr;
    die unless $sth_postgres->rows == 1;
  }
  return 1;
}

# utf8 stdout
binmode STDOUT, ":encoding(utf8)";

# MySQL db & tables are in latin1 (iso-8859-1)
my $dsn_mysql = "DBI:mysql:database=songs;host=beast.lan";
my $dbh_mysql = DBI->connect($dsn_mysql, 'songs', 'songs');
#my $dbh_mysql = DBI->connect($dsn_mysql, 'songs', 'songs', { mysql_enable_utf8 => 1 });
#$dbh_mysql->{'mysql_enable_utf8'} = 0;
#$dbh_mysql->{'mysql_enable_utf8'} = 1;
#$dbh_mysql->do("SET NAMES 'utf8';");
$dbh_mysql->do("SET NAMES 'latin1';");

my $dsn_postgres = "DBI:Pg:dbname=songs;host=beast.lan";
my $dbh_postgres = DBI->connect($dsn_postgres, 'songs', 'songs', { pg_enable_utf8 => 1});

# users table
#&copy_users($dbh_mysql, $dbh_postgres);

# songs table
&copy_songs($dbh_mysql, $dbh_postgres);

# plays table
&copy_plays($dbh_mysql, $dbh_postgres);

print "NOTE: you will need to set the serial sequence for each table to be after the last id for each table (or else primary key / unique will be validated when trying to insert new\n";
