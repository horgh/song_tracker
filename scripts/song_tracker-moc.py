#!/usr/bin/env python3
#
# 20/08/2010
# by horgh
#
# This script reads the current song playing in moc (audio player) and when
# a new song is found it is sent to a website.
#
# Sent as POST request with params: artist, album, title, length
#
# I based this on lastfm-mocp by Denis Fernandez Cabrera from
# ftp://daper.net/pub/soft/moc/contrib/lastfm-mocp
#
# moc can be found at: http://moc.daper.net
#
# Requirements: python3
#

#MOC_COMMAND = "/usr/bin/mocp -i"
MOC_COMMAND = "/home/will/t/mocp-dev2/bin/mocp -i"
# http://leviathan.summercat.com/~a/music/index.php
UPDATE_SITE = "leviathan.summercat.com"
UPDATE_PATH = "/~a/music/index.php"
# This password must be same as password in song_tracker PHP
PASSWORD = "a110e6b9a361653a042e3f5dfbac4c6105693789"

import time
import http.client
import urllib.parse
import os
import hashlib

def send_song_http(song):
	params = urllib.parse.urlencode(song)
	headers = {"Content-type": "application/x-www-form-urlencoded",
		"Accept": "text/plain"}
	conn = http.client.HTTPConnection(UPDATE_SITE)
	conn.request("POST", UPDATE_PATH, params, headers)
	conn.close()
	# Debug
	#print("Params:", params)
	#response = conn.getresponse()
	#print("status: " + str(response.status))
	#print(response.read())
	#response.reason

def get_after_prefix(s):
	return s[s.index(":")+2:]

def get_current_song():
	proc = os.popen(MOC_COMMAND)
	data = proc.read()
	proc.close()
	data = data.split("\n")
	if len(data) < 7:
		return ""

	artist = get_after_prefix(data[3])
	title = get_after_prefix(data[4])
	album = get_after_prefix(data[5])
	length = get_after_prefix(data[6])
	result = dict()
	result["artist"] = artist
	result["album"] = album
	result["title"] = title
	result["length"] = length
	result["pass"] = PASSWORD
	return result

def begin():
	current_song = ""
	while True:
		song = get_current_song()
		if song != current_song and song != "":
			print("Sending http:" + str(song))
			send_song_http(song)
			current_song = song
		time.sleep(1)

begin()
