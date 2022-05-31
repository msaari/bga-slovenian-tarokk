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
 * material.inc.php
 *
 * SlovenianTarokk game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 */

$this->colors = array(
	SUIT_SPADES => array(
		'name'   => clienttranslate( 'spade' ),
		'nametr' => self::_('spade'),
		'symbol' => '♠',
	),
	SUIT_CLUBS => array(
		'name'   => clienttranslate( 'club' ),
		'nametr' => self::_('club'),
		'symbol' => '♣',
	),
	SUIT_HEARTS => array(
		'name'   => clienttranslate( 'heart' ),
		'nametr' => self::_('heart'),
		'symbol' => '<span style="color: #D22B2B">♥</span>',
	),
	SUIT_DIAMONDS => array(
		'name'   => clienttranslate( 'diamond' ),
		'nametr' => self::_('diamond'),
		'symbol' => '<span style="color: #D22B2B">♦</span>',
	),
	SUIT_TRUMP => array(
		'name'   => clienttranslate( 'trump' ),
		'nametr' => self::_('trump'),
		'symbol' => '',
	)
);

$this->black_suit_labels = array(
	7 => '7',
	8 => '8',
	9 => '9',
	10 => '10',
	11 => clienttranslate('J'),
	12 => clienttranslate('C'),
	13 => clienttranslate('Q'),
	14 => clienttranslate('K'),
);

$this->red_suit_labels = array(
	7 => '4',
	8 => '3',
	9 => '2',
	10 => '1',
	11 => clienttranslate('J'),
	12 => clienttranslate('C'),
	13 => clienttranslate('Q'),
	14 => clienttranslate('K'),
);

$this->trump_values = array(
	1 => '1 (Pagat)',
	2 => '2',
	3 => '3',
	4 => '4',
	5 => '5',
	6 => '6',
	7 => '7',
	8 => '8',
	9 => '9',
	10 => '10',
	11 => '11',
	12 => '12',
	13 => '13',
	14 => '14',
	15 => '15',
	16 => '16',
	17 => '17',
	18 => '18',
	19 => '19',
	20 => '20',
	21 => '21 (Mond)',
	22 => 'Škiš',
);

$this->trump_point_values = array(
	1  => 5,
	2  => 1,
	3  => 1,
	4  => 1,
	5  => 1,
	6  => 1,
	7  => 1,
	8  => 1,
	9  => 1,
	10 => 1,
	11 => 1,
	12 => 1,
	13 => 1,
	14 => 1,
	15 => 1,
	16 => 1,
	17 => 1,
	18 => 1,
	19 => 1,
	20 => 1,
	21 => 5,
	22 => 5,
);

$this->point_values = array(
	7  => 1,
	8  => 1,
	9  => 1,
	10 => 1,
	11 => 2,
	12 => 3,
	13 => 4,
	14 => 5,
);

$this->bid_data = array(
	BID_KLOP => array(
		'name'  => clienttranslate( 'Klop' ),
		'stat'  => 'bid_klop',
		'value' => 70,
	),
	BID_THREE => array(
		'name'  => clienttranslate( 'Three' ),
		'stat'  => 'bid_three',
		'value' => 10,
	),
	BID_TWO => array(
		'name'  => clienttranslate( 'Two' ),
		'stat'  => 'bid_two',
		'value' => 20,
	),
	BID_ONE => array(
		'name'  => clienttranslate( 'One' ),
		'stat'  => 'bid_one',
		'value' => 30,
	),
	BID_SOLO_THREE => array(
		'name'  => clienttranslate( 'Solo three' ),
		'stat'  => 'bid_solo_three',
		'value' => 40,
	),
	BID_SOLO_TWO => array(
		'name'  => clienttranslate( 'Solo two' ),
		'stat'  => 'bid_solo_two',
		'value' => 50,
	),
	BID_SOLO_ONE => array(
		'name'  => clienttranslate( 'Solo one' ),
		'stat'  => 'bid_solo_one',
		'value' => 60,
	),
	BID_BEGGAR => array(
		'name'  => clienttranslate( 'Beggar' ),
		'stat'  => 'bid_beggar',
		'value' => 70,
	),
	BID_SOLO_WITHOUT => array(
		'name'  => clienttranslate( 'Solo without' ),
		'stat'  => 'bid_solo_without',
		'value' => 80,
	),
	BID_OPEN_BEGGAR => array(
		'name'  => clienttranslate( 'Open beggar' ),
		'stat'  => 'bid_open_beggar',
		'value' => 90,
	),
	BID_COLOUR_VALAT_WITHOUT => array(
		'name'  => clienttranslate( 'Colour valat without' ),
		'stat'  => 'bid_colour_valat_without',
		'value' => 125,
	),
	BID_COLOUR_VALAT => array(
		'name'  => clienttranslate( 'Colour valat' ),
		'stat'  => 'bid_colour_valat',
		'value' => 125,
	),
	BID_VALAT => array(
		'name'  => clienttranslate( 'Valat' ),
		'stat'  => 'bid_valat',
		'value' => 500,
	),
);

$this->announcements = array(
	ANNOUNCEMENT_GAME => array(
		'name'   => clienttranslate( 'game' ),
		'value'  => 'gameValue',
		'player' => '',
	),
	ANNOUNCEMENT_TRULA => array(
		'name'   => clienttranslate( 'trula' ),
		'value'  => 'trulaValue',
		'player' => 'trulaPlayer',
		'points' => 10,
	),
	ANNOUNCEMENT_KINGS => array(
		'name'   => clienttranslate( 'kings' ),
		'value'  => 'kingsValue',
		'player' => 'kingsPlayer',
		'points' => 10,
	),
	ANNOUNCEMENT_KINGULTIMO => array(
		'name'   => clienttranslate( 'king ultimo' ),
		'value'  => 'kingUltimoValue',
		'player' => 'kingUltimoPlayer',
		'points' => 10,
	),
	ANNOUNCEMENT_PAGATULTIMO => array(
		'name'   => clienttranslate( 'pagat ultimo' ),
		'value'  => 'pagatUltimoValue',
		'player' => 'pagatUltimoPlayer',
		'points' => 25,
	),
	ANNOUNCEMENT_VALAT => array(
		'name'   => clienttranslate( 'valat' ),
		'value'  => 'valatValue',
		'player' => 'valatPlayer',
		'points' => 250,
	),
);

$this->announcement_values = array(
	ANNOUNCEMENT_BASIC      => '',
	ANNOUNCEMENT_KONTRA     => clienttranslate('kontra'),
	ANNOUNCEMENT_REKONTRA   => clienttranslate('rekontra'),
	ANNOUNCEMENT_SUBKONTRA  => clienttranslate('subkontra'),
	ANNOUNCEMENT_MORDKONTRA => clienttranslate('mordkontra'),
);
