<?php
/*
Selfish Bottom Feeder
This simple bot will make a low bid on all game auctions
On games, it makes whichever choice has the bigger potential payoff
*/

bid_on_games();
play_games();

function bid_on_games() {
	// Make this bid on all games, adjust as needed
	$bid = 80;

	// Get games on auction
	$path = 'games_on_auction';
	$data = array();
	$response = send_request($path, $data);
	if ($response->error) {
		die();
	}

	// Foreach game on auction
	foreach ($response->games_on_auction as $game) {

		// If game has bid by you, skip
		if ($game->has_bid_by_you) {
			continue;
		}

		// Make bid
		$path = 'game/bid';
		$data = array();
		$data['game_id'] = (int) $game->id;
		$data['amount'] = $bid;
		$response = send_request($path, $data);
	}
}

function play_games() {
	// Get your turn games
	$path = 'started_games';
	$data = array();
	$response = send_request($path, $data);
	if (!$response) {
		die();
	}

	// Foreach game on auction
	foreach ($response->started_games as $game) {

		// If choice has already been made, skip
		if ($game->your_choice_made) {
			continue;
		}

		// Find the choice that has the best potential payoff
		$best_payoff_amount = 0;
		$best_payoff_choice = 0;
		foreach ($game->payoffs as $payoff) {
			if ($game->your_player_type) {
				if ($payoff->primary_payoff > $best_payoff_amount) {
					$best_payoff_amount = $payoff->primary_payoff;
					$best_payoff_choice = $payoff->primary_choice;
				}
			}
			else {
				if ($payoff->secondary_payoff > $best_payoff_amount) {
					$best_payoff_amount = $payoff->secondary_payoff;
					$best_payoff_choice = $payoff->secondary_choice;
				}
			}
		}

		// Play the game
		$path = 'game/play';
		$data = array();
		$data['game_id'] = (int) $game->id;
		$data['choice'] = $best_payoff_choice;
		$response = send_request($path, $data);
	}
}

function send_request($path, $data) {
	// Info for debug
	echo 'Calling ' . $path . ': ' . print_r($data, true) . PHP_EOL . '<br>';

	// Authentication
	$api_key = '';
	$user_id = 1;

	// Add authentication to post
	$data['user_id'] = $user_id;
	$data['api_key'] = $api_key;
	$post = json_encode($data);

	// Create URL
	$base_url = 'https://karmadilemma.com/api/';
	$url = $base_url . $path;

	// Perform API Call with CURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$raw_response = curl_exec($ch);

	// Get Response
	$response = json_decode($raw_response);

	// Catch error
	if ($response->error) {
		echo $response->error_code . ' - ' . $response->error_message . PHP_EOL . '<br>';
	}

	// Return response
	return $response;
}