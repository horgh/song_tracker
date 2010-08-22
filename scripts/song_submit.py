#!/usr/bin/env python3
#
# Take command line arguments of artist, album, title, length and submit to
# website. Intended for use with song_change PHP script.
#
# Example usage is to be called from the audacious plugin "song change" on new
# song:
#  - /path/to/song_submit.py "%a" "%b" "%r" "%l"
#    where %a is artist, %b is album, %r is track name, and %l is length
#
# Setup:
#  - Set configuration variables for URL below & authentication
#

# http://leviathan.summercat.com/~a/music/index.php
UPDATE_SITE = "leviathan.summercat.com"
UPDATE_PATH = "/~a/music/index.php"
PASSWORD = "a110e6b9a361653a042e3f5dfbac4c6105693789"

import sys
import http.client, urllib.parse, hashlib

# same as in moc script
def send_song_http(song):
	params = urllib.parse.urlencode(song)
	headers = {"Content-type": "application/x-www-form-urlencoded",
		"Accept": "text/plain"}
	conn = http.client.HTTPConnection(UPDATE_SITE)
	conn.request("POST", UPDATE_PATH, params, headers)
	# Debug
	#print("Params:", params)
	#response = conn.getresponse()
	#print("status: " + str(response.status))
	#print(response.read())
	#response.reason
	conn.close()

if len(sys.argv) != 5:
	print("Usage: %s <artist> <album> <song> <length>" % sys.argv[0])

params = dict()
params["artist"] = sys.argv[1]
params["album"] = sys.argv[2]
params["title"] = sys.argv[3]
params["length"] = sys.argv[4]
params["pass"] = PASSWORD

send_song_http(params)
