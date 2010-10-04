#!/usr/bin/env tclsh8.5
#
# same as song_submit.py essentially
#

package require http

set url http://leviathan.summercat.com/~a/music/api.php
set username "cd"
set password "password"

if {$argc != 4} {
	puts "Usage: $argv0 <artist> <album> <song> <length>"
	return
}

puts "song_submit.tcl got: artist: [lindex $argv 0] album: [lindex $argv 1] title: [lindex $argv 1] length: [lindex $argv 3]"
set query [http::formatQuery artist [lindex $argv 0] album [lindex $argv 1] title [lindex $argv 2] length [lindex $argv 3] user $username pass $password]
set t [http::geturl $url -query $query -binary 1]
puts [http::data $t]
puts "converted: [encoding convertfrom utf-8 [http::data $t]] (end of converted)"
puts $query
http::cleanup $t
