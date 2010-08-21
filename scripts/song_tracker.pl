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
my $url = "http://leviathan.summercat.com/~a/music/index.php?last";

sub get_song {
	my $result = get($url);
	die "Could not fetch song" unless defined $result;
	return $result;
}

sub cmd_np {
	my ($data, $server, $witem) = @_;
	my $track = get_song();
	if ($witem) {
		$witem->command("say np: $track");
	}
}

Irssi::command_bind('np', 'cmd_np', 'song_tracker');
