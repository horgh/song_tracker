#!/usr/bin/env tclsh8.5
#
# same as song_submit.py essentially
#

package require http

set url http://leviathan.summercat.com/~a/music/api.php
set username "cd"
set password "password"
set debug 1

if {$argc != 4} {
	puts "Got $argc arguments: $argv"
	puts "Usage: $argv0 <artist> <album> <song> <length>"
	return
}

set query [http::formatQuery artist [lindex $argv 0] album [lindex $argv 1] title [lindex $argv 2] length [lindex $argv 3] user $username pass $password]
set t [http::geturl $url -query $query -binary 1]
if {$debug == 1} {
	puts "song_submit.tcl: artist: [lindex $argv 0] album: [lindex $argv 1] title: [lindex $argv 2] length: [lindex $argv 3]"
	puts "Response: ([http::data $t])"
	puts "Converted response: ([encoding convertfrom utf-8 [http::data $t]])"
	puts "${query}\n"
}
http::cleanup $t
