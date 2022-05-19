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
 * gameoptions.inc.php
 *
 * SlovenianTarokk game options description
 *
 * In this file, you can define your game options (= game variants).
 *
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in sloveniantarokk.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 */

define( 'OPTION_GAME_LENGTH', 100 );
define( 'OPTION_RADLI_VALUE', 101 );
define( 'OPTION_ALLOW_UPGRADES', 102 );

define( 'VALUE_YES', 1 );
define( 'VALUE_NO', 2 );

$game_options = array(
	OPTION_GAME_LENGTH => array(
		'name'   => totranslate( 'Game length' ),
		'values' => array(
			4 => array( 'name' => totranslate( '4 deals' ) ),
			8 => array( 'name' => totranslate( '8 deals' ) ),
			12 => array( 'name' => totranslate( '12 deals' ) ),
			16 => array( 'name' => totranslate( '16 deals' ) ),
			32 => array( 'name' => totranslate( '32 deals' ) ),
		)
	),

	OPTION_RADLI_VALUE => array(
		'name'   => totranslate( 'Radli point value' ),
		'values' => array(
			100 => array( 'name' => totranslate( '-100 points' ) ),
			40 => array( 'name' => totranslate( '-40 points' ) ),
		)
	),

	OPTION_ALLOW_UPGRADES => array(
		'name'   => totranslate( 'Allow upgrading contract' ),
		'values' => array(
			VALUE_YES => array( 'name' => totranslate( 'Yes' ) ),
			VALUE_NO  => array( 'name' => totranslate( 'No' ) ),
		)
	),
);
