#
# 20/08/2010
# by horgh
#
# Irssi script that provides /np functionality (say current listening track
# as read from website)
#
# Intended for use with song_tracker PHP website script
#
# The config variable $url must be set
#

use strict;
use warnings;

# for ceil/floor
use POSIX ();

# Use LWP::UserAgent instead of LWP::Simple for https. This may only be
# necessary to set the VERIFY_HOSTNAME off as that may be causing the
# failures I have seen (due to my self signed cert).
use LWP::UserAgent ();
# Disable SSL verification
$ENV{PERL_LWP_SSL_VERIFY_HOSTNAME} = 0;

use vars qw($VERSION %IRSSI);

$VERSION = "0.1";
%IRSSI = (
	authors => "horgh",
	contact => "will\@summercat.com",
	name => "song_tracker",
	description => "Provide now playing (/np) from song_tracker website",
	license => "Public Domain",
	url => "http://www.summercat.com",
	changed => "20/08/10"
);

# Configuration
my $username = "cd";
my $url = "https://leviathan.summercat.com/~a/music/api.php?last=1&user=$username";

# Take a length in milliseconds and return in a nicer format:
# mm:ss
sub format_length {
	my ($ms) = @_;
	my $seconds_total = POSIX::ceil($ms / 1000);
	my $mins = POSIX::floor($seconds_total / 60);
	my $seconds = $seconds_total % 60;
  return (sprintf "%02d:%02d", $mins, $seconds);
}

# @return mixed string or undef
sub get_song {
  my $ua = LWP::UserAgent->new;
  my $req = HTTP::Request->new(GET => $url,);
  my $res = $ua->request($req);
  if (!$res->is_success) {
    Irssi::print("Could not fetch song");
    return undef;
  }
  my $result = $res->decoded_content;

	# Response should have length given at the end in form: (ms)
	if ($result =~ /\((\d+)\)$/) {
		my $length = &format_length($1);
		$result =~ s/\((\d+\))$/($length)/;
	}
	return $result;
}

sub cmd_np {
	my ($data, $server, $witem) = @_;
	my $track = &get_song;
	if ($track && $witem) {
		$witem->command("say np: $track");
	}
}

Irssi::command_bind('np', 'cmd_np', 'song_tracker');
