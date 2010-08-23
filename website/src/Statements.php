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
	const _LAST_PLAYS = "SELECT p.id, p.date, s.artist, s.album, s.title, s.length FROM plays p, songs s WHERE p.songid = s.id AND p.userid = ? ORDER BY p.id DESC LIMIT ?";
	const _LAST_PLAYS_TYPE = "ii";
	const _GET_SONGID = "SELECT s.id FROM songs s WHERE title = ? AND artist = ? AND album = ?";
	const _GET_SONGID_TYPE = "sss";
	const _USER_DATA = "SELECT u.id, u.pass FROM users u WHERE user = ?";
	const _USER_DATA_TYPE = "s";
	const _USER_ID = "SELECT u.id FROM users u WHERE user = ?";
	const _USER_ID_TYPE = "s";
	const _GET_USERS = "SELECT u.user FROM users u ORDER BY u.user ASC";
}
?>
