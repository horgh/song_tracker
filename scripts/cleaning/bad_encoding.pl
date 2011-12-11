#!/usr/bin/env perl
#
# 11/12/2011
# Attempt to deal with some issues with bad encodings in the database
#

use strict;
use warnings;
use DBI ();
use utf8;

sub print_song_record {
  my ($href) = @_;
  print "Artist: " . $href->{artist}
    . " Title: " . $href->{title}
    . " Album: " . $href->{album}
    . " Length: " . $href->{length}
    . "\n";
  return 1;
}

sub db_query {
  my ($dbh, $sql, $params_aref) = @_;
  my $sth = $dbh->prepare($sql)
    or die $dbh->errstr;
  $sth->execute(@$params_aref)
    or die $sth->errstr;
  return $sth;
}

sub db_select {
  my ($dbh, $sql, $params_aref) = @_;
  my $sth = &db_query($dbh, $sql, $params_aref);
  my $href = $sth->fetchall_hashref('id');
  die $dbh->errstr if $dbh->err;
  return $href;
}

sub db_manipulate {
  my ($dbh, $sql, $params_aref) = @_;
  my $sth = &db_query($dbh, $sql, $params_aref);
  return $sth->rows;
}

sub merge_invalid_song {
  my ($dbh, $song_href, $other_song_href) = @_;
  # Update the play entries for the first song to match the second's
  my $sql = "UPDATE plays SET song_id = ? WHERE song_id = ?";
  my @params = (
                $other_song_href->{id},
                $song_href->{id},
               );
  if (&db_manipulate($dbh, $sql, \@params) < 1) {
    print "No plays updated? Song:\n";
    &print_song_record($song_href);
  }
  # And delete the bad song
  $sql = "DELETE FROM songs WHERE id = ?";
  @params = ($song_href->{id});
  if (&db_manipulate($dbh, $sql, \@params) != 1) {
    print "Failed to delete original song?\n";
    &print_song_record($song_href);
  }

  print "Merged\n";
}

# Some artists are only '???', try to merge them
sub merge_songs_with_invalid_artists {
  my ($dbh) = @_;
  my $sql = "SELECT * FROM songs WHERE artist LIKE ?";
  my @params = ('%?%');
  my $href = &db_select($dbh, $sql, \@params);

  print "Finding songs with invalid artists...\n";
  my @bad_songs_href;
  # Get artists which are ONLY '?'
  foreach my $id (keys %$href) {
    my $artist = $href->{$id}->{artist};
    my $title = $href->{$id}->{title};
    my $length = $href->{$id}->{length};
    my $album = $href->{$id}->{album};
    next unless $artist =~ /^\?+$/;
    &print_song_record($href->{$id});
    push(@bad_songs_href, $href->{$id});
  }

  print "\nFinding songs which match these invalid songs...\n\n";
  # For each of these artists, if there are is a single song in the
  # db with the same title, length, and album, replace the song_id
  # for the original song with the song_id for the found new song
  # in play, and delete the original
  $sql = "SELECT * FROM songs"
       . " WHERE album = ? AND title = ? AND length = ?"
       . " AND id != ?";
  foreach my $song_href (@bad_songs_href) {
    @params = (
                $song_href->{album},
                $song_href->{title},
                $song_href->{length},
                $song_href->{id},
              );
    my $other_songs_href = &db_select($dbh, $sql, \@params);
    if (scalar(keys(%$other_songs_href)) == 1) {
      my $other_song_id = (keys(%$other_songs_href))[0];
      my $other_song_href = $other_songs_href->{$other_song_id};
      print "Found a match!\n";
      &print_song_record($song_href);
      print " ==== \n";
      &print_song_record($other_song_href);
      &merge_invalid_song($dbh, $song_href, $other_song_href);
    }
  }
}

# Some songs have no album information (blank) due to being from lastfm...
# (the lengths will also be blank)
# Look for matches where the title and artist matches and where there is
# only a single match which is not our record, and replace them
sub merge_songs_with_no_albums {
  my ($dbh) = @_;
  my $sql = "SELECT * FROM songs WHERE album = ? and length = ?";
  my @params = ('', '');
  my $href = &db_select($dbh, $sql, \@params);
  foreach my $id (keys %$href) {
    #print "Got a song with blanks\n";
    #&print_song_record($href->{$id});

    # Look for another song which matches us
    $sql = "SELECT * FROM songs"
         . " WHERE id != ?"
         . " AND artist = ? AND title = ?";
    @params = (
                $id,
                $href->{$id}->{artist},
                $href->{$id}->{title},
              );
    my $href2 = &db_select($dbh, $sql, \@params);
    # We can only merge if there is a single result
    next unless scalar(keys(%$href2)) == 1;
    my $other_song_id = (keys(%$href2))[0];
    my $other_song_href = $href2->{$other_song_id};
    print "Found possible merge:\n";
    &print_song_record($href->{$id});
    print " === \n";
    &print_song_record($other_song_href);
    print "\n\n";
    &merge_invalid_song($dbh, $href->{$id}, $other_song_href);
  }
}

# Since until now we have enforced uniqueness while still allowing different
# character cases of two strings to not be equal, some songs can be merged
# this way
sub merge_songs_case_insensitive {
  my ($dbh) = @_;
  # First get every song
  my $sql = "SELECT * FROM songs";
  my $href = &db_select($dbh, $sql, []);
  foreach my $id (keys %$href) {
    # For every song, see if there is an identical one if we lowercase
    # all fields
    $sql = "SELECT * FROM songs"
         . " WHERE id != ?"
         . " AND lower(title) = lower(?)"
         . " AND lower(artist) = lower(?)"
         . " AND lower(album) = lower(?)";
    my @params = (
                  $id,
                  $href->{$id}->{title},
                  $href->{$id}->{artist},
                  $href->{$id}->{album},
                 );
    my $href2 = &db_select($dbh, $sql, \@params);
    foreach my $id2 (keys %$href2) {
      print "Found possible match:\n";
      &print_song_record($href->{$id});
      print " === \n";
      &print_song_record($href2->{$id2});
      print "\n\n";
    }
  }
}

binmode STDOUT, ":encoding(utf8)";

my $dsn = "DBI:Pg:dbname=songs;host=beast.lan";
my $dbh = DBI->connect($dsn, 'songs', 'songs', { pg_enable_utf8 => 1})
  or die;

#&merge_songs_with_invalid_artists($dbh);
#&merge_songs_with_no_albums($dbh);
&merge_songs_case_insensitive($dbh);
