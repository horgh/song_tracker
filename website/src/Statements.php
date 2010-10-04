<?
/*
 * Hold MySQL statements & types
 */

class Statements {
	const _INSERT_SONG = "INSERT INTO songs (artist, album, title, length) VALUES(?, ?, ?, ?)";
	const _INSERT_SONG_TYPE = "ssss";
	const _INSERT_USER = "INSERT INTO users (user, pass, email) VALUES(?, ?, ?)";
	const _INSERT_USER_TYPE = "sss";
	const _INSERT_PLAY = "INSERT INTO plays (songid, userid) VALUES(?, ?)";
	const _INSERT_PLAY_TYPE = "ii";
	const _LAST_PLAYS = "SELECT p.id, p.date, s.artist, s.album, s.title, s.length FROM plays p, songs s WHERE p.songid = s.id AND p.userid = ? ORDER BY p.date DESC LIMIT ?";
	const _LAST_PLAYS_TYPE = "ii";
	const _GET_SONGID = "SELECT s.id FROM songs s WHERE title = ? AND artist = ? AND album = ?";
	const _GET_SONGID_TYPE = "sss";
	const _USER_DATA = "SELECT u.id, u.pass FROM users u WHERE user = ?";
	const _USER_DATA_TYPE = "s";
	const _USER_ID = "SELECT u.id FROM users u WHERE user = ?";
	const _USER_ID_TYPE = "s";
	const _GET_USERS = "SELECT u.user FROM users u ORDER BY u.user ASC";
	const _TOP_SONGS = "SELECT COUNT(s.id) AS count, CONCAT(s.artist, ' - ', s.title) FROM plays p JOIN songs s ON p.songid = s.id WHERE p.userid = ? GROUP BY p.songid ORDER BY count DESC LIMIT ?";
	const _TOP_SONGS_TYPE = "ii";
	const _TOP_ARTISTS = "SELECT COUNT(s.id) AS count, s.artist FROM plays p JOIN songs s ON p.songid = s.id WHERE p.userid = ? AND s.artist != '' GROUP BY s.artist ORDER BY count DESC LIMIT ?";
	const _TOP_ARTISTS_TYPE = "ii";
	const _PLAYS_USER = "SELECT COUNT(p.id) FROM plays p WHERE userid = ?";
	const _PLAYS_USER_TYPE = "i";

	const _DEBUG_SONG_BY_LENGTH = "SELECT s.id, s.title, s.artist, s.album, s.length FROM songs s WHERE length = ?";
	const _DEBUG_SONG_BY_LENGTH_TYPE = "s";
}
?>
