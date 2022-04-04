<?php

$gameinfos = array(
	'game_name' => 'Slovenian Tarokk',
	'designer'  => '',
	'artist'    => 'Mikko Saari',
	'year'      => 1800,
	'publisher' => '',
	'publisher_website' => '',
	'publisher_bgg_id' => 0,
	'bgg_id' => 2780,
	'players' => array( 4 ),
	'suggest_player_number' => null,
	'not_recommend_player_number' => null,
	'estimated_duration' => 30,

	// Time in second add to a player when "giveExtraTime" is called (speed profile = fast)
	'fast_additional_time' => 30,
	// Time in second add to a player when "giveExtraTime" is called (speed profile = medium)
	'medium_additional_time' => 40,
	// Time in second add to a player when "giveExtraTime" is called (speed profile = slow)
	'slow_additional_time' => 50,

	'tie_breaker_description' => "",
	'losers_not_ranked' => false,
	'solo_mode_ranked' => false,

	'is_beta' => 1,
	'is_coop' => 0,

	'language_dependency' => false,
	'complexity' => 3,
	'luck' => 3,
	'strategy' => 3,
	'diplomacy' => 0,

	'player_colors' => array( "ff0000", "008000", "0000ff", "ffa500", "773300" ),
	'favorite_colors_support' => true,
	'disable_player_order_swap_on_rematch' => false,

	// Game interface width range (pixels)
	// Note: game interface = space on the left side, without the column on the right
	'game_interface_width' => array(

		// Minimum width
		//  default: 740
		//  maximum possible value: 740 (ie: your game interface should fit with a 740px width (correspond to a 1024px screen)
		//  minimum possible value: 320 (the lowest value you specify, the better the display is on mobile)
		'min' => 740,

		// Maximum width
		//  default: null (ie: no limit, the game interface is as big as the player's screen allows it).
		//  maximum possible value: unlimited
		//  minimum possible value: 740
		'max' => null
	),

	// Game presentation
	// Short game presentation text that will appear on the game description page, structured as an array of paragraphs.
	// Each paragraph must be wrapped with totranslate() for translation and should not contain html (plain text without formatting).
	// A good length for this text is between 100 and 150 words (about 6 to 9 lines on a standard display)
	'presentation' => array(
		totranslate( 'Slovenian Tarokk is a game of chance and luck. The goal is to win the game by getting the highest score.' ),
	),

	// Games categories
	//  You can attribute a maximum of FIVE "tags" for your game.
	//  Each tag has a specific ID (ex: 22 for the category "Prototype", 101 for the tag "Science-fiction theme game")
	//  Please see the "Game meta information" entry in the BGA Studio documentation for a full list of available tags:
	//  http://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php
	//  IMPORTANT: this list should be ORDERED, with the most important tag first.
	//  IMPORTANT: it is mandatory that the FIRST tag is 1, 2, 3 and 4 (= game category)
	'tags' => array( 3, 220, 200, 1, 28 ),


	//////// BGA SANDBOX ONLY PARAMETERS (DO NOT MODIFY)

	// simple : A plays, B plays, C plays, A plays, B plays, ...
	// circuit : A plays and choose the next player C, C plays and choose the next player D, ...
	// complex : A+B+C plays and says that the next player is A+B
	'is_sandbox' => false,
	'turnControl' => 'simple'
);
