#!/usr/bin/env tclsh8.5
#
# same as song_submit.py essentially
#

package require http

set url http://leviathan.summercat.com/~a/music/api.php
set username "cd"
set password "password"
set debug 1

if {$argc != 4 && $argc != 5} {
	puts "Usage: $argv0 <artist> <album> <song> <length> \[url to api.php\]"
	return
}

lassign $argv artist album title length
if {$argc == 5} {
	set url [lindex $argv 4]
}

set query [http::formatQuery artist $artist album $album title $title length $length user $username pass $password]
set t [http::geturl $url -query $query -binary 1]
if {$debug == 1} {
	puts "Sending data: artist: $artist album: $album title: $title length: $length"
	puts "Query: ${query}\n"
	#puts "Response: ([http::data $t])"
	puts "Converted response: ([encoding convertfrom utf-8 [http::data $t]])"
}
http::cleanup $t
