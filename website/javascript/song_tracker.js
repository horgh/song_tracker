"use strict";

if (St === undefined) {
	var St = {};
}

// url_prefix is the request prefix to which we send ajax requests.
St.url_prefix = '/song_tracker2';

// top_limit is the limit for 'top' requests - such as the number of
// top artists we want.
St.top_limit = 10;

// St.user_id is the user id of the user whose page we are viewing (if we are
// viewing a user's page).

//! get_top_artists performs an ajax request to get the top artists.
/*!
 * @param string element_id At this time this is the id of a table element.
 * @param int days_back The number of days back to request the top artists
 *   for. The API allows this to be unspecified to retrieve the top
 *   artists of all time.
 * @param Array next_requests An array of objects, each representing
 *   another top artist request to make. Each object has the
 *   properties 'element_id', and 'days_back'.
 *
 * we set the results into the element with id element_id.
 *
 * we retrieve the top artists for the given days back.
 *
 * after we complete and place the results into the document,
 * we call ourselves with the data for the next_request to perform
 * another request in a chain (if there are any left).
 */
St.get_top_artists = function(element_id, days_back, next_requests) {
	// days_back is optional.
	if (element_id === undefined || next_requests === undefined) {
		return;
	}

	var params = {
		'user_id': St.user_id,
		'limit': St.top_limit
	};
	if (days_back !== undefined) {
		params.days_back = days_back;
	}

	$.get(
		St.url_prefix + '/top/artists',
		params,
		function(data, textStatus, jqXHR) {
			// data.Counts may be null if no counts found.
			for (var i = 0; data.Counts && i < data.Counts.length; ++i) {
				var count = data.Counts[i];
				var tr = $('<tr>');
				var label_td = $('<td>').text(count.Label)
					.addClass('label');
				tr.append(label_td);
				var count_td = $('<td>').text(count.Count)
					.addClass('label');
				tr.append(count_td);
				$('#' + element_id).append(tr);
			}

			if (next_requests.length > 0) {
				var next_request = next_requests.shift();
				St.get_top_artists(next_request.element_id, next_request.days_back,
					next_requests);
			}
		}
	);
}

//! get_top_songs performs an ajax request to get the top songs.
/*!
 * @param string element_id At this time this is the id of a table element.
 * @param int days_back The number of days back to request the top songs
 *   for. The API allows this to be unspecified to retrieve the top
 *   songs of all time.
 * @param Array next_requests An array of objects, each representing
 *   another top song request to make. Each object has the
 *   properties 'element_id', and 'days_back'.
 *
 * we set the results into the element with id element_id.
 *
 * we retrieve the top songs for the given days back.
 *
 * after we complete and place the results into the document,
 * we call ourselves with the data for the next_request to perform
 * another request in a chain (if there are any left).
 */
St.get_top_songs = function(element_id, days_back, next_requests) {
	// days_back is optional.
	if (element_id === undefined || next_requests === undefined) {
		return;
	}

	var params = {
		'user_id': St.user_id,
		'limit': St.top_limit
	};
	if (days_back !== undefined) {
		params.days_back = days_back;
	}

	$.get(
		St.url_prefix + '/top/songs',
		params,
		function(data, textStatus, jqXHR) {
			// data.Counts may be null if no counts found.
			for (var i = 0; data.Counts && i < data.Counts.length; ++i) {
				var count = data.Counts[i];
				var tr = $('<tr>');
				var label_td = $('<td>').text(count.Label)
					.addClass('label');
				tr.append(label_td);
				var count_td = $('<td>').text(count.Count)
					.addClass('label');
				tr.append(count_td);
				$('#' + element_id).append(tr);
			}

			if (next_requests.length > 0) {
				var next_request = next_requests.shift();
				St.get_top_songs(next_request.element_id, next_request.days_back,
					next_requests);
			}
		}
	);
}

$(document).ready(function() {
	// if we are viewing a user's page, then we initiate some ajax requests
	// to build their charts.
	// these are chained in that we do one chart, then the next, and so on.
	if (St.user_id !== undefined) {
		var artists_requests = [
			{'element_id': 'top_artists_day', 'days_back': 1 },
			{'element_id': 'top_artists_week', 'days_back': 7 },
			{'element_id': 'top_artists_month', 'days_back': 30 },
			{'element_id': 'top_artists_three_months', 'days_back': 30 * 3 },
			{'element_id': 'top_artists_six_months', 'days_back': 30 * 6 },
			{'element_id': 'top_artists_year', 'days_back': 365 },
			{'element_id': 'top_artists_all_time' }
		];
		var artists_request = artists_requests.shift();
		St.get_top_artists(artists_request.element_id, artists_request.days_back,
			artists_requests);

		var songs_requests = [
			{'element_id': 'top_songs_day', 'days_back': 1 },
			{'element_id': 'top_songs_week', 'days_back': 7 },
			{'element_id': 'top_songs_month', 'days_back': 30 },
			{'element_id': 'top_songs_three_months', 'days_back': 30 * 3 },
			{'element_id': 'top_songs_six_months', 'days_back': 30 * 6 },
			{'element_id': 'top_songs_year', 'days_back': 365 },
			{'element_id': 'top_songs_all_time' }
		];
		var songs_request = songs_requests.shift();
		St.get_top_songs(songs_request.element_id, songs_request.days_back,
			songs_requests);
	}
});
