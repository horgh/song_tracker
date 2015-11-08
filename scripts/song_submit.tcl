#!/usr/bin/env tclsh8.5
#
# will@summercat.com
#
# this script is for sending updates to the song tracker API.
#
# this is essentially the same as song_submit.py.
#
# for a sample config, please refer to song_submit.conf.sample.
#

package require http
package require tls

::http::register https 443 [list ::tls::socket -ssl2 0 -ssl3 0 -tls1 1]

# @return mixed dict or 0 if failure
#
# read our config file.
proc read_config {} {
	# try ~/.config/song_submit first.
	set conf {~/.config/song_submit}
	if {![file readable $conf]} {
		set conf {/etc/song_submit}
	}
	if {![file readable $conf]} {
		puts "Error: please create a config file (~/.config/song_submit or /etc/song_submit)"
		return 0
	}

	set f [open $conf r]
	set content [read $f]
	close $f

	set lines [split $content \n]
	set conf [dict create]
	foreach line $lines {
		if {[regexp {^\s*#} $line]} {
			continue
		}
		if {[regexp {^(\S+?)\s*=\s*(.+)$} $line -> option value]} {
			dict set conf $option $value
		}
	}

	# verify we received keys that we require.
	foreach required [list username password url] {
		if {![dict exists $conf $required]} {
			puts "Error: missing configuration option: $required"
			return 0
		}
	}
	return $conf
}

# @return int 1 success 0 failure
#
# program entry
proc main {} {
	global argc argv0 argv
	if {$argc != 4 && $argc != 5} {
		puts "Usage: $argv0 <artist> <album> <song> <length> \[url to api.php\]"
		return 0
	}
	lassign $argv artist album title length
	if {$argc == 5} {
		set url [lindex $argv 4]
	}

	# read our config.
	set conf [read_config]
	if {$conf == 0} {
		return 0
	}
	set username [dict get $conf username]
	set password [dict get $conf password]
	set url [dict get $conf url]
	set debug 0
	if {[dict exists $conf debug]} {
		set debug [dict get $conf debug]
	}

	# perform the update.
	set query [::http::formatQuery artist $artist album $album title $title length $length user $username pass $password]
	set t [::http::geturl $url -query $query -binary 1]
	if {$debug == 1} {
		puts "Sending data: artist: $artist album: $album title: $title length: $length"
		puts "Query: ${query}\n"
		puts "Converted response: ([encoding convertfrom utf-8 [::http::data $t]])"
	}
	http::cleanup $t

	return 1
}

set status [main]
if {$status != 1} {
	exit 1
}
exit 0
