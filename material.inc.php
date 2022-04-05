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
	1 => array(
		'name' => clienttranslate( 'spade' ),
		'nametr' => self::_('spade')
	),
	2 => array(
		'name' => clienttranslate( 'club' ),
		'nametr' => self::_('club')
	),
	3 => array(
		'name' => clienttranslate( 'heart' ),
		'nametr' => self::_('heart')
	),
	4 => array(
		'name' => clienttranslate( 'diamond' ),
		'nametr' => self::_('diamond')
	),
	5 => array(
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
