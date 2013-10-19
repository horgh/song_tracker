#!/usr/bin/env python2.6
#
# Take the data .txt output that is output from lastscrape
# (see http://bugs.libre.fm/wiki/Using_lastscrape) and insert into the given
# MySQL db.
#
# Intended for use with song tracker PHP site
#
# Works with python2.*. Requires the python mysqldb library
#

# DB Configuration
DB_HOST = "leviathan"
DB_USER = "songs"
DB_PASS = "songs"
DB_DB = "songs"

# song tracker specifics
# user_id to add the songs to
USER_ID = 1

import MySQLdb
import sys

def insert_play(db, c, artist, title, date):
	song_id = insert_song(db, c, title, artist)
	# insert play
	c.execute("""insert into plays (date, songid, userid) values (%s, %s, %s)""",
		(date, song_id, USER_ID))
	#c.execute()
	print("Inserted " + artist + " - " + title + " with date " + date + " (Play id " + str(db.insert_id()) + ")")

# used by insert_play
def insert_song(db, c, title, artist):
	songid = -1
	try:
		c.execute("""insert into songs(artist, title, album) values(%s, %s, %s)""",
			(artist, title, ""))
		return db.insert_id()
	except Exception as e:
		print("Song already exists; getting existing song id...")
		return select_songid(c, title, artist)

# used by insert_song
def select_songid(c, title, artist):
	c.execute("""select s.id from songs s where title = %s and artist = %s""",
		(title, artist))
	row = c.fetchone()
	return row[0]

def db_connect():
	db = MySQLdb.connect(host=DB_HOST, user=DB_USER, passwd=DB_PASS, db=DB_DB, use_unicode=True, charset="utf8")
	return db

def insert_from_file(filename):
	db = db_connect()
	c = db.cursor()
	f = open(filename)
	for line in f:
		line = line.strip().split("\t")
		# artist, title, date
		insert_play(db, c, line[0], line[1], line[2])

if len(sys.argv) != 2:
	print("Usage: %s <lastfm-data.txt>" % sys.argv[0])
	sys.exit()

insert_from_file(sys.argv[1])
