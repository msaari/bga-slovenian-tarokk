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
		'name' => clienttranslate( 'spade' ),
		'nametr' => self::_('spade')
	),
	SUIT_CLUBS => array(
		'name' => clienttranslate( 'club' ),
		'nametr' => self::_('club')
	),
	SUIT_HEARTS => array(
		'name' => clienttranslate( 'heart' ),
		'nametr' => self::_('heart')
	),
	SUIT_DIAMONDS => array(
		'name' => clienttranslate( 'diamond' ),
		'nametr' => self::_('diamond')
	),
	SUIT_TRUMP => array(
		'name' => clienttranslate( 'trump' ),
		'nametr' => self::_('trump')
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

$this->bid_names = array(
	1 => clienttranslate( 'Klop' ),
	2 => clienttranslate( 'Three' ),
	3 => clienttranslate( 'Two' ),
	4 => clienttranslate( 'One' ),
	5 => clienttranslate( 'Solo three' ),
	6 => clienttranslate( 'Solo two' ),
	7 => clienttranslate( 'Solo one' ),
	8 => clienttranslate( 'Beggar' ),
	9 => clienttranslate( 'Solo without' ),
	10 => clienttranslate( 'Open beggar' ),
	11 => clienttranslate( 'Colour valat' ),
	12 => clienttranslate( 'Colour valat without' ),
	13 => clienttranslate( 'Valat' ),
	'pass' => clienttranslate( 'Pass' ),
);

$this->bid_point_values = array(
	1 => 70,
	2 => 10,
	3 => 20,
	4 => 30,
	5 => 40,
	6 => 50,
	7 => 60,
	8 => 70,
	9 => 80,
	10 => 90,
	11 => 125,
	12 => 125,
	13 => 500,
);

$this->announcements = array(
	ANNOUNCEMENT_GAME => array(
		'name'  => clienttranslate( 'game' ),
		'value' => 'gameValue',
		'team'  => '',
	),
	ANNOUNCEMENT_TRULA => array(
		'name'  => clienttranslate( 'trula' ),
		'value' => 'trulaValue',
		'team'  => 'trulaTeam',
	),
	ANNOUNCEMENT_KINGS => array(
		'name'  => clienttranslate( 'kings' ),
		'value' => 'kingsValue',
		'team'  => 'kingsTeam',
	),
	ANNOUNCEMENT_KINGULTIMO => array(
		'name'  => clienttranslate( 'king ultimo' ),
		'value' => 'kingUltimoValue',
		'team'  => 'kingUltimoTeam',
	),
	ANNOUNCEMENT_PAGATULTIMO => array(
		'name'  => clienttranslate( 'pagat ultimo' ),
		'value' => 'pagatUltimoValue',
		'team'  => 'pagatUltimoTeam',
	),
	ANNOUNCEMENT_VALAT => array(
		'name'  => clienttranslate( 'valat' ),
		'value' => 'valatValue',
		'team'  => 'valatTeam',
	),
);

$this->announcement_values = array(
	ANNOUNCEMENT_BASIC      => '',
	ANNOUNCEMENT_KONTRA     => clienttranslate('kontra'),
	ANNOUNCEMENT_REKONTRA   => clienttranslate('rekontra'),
	ANNOUNCEMENT_SUBKONTRA  => clienttranslate('subkontra'),
	ANNOUNCEMENT_MORDKONTRA => clienttranslate('mordkontra'),
);
