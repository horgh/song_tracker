#!/usr/bin/env tclsh8.5
#
# Send a test song update to the website
#

package require http

if {$argc != 3} {
	puts "Usage: $argv0 <URL to api.php> <username> <password>"
	exit 1
}
set url [lindex $argv 0]
set user [lindex $argv 1]
set pass [lindex $argv 2]

set query [http::formatQuery \
	user $user \
	pass $pass \
	album "some album" \
	title "some title" \
	artist "綾倉盟" \
	length "11:22"]

puts "Sending request to $url"
puts "Request body: $query"

set token [http::geturl $url -query $query]
set data [http::data $token]
http::cleanup $token

puts "Response:"
puts $data
