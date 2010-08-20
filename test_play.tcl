#!/usr/bin/env tclsh8.5
package require http

set url http://leviathan.summercat.com/~a/music/music.php

set query [http::formatQuery album "some album" title "some title" artist "some artist" length "11:22"]
puts $query
set token [http::geturl $url -query $query]
set data [http::data $token]
http::cleanup $token
puts $data
