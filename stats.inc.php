<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SlovenianTarokk implementation : © Mikko Saari <mikko@mikkosaari.fi>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * SlovenianTarokk game statistics description
 */

$stats_type = array(
	'table'  => array(
		'bid_klop' => array(
			'id'   => 10,
			'name' => totranslate( 'Klop' ),
			'type' => 'int',
		),
		'bid_three' => array(
			'id'   => 11,
			'name' => totranslate( 'Three' ),
			'type' => 'int',
		),
		'bid_two' => array(
			'id'   => 12,
			'name' => totranslate( 'Two' ),
			'type' => 'int',
		),
		'bid_one' => array(
			'id'   => 13,
			'name' => totranslate( 'One' ),
			'type' => 'int',
		),
		'bid_solo_three' => array(
			'id'   => 14,
			'name' => totranslate( 'Solo 3' ),
			'type' => 'int',
		),
		'bid_solo_two' => array(
			'id'   => 15,
			'name' => totranslate( 'Solo 2' ),
			'type' => 'int',
		),
		'bid_solo_one' => array(
			'id'   => 16,
			'name' => totranslate( 'Solo 1' ),
			'type' => 'int',
		),
		'bid_beggar' => array(
			'id'   => 17,
			'name' => totranslate( 'Beggar' ),
			'type' => 'int',
		),
		'bid_solo_without' => array(
			'id'   => 18,
			'name' => totranslate( 'Solo withouy' ),
			'type' => 'int',
		),
		'bid_open_beggar' => array(
			'id'   => 19,
			'name' => totranslate( 'Open beggar' ),
			'type' => 'int',
		),
		'bid_colour_valat_without' => array(
			'id'   => 20,
			'name' => totranslate( 'Colour valat without' ),
			'type' => 'int',
		),
		'bid_colour_valat' => array(
			'id'   => 21,
			'name' => totranslate( 'Colour valat' ),
			'type' => 'int',
		),
		'bid_valat' => array(
			'id'   => 22,
			'name' => totranslate( 'Valat' ),
			'type' => 'int',
		),
	),
	'player' => array(
		'games_declared' => array(
			'id'   => 10,
			'name' => totranslate( 'Games declared' ),
			'type' => 'int',
		),
		'games_as_partner' => array(
			'id'   => 11,
			'name' => totranslate( 'Games as a partner' ),
			'type' => 'int',
		),
		'hands_won' => array(
			'id'   => 12,
			'name' => totranslate( 'Hands won as declarer' ),
			'type' => 'int',
		),
		'hands_lost' => array(
			'id'   => 13,
			'name' => totranslate( 'Hands lost as declarer' ),
			'type' => 'int',
		),
		'monds_captured' => array(
			'id'   => 14,
			'name' => totranslate( 'Monds captured' ),
			'type' => 'int',
		),
		'monds_lost' => array(
			'id'   => 15,
			'name' => totranslate( 'Monds lost' ),
			'type' => 'int',
		),
		'emperor_tricks_taken' => array(
			'id'   => 16,
			'name' => totranslate( "Emperor's tricks taken" ),
			'type' => 'int',
		),
		'tricks_taken' => array(
			'id'   => 17,
			'name' => totranslate( 'Tricks taken' ),
			'type' => 'int',
		),
		'radli_cleared' => array(
			'id'   => 18,
			'name' => totranslate( 'Radli cleared' ),
			'type' => 'int',
		),
		'kings_in_talon' => array(
			'id'   => 19,
			'name' => totranslate( 'Called king in talon' ),
			'type' => 'int',
		),
		'beggars_played' => array(
			'id'   => 20,
			'name' => totranslate( 'Beggar games played' ),
			'type' => 'int',
		),
		'beggars_won' => array(
			'id'   => 21,
			'name' => totranslate( 'Beggar games won' ),
			'type' => 'int',
		),
		'solos_played' => array(
			'id'   => 22,
			'name' => totranslate( 'Solo games played' ),
			'type' => 'int',
		),
		'solos_won' => array(
			'id'   => 23,
			'name' => totranslate( 'Solo games won' ),
			'type' => 'int',
		),
		'valats_played' => array(
			'id'   => 24,
			'name' => totranslate( 'Valat games played' ),
			'type' => 'int',
		),
		'valats_won' => array(
			'id'   => 25,
			'name' => totranslate( 'Valat games won' ),
			'type' => 'int',
		),
		'announcements_made' => array(
			'id'   => 26,
			'name' => totranslate( 'Announcements made' ),
			'type' => 'int',
		),
		'announcements_kontrad' => array(
			'id'   => 27,
			'name' => totranslate( 'Announcements kontrad' ),
			'type' => 'int',
		),
	),
);
