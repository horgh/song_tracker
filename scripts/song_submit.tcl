#!/usr/bin/env tclsh8.5
#
# same as song_submit.py essentially
#

package require http

set url http://leviathan.summercat.com/~a/music/index.php
set query [http::formatQuery artist [lindex $argv 0] album [lindex $argv 1] title [lindex $argv 2] length [lindex $argv 3] pass a110e6b9a361653a042e3f5dfbac4c6105693789]
set t [http::geturl $url -query $query]
puts [http::data $t]
puts $query
puts [encoding system]
