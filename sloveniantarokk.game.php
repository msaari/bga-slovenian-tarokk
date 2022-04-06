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
  * sloveniantarokk.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  */

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

define( 'HAND_TYPE_NORMAL', 1 );

define( 'SUIT_SPADES', 1 );
define( 'SUIT_CLUBS', 2 );
define( 'SUIT_HEARTS', 3 );
define( 'SUIT_DIAMONDS', 4 );
define( 'SUIT_TRUMP', 5 );

define( 'RED_SUITS', array( SUIT_HEARTS, SUIT_DIAMONDS ) );

class SlovenianTarokk extends Table {
	function __construct() {
		// Your global variables labels:
		//  Here, you can assign labels to global variables you are using for this game.
		//  You can use any number of global variables with IDs between 10 and 99.
		//  If your game has options (variants), you also have to associate here a label to
		//  the corresponding ID in gameoptions.inc.php.
		// Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
		parent::__construct();

		self::initGameStateLabels(
			array(
				'currentHandType' => 10,
				'trickColor'      => 11,
				'dealer'          => 12,
				'declarer'        => 13,
				'declarerPartner' => 14,
			)
		);

		$this->cards = self::getNew( 'module.common.deck' );
		$this->cards->init( 'card' );
	}

	protected function getGameName() {
		// Used for translations and stuff. Please do not modify.
		return "sloveniantarokk";
	}

	/*
		setupNewGame:

		This method is called only once, when a new game is launched.
		In this method, you must setup the game according to the game rules, so that
		the game is ready to be played.
	*/
	protected function setupNewGame( $players, $options = array() ) {
		// Set the colors of the players with HTML color code
		// The default below is red/green/blue/orange/brown
		// The number of colors defined here must correspond to the maximum number of players allowed for the gams
		$gameinfos      = self::getGameinfos();
		$default_colors = $gameinfos['player_colors'];

		// Create players
		// Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
		$sql    = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ';
		$values = array();
		foreach ( $players as $player_id => $player ) {
			$color    = array_shift( $default_colors );
			$name     = addslashes( $player['player_name'] );
			$avatar   = addslashes( $player['player_avatar'] );
			$values[] = "('$player_id','$color','{$player['player_canal']}','$name','$avatar')";
		}
		$sql .= implode( $values, ',' );
		self::DbQuery( $sql );
		self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
		self::reloadPlayersBasicInfos();

		self::setGameStateInitialValue( 'dealer', 0 );
		self::setGameStateInitialValue( 'currentHandType', 0 );
		self::setGameStateInitialValue( 'trickColor', 0 );
		self::setGameStateInitialValue( 'declarer', 0 );
		self::setGameStateInitialValue( 'declarerPartner', 0 );

		// Create cards
		$cards = array ();
		foreach ( $this->colors as $color_id => $color ) {
			if ( $color_id < SUIT_TRUMP ) {
				// Non-trump suits.
				for ( $value = 7; $value <= 14; $value++ ) {
					$cards[] = array(
						'type'     => $color_id,
						'type_arg' => $value,
						'nbr'      => 1
					);
				}
			} else {
				// Trump suit.
				for ( $value = 1; $value <= 22; $value++ ) {
					$cards[] = array(
						'type'     => $color_id,
						'type_arg' => $value,
						'nbr'      => 1
					);
				}
			}
		}

		$this->cards->createCards( $cards, 'deck' );

		/*
		*********** End of the game initialization *****/
	}

	/*
		getAllDatas:

		Gather all informations about current game situation (visible by the current player).

		The method is called each time the game interface is displayed to a player, ie:
		_ when the game starts
		_ when a player refreshes the game page (F5)
	*/
	protected function getAllDatas() {
		$result = array();

		$current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

		// Get information about players
		// Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
		$sql = 'SELECT player_id id, player_score score FROM player ';

		$result['players'] = self::getCollectionFromDb( $sql );

		// Cards in player hand
		$result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );

		// Cards played on the table
		$result['cardsontable'] = $this->cards->getCardsInLocation( 'cardsontable' );

		// Cards in talon
		$result['cardsintalon'] = $this->cards->getCardsInLocation( 'talon' );

		return $result;
	}

	/*
		getGameProgression:

		Compute and return the current game progression.
		The number returned must be an integer beween 0 (=the game just started) and
		100 (= the game is finished or almost finished).

		This method is called each time we are in a game state with the "updateGameProgression" property set to true
		(see states.inc.php)
	*/
	function getGameProgression() {
		// TODO: compute and return the game progression

		return 0;
	}


	//////////////////////////////////////////////////////////////////////////////
	//////////// Utility functions
	////////////

	function getCardDisplayValue( $color, $card ) {
		if ( $color == SUIT_TRUMP ) {
			return $this->trump_values[ $card ];
		} elseif ( in_array( $color, RED_SUITS ) ) {
			return $this->red_suit_labels[ $card ];
		} else {
			return $this->black_suit_labels[ $card ];
		}
	}

	function countScores( $teamCards ) {
		$scores = array();
		foreach ( $teamCards as $team => $cards ) {
			$totalPoints = 0;
			foreach ( $cards as $card ) {
				$totalPoints += $card['type'] == SUIT_TRUMP
					? $this->trump_point_values[ intval( $card['type_arg'] ) ]
					: $this->point_values[ intval( $card['type_arg'] ) ];
			}
			$cardCount    = count( $cards );
			self::trace( "Total points: " . $totalPoints );
			self::trace( "Card count: " . $cardCount );
			$totalPoints -= intdiv( $cardCount, 3 ) * 2;
			self::trace( "Total points after reduction: " . $totalPoints );
			if ( $cardCount % 3 !== 0 ) {
				self::trace( "Card count not divisible by 3" );
				$totalPoints -= 1;
			}
			$scores[ $team ] = $totalPoints;
		}
		return $scores;
	}

	function haveColorInHand( $color, $hand ) {
		foreach ( $hand as $card ) {
			if ( intval( $card['type'] ) === intval( $color ) ) {
				return true;
			}
		}
		return false;
	}

	function getPlayerWithTheKing( $color ) {
		$sql    = "SELECT card_location, card_location_arg FROM card WHERE card_type = '"
			. intval( $color ) . "' AND card_type_arg = 14";
		$result = self::getObjectFromDB( $sql );
		if ( $result['card_location'] === 'talon' ) {
			return 'talon';
		} else {
			return $result['card_location_arg'];
		}
	}

	//////////////////////////////////////////////////////////////////////////////
	//////////// Player actions
	////////////

	function playCard( $card_id ) {
		self::checkAction( 'playCard' );
		$player_id         = self::getActivePlayerId();
		$currentCard       = $this->cards->getCard( $card_id );
		$currentTrickColor = intval( self::getGameStateValue( 'trickColor' ) );
		$cardsInHand       = $this->cards->getCardsInLocation( 'hand', $player_id );

		if ( $currentTrickColor > 0 ) {
			if ( $this->haveColorInHand( $currentTrickColor, $cardsInHand )
				&& intval( $currentCard['type'] ) !== $currentTrickColor ) {
				throw new BgaUserException( self::_( 'You must play a ' ) . $this->colors[ $currentTrickColor ]['name'] . '.' );
			}
		}
		$this->cards->moveCard( $card_id, 'cardsontable', $player_id );

		if ( $currentTrickColor === 0 ) {
			self::setGameStateValue( 'trickColor', $currentCard['type'] );
		}

		self::notifyAllPlayers(
			'playCard',
			clienttranslate( '${player_name} plays ${color_displayed} ${value_displayed}' ),
			array(
				'i18n'            => array( 'color_displayed','value_displayed' ),
				'card_id'         => $card_id,
				'player_id'       => $player_id,
				'player_name'     => self::getActivePlayerName(),
				'value'           => $currentCard['type_arg'],
				'value_displayed' => $this->getCardDisplayValue( $currentCard['type'], $currentCard['type_arg'] ),
				'color'           => $currentCard['type'],
				'color_displayed' => $this->colors[ $currentCard['type'] ]['name']
			),
		);

		self::trace( 'playCard->playCard' );
		$this->gamestate->nextState( 'playCard' );
	}

	function callSpadeKing() {
		self::checkAction('callSpadeKing');
		self::trace('callSpadeKing');
		$this->callKing( SUIT_SPADES );
	}

	function callClubKing() {
		self::checkAction('callClubKing');
		self::trace('callClubKing');
		$this->callKing( SUIT_CLUBS );
	}

	function callHeartKing() {
		self::checkAction('callHeartKing');
		self::trace('callHeartKing');
		$this->callKing( SUIT_HEARTS );
	}

	function callDiamondKing() {
		self::checkAction('callDiamondKing');
		self::trace('callDiamondKing');
		$this->callKing( SUIT_DIAMONDS );
	}

	function callKing( $color ) {
		$players = self::loadPlayersBasicInfos();
		$partner = $this->getPlayerWithTheKing( $color );

		self::notifyAllPlayers(
			'callKing',
			clienttranslate( '${player_name} calls the ${color_displayed} king' ),
			array(
				'i18n'            => array( 'color_displayed' ),
				'player_id'       => self::getActivePlayerId(),
				'player_name'     => self::getActivePlayerName(),
				'color'           => $color,
				'color_displayed' => $this->colors[ $color ]['name']
			)
		);

		if ( $partner !== 'talon' && $partner !== self::getActivePlayerId() ) {
			self::setGameStateValue( 'declarerPartner', $partner );
			self::notifyPlayer(
				$partner,
				'youreChosen',
				clienttranslate( 'You have the ${color_displayed} king and are the declarer\'s partner' ),
				array(
					'i18n'            => array( 'color_displayed' ),
					'color'           => $color,
					'color_displayed' => $this->colors[ $color ]['name']
				)
			);
		}

		self::trace('callKing->kingChosen->newTrick');
		$this->gamestate->nextState( 'kingChosen' );
	}

	//////////////////////////////////////////////////////////////////////////////
	//////////// Game state arguments
	////////////

	/*
		Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
		These methods function is to return some additional information that is specific to the current
		game state.
	*/

	/*

	Example for game state "MyGameState":

	function argMyGameState()
	{
		// Get some values from the current game situation in database...

		// return values:
		return array(
			'variable1' => $value1,
			'variable2' => $value2,
			...
		);
	}
	*/

	//////////////////////////////////////////////////////////////////////////////
	//////////// Game state actions
	////////////

	function stNewHand() {
		self::setGameStateValue( 'currentHandType', HAND_TYPE_NORMAL );
		self::setGameStateValue( 'trickColor', 0 );

		$this->cards->moveAllCardsInLocation( null, 'deck' );
		$this->cards->shuffle('deck');

		$dealer = intval( self::getGameStateValue( 'dealer' ) );

		if ( $dealer === 0 ) {
			$playerTable = $this->getNextPlayerTable();
			$dealer      = $this->getPlayerBefore( $playerTable[0] );
			$players     = self::loadPlayersBasicInfos();

			self::notifyAllPlayers(
				'newDealer',
				clienttranslate( '${player_name} is the first dealer' ),
				array(
					'player_id'   => $dealer,
					'player_name' => $players[ $dealer ]['player_name'],
				)
			);
		}
		$this->gamestate->changeActivePlayer( $this->getPlayerAfter( $dealer ) );
		self::setGameStateValue( 'declarer', $this->getActivePlayerId() );

		// Deal 12 cards to each player.
		$players = self::loadPlayersBasicInfos();
		foreach ( $players as $player_id => $player ) {
			$cards = $this->cards->pickCards( 12, 'deck', $player_id );
			self::notifyPlayer(
				$player_id,
				'newHand',
				'',
				array(
					'cards' => $cards
				)
			);
		}

		$this->talon = $this->cards->pickCardsForLocation( 6, 'deck', 'talon' );

		self::notifyAllPlayers(
			'declarer',
			clienttranslate( '${player_name} is the declarer' ),
			array(
				'player_id'   => $this->getActivePlayerId(),
				'player_name' => $players[ $this->getActivePlayerId() ]['player_name'],
			)
		);

		self::trace( 'stNewHand->kingCalling' );
		$this->gamestate->nextState();
	}

	function stNewTrick() {
		self::setGameStateInitialValue( 'trickColor', 0 );
		self::trace( 'stNewTrick->playerTurn' );
		$this->gamestate->nextState();
	}

	function stNextPlayer() {
		if ( intval( $this->cards->countCardInLocation( 'cardsontable' ) ) === 4 ) {
			self::trace('Trick over, determine winner.');
			$cardsOnTable      = $this->cards->getCardsInLocation( 'cardsontable' );
			$bestValue         = 0;
			$bestValuePlayerId = null;
			$bestValueIsTrump  = false;
			$currentTrickColor = intval( self::getGameStateValue( 'trickColor' ) );

			foreach ( $cardsOnTable as $card ) {
				$cardValue = $card['type_arg'];
				$cardColor = intval( $card['type'] );
				if ( $cardColor === $currentTrickColor && ! $bestValueIsTrump ) {
					if ( $cardValue > $bestValue ) {
						$bestValue         = $cardValue;
						$bestValuePlayerId = $card['location_arg'];
					}
				}
				if ( $cardColor === 5 ) {
					if ( ! $bestValueIsTrump ) {
						$bestValue         = $cardValue;
						$bestValuePlayerId = $card['location_arg'];
						$bestValueIsTrump  = true;
					} else {
						if ( $cardValue > $bestValue ) {
							$bestValue         = $cardValue;
							$bestValuePlayerId = $card['location_arg'];
						}
					}
				}
			}

			$this->gamestate->changeActivePlayer( $bestValuePlayerId );
			$this->cards->moveAllCardsInLocation( 'cardsontable', 'cardswon', null, $bestValuePlayerId );

			$players = self::loadPlayersBasicInfos();
			self::notifyAllPlayers(
				'trickWin',
				clienttranslate( '${player_name} wins the trick' ),
				array(
					'player_id'   => $bestValuePlayerId,
					'player_name' => $players[ $bestValuePlayerId ]['player_name'],
				)
			);
			self::notifyAllPlayers(
				'giveAllCardsToPlayer',
				'',
				array(
					'player_id' => $bestValuePlayerId,
				)
			);

			if ( intval( $this->cards->countCardInLocation( 'hand' ) ) === 0 ) {
				self::trace( 'stNextPlayer->endHand' );
				$this->gamestate->nextState( 'endHand' );
			} else {
				self::trace( 'stNextPlayer->nextTrick' );
				$this->gamestate->nextState( 'nextTrick' );
			}
		} else {
			$player_id = self::activeNextPlayer();
			self::giveExtraTime( $player_id );
			self::trace( 'stNextPlayer->nextPlayer' );
			$this->gamestate->nextState( 'nextPlayer' );
		}
	}

	function stCountingAndScoring() {
		$players = self::loadPlayersBasicInfos();

		$teamCards = array();

		$declarer        = intval( self::getGameStateValue( 'declarer' ) );
		$declarerTeam    = array( $declarer );
		$declarerPartner = intval( self::getGameStateValue( 'declarerPartner' ) );
		if ( $declarerPartner ) {
			$declarerTeam[] = $declarerPartner;
		}

		$cards = $this->cards->getCardsInLocation("cardswon");
		foreach ( $cards as $card ) {
			$team = in_array( $card['location_arg'], $declarerTeam ) ? 'Declarer' : 'Opponents';

			$teamCards[ $team ][] = $card;
		}

		$teamScores = $this->countScores( $teamCards );

		foreach ( $teamScores as $team => $score ) {
			self::notifyAllPlayers(
				'score',
				clienttranslate( '${team} team scores ${score}' ),
				array(
					'team'  => $team,
					'score' => $score,
				)
			);
		}

		$difference = abs( $teamScores['Declarer'] - 35 );
		$difference = floor( $difference / 5 ) * 5;
		$points     = 10 + $difference;

		if ( $teamScores['Declarer'] > 35 ) {
			$sql = "UPDATE player SET player_score=player_score+$points  WHERE player_id='$declarer'";
			self::DbQuery($sql);
			if ( $declarerPartner ) {
				$sql = "UPDATE player SET player_score=player_score+$points  WHERE player_id='$declarerPartner'";
				self::DbQuery($sql);
			}
			self::notifyAllPlayers(
				'points',
				clienttranslate( 'Declarer\s team gains ${points} points' ),
				array ( 'points' => $points )
			);
		} else {
			$sql = "UPDATE player SET player_score=player_score-$points  WHERE player_id='$declarer'";
			self::DbQuery($sql);
			if ( $declarerPartner ) {
				$sql = "UPDATE player SET player_score=player_score-$points  WHERE player_id='$declarerPartner'";
				self::DbQuery($sql);
			}
			self::notifyAllPlayers(
				'points',
				clienttranslate( 'Declarer\'s team lost ${points} points' ),
				array ( 'points' => $points )
			);
		}

		$newScores = self::getCollectionFromDb( 'SELECT player_id, player_score FROM player', true );
		self::notifyAllPlayers( 'newScores', '', array( 'newScores' => $newScores ) );

		self::trace( 'stCountingAndScoring->endHand' );
		$this->gamestate->nextState();
	}

	function stEndHand() {
		$players = self::loadPlayersBasicInfos();
		$dealer  = intval( self::getGameStateValue( 'dealer' ) );

		$nextDealer = $this->getPlayerAfter( $dealer );
		self::notifyAllPlayers(
			'newDealer',
			clienttranslate( '${player_name} is the new dealer' ),
			array(
				'player_id'   => $nextDealer,
				'player_name' => $players[ $nextDealer ]['player_name'],
			)
		);
		self::setGameStateValue( 'dealer', $nextDealer );

		self::trace( 'stEndHand->nextHand' );
		$this->gamestate->nextState( 'nextHand' );
	}

	//////////////////////////////////////////////////////////////////////////////
	//////////// Zombie
	////////////

	/*
		zombieTurn:

		This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
		You can do whatever you want in order to make sure the turn of this player ends appropriately
		(ex: pass).

		Important: your zombie code will be called when the player leaves the game. This action is triggered
		from the main site and propagated to the gameserver from a server, not from a browser.
		As a consequence, there is no current player associated to this action. In your zombieTurn function,
		you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
	*/

	function zombieTurn( $state, $active_player ) {
		$statename = $state['name'];

		if ($state['type'] === "activeplayer") {
			switch ($statename) {
				default:
					$this->gamestate->nextState( "zombiePass" );
					break;
			}

			return;
		}

		if ($state['type'] === "multipleactiveplayer") {
			// Make sure player is in a non blocking status for role turn
			$this->gamestate->setPlayerNonMultiactive( $active_player, '' );

			return;
		}

		throw new feException( "Zombie mode not supported at this game state: ".$statename );
	}

	///////////////////////////////////////////////////////////////////////////////////:
	////////// DB upgrade
	//////////

	/*
		upgradeTableDb:

		You don't have to care about this until your game has been published on BGA.
		Once your game is on BGA, this method is called everytime the system detects a game running with your old
		Database scheme.
		In this case, if you change your Database scheme, you just have to apply the needed changes in order to
		update the game database and allow the game to continue to run with your new version.

	*/

	function upgradeTableDb( $from_version ) {
		// $from_version is the current version of this game database, in numerical form.
		// For example, if the game was running with a release of your game named "140430-1345",
		// $from_version is equal to 1404301345

		// Example:
		//        if( $from_version <= 1404301345 )
		//        {
		//            // ! important ! Use DBPREFIX_<table_name> for all tables
		//
		//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
		//            self::applyDbUpgradeToAllDB( $sql );
		//        }
		//        if( $from_version <= 1405061421 )
		//        {
		//            // ! important ! Use DBPREFIX_<table_name> for all tables
		//
		//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
		//            self::applyDbUpgradeToAllDB( $sql );
		//        }
		//        // Please add your future database scheme changes here
		//
		//


	}
}
