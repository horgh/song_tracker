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

use LWP::Simple;

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
my $url = "http://leviathan.summercat.com/~a/music/api.php?last=1&user=$username";

# Take a length in milliseconds and return in a nicer format:
# mm:ss
sub format_length {
	my ($ms) = @_;
	my $seconds_total = POSIX::ceil($ms / 1000);
	my $mins = POSIX::floor($seconds_total / 60);
	my $seconds = $seconds_total % 60;
	return "$mins:$seconds";
}

sub get_song {
	my $result = get($url);
	die "Could not fetch song" unless defined $result;
	# Response should have length given at the end in form: (ms)
	Irssi::print("here1");
	if ($result =~ /\((\d+)\)$/) {
		Irssi::print("ytes");
		my $length = &format_length($1);
		$result =~ s/\((\d+\))$/($length)/;
	}
	return $result;
}

sub cmd_np {
	my ($data, $server, $witem) = @_;
	my $track = &get_song();
	if ($witem) {
		$witem->command("say np: $track");
	}
}

Irssi::command_bind('np', 'cmd_np', 'song_tracker');
