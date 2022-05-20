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

define( 'SUIT_SPADES', 1 );
define( 'SUIT_CLUBS', 2 );
define( 'SUIT_HEARTS', 3 );
define( 'SUIT_DIAMONDS', 4 );
define( 'SUIT_TRUMP', 5 );

define( 'RED_SUITS', array( SUIT_HEARTS, SUIT_DIAMONDS ) );

define( 'HAND_SIZE', 12 );

define( 'CAPTURED_MOND_PENALTY', 20 );

define( 'BID_KLOP', 1 );
define( 'BID_THREE', 2 );
define( 'BID_TWO', 3 );
define( 'BID_ONE', 4 );
define( 'BID_SOLO_THREE', 5 );
define( 'BID_SOLO_TWO', 6 );
define( 'BID_SOLO_ONE', 7 );
define( 'BID_BEGGAR', 8 );
define( 'BID_SOLO_WITHOUT', 9 );
define( 'BID_OPEN_BEGGAR', 10 );
define( 'BID_COLOUR_VALAT_WITHOUT', 11 );
define( 'BID_COLOUR_VALAT', 12 );
define( 'BID_VALAT', 13 );

define( 'ANNOUNCEMENT_BASIC', 1 );
define( 'ANNOUNCEMENT_KONTRA', 2 );
define( 'ANNOUNCEMENT_REKONTRA', 4 );
define( 'ANNOUNCEMENT_SUBKONTRA', 8 );
define( 'ANNOUNCEMENT_MORDKONTRA', 16 );

define( 'ANNOUNCEMENT_GAME', 1 );
define( 'ANNOUNCEMENT_TRULA', 2 );
define( 'ANNOUNCEMENT_KINGS', 3 );
define( 'ANNOUNCEMENT_KINGULTIMO', 4 );
define( 'ANNOUNCEMENT_PAGATULTIMO', 5 );
define( 'ANNOUNCEMENT_VALAT', 6 );

define( 'TEAM_DECLARER', 1 );
define( 'TEAM_OPPONENT', 2 );

define( 'BONUS_SUCCESS', 1 );
define( 'BONUS_FAILURE', 2 );

if ( ! defined( 'OPTION_GAME_LENGTH' ) ) {
	define( 'OPTION_GAME_LENGTH', 100 );
	define( 'OPTION_RADLI_VALUE', 101 );
	define( 'OPTION_ALLOW_UPGRADES', 102 );

	define( 'VALUE_YES', 1 );
	define( 'VALUE_NO', 2 );
}

class SlovenianTarokk extends Table {
	function __construct() {
		parent::__construct();

		self::initGameStateLabels(
			array(
				'compulsoryKlop'      => 10,
				'trickColor'          => 11,
				'dealer'              => 12,
				'declarer'            => 13,
				'declarerPartner'     => 14,
				'forehand'            => 15,
				'secondPriority'	  => 16,
				'thirdPriority'		  => 17,
				'fourthPriority'	  => 18,
				'highBidder'          => 19,
				'highBid'             => 20,
				'firstPasser'		  => 21,
				'secondPasser'        => 22,
				'thirdPasser'         => 23,
				'trickCount'          => 24,
				'tricksByDeclarer'    => 25,
				'calledKing'          => 26,
				'trulaPlayer'         => 27,
				'trulaValue'          => 28,
				'kingsPlayer'         => 29,
				'kingsValue'          => 30,
				'kingUltimoPlayer'    => 31,
				'kingUltimoValue'     => 32,
				'pagatUltimoPlayer'   => 33,
				'pagatUltimoValue'    => 34,
				'valatPlayer'         => 35,
				'valatValue'          => 36,
				'gameValue'           => 37,
				'playerAnnouncements' => 38,
				'pagatUltimoStatus'   => 39,
				'kingUltimoStatus'    => 40,
				'handsPlayed'         => 41,
				'calledKingChosen'    => 42,
				'gameLength'          => OPTION_GAME_LENGTH,
				'radliValue'          => OPTION_RADLI_VALUE,
				'allowUpgrades'       => OPTION_ALLOW_UPGRADES,
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

		self::setGameStateInitialValue( 'compulsoryKlop', 0 );
		self::setGameStateInitialValue( 'dealer', 0 );
		self::setGameStateInitialValue( 'trickColor', 0 );
		self::setGameStateInitialValue( 'declarer', 0 );
		self::setGameStateInitialValue( 'declarerPartner', 0 );
		self::setGameStateInitialValue( 'forehand', 0 );
		self::setGameStateInitialValue( 'secondPriority', 0 );
		self::setGameStateInitialValue( 'thirdPriority', 0 );
		self::setGameStateInitialValue( 'fourthPriority', 0 );
		self::setGameStateInitialValue( 'highBidder', 0 );
		self::setGameStateInitialValue( 'highBid', 0 );
		self::setGameStateInitialValue( 'firstPasser', 0 );
		self::setGameStateInitialValue( 'secondPasser', 0 );
		self::setGameStateInitialValue( 'thirdPasser', 0 );
		self::setGameStateInitialValue( 'trickCount', 0 );
		self::setGameStateInitialValue( 'tricksByDeclarer', 0 );
		self::setGameStateInitialValue( 'calledKing', 0 );
		self::setGameStateInitialValue( 'trulaPlayer', 0 );
		self::setGameStateInitialValue( 'trulaValue', 0 );
		self::setGameStateInitialValue( 'kingsPlayer', 0 );
		self::setGameStateInitialValue( 'kingsValue', 0 );
		self::setGameStateInitialValue( 'kingUltimoPlayer', 0 );
		self::setGameStateInitialValue( 'kingUltimoValue', 0 );
		self::setGameStateInitialValue( 'pagatUltimoPlayer', 0 );
		self::setGameStateInitialValue( 'pagatUltimoValue', 0 );
		self::setGameStateInitialValue( 'valatPlayer', 0 );
		self::setGameStateInitialValue( 'valatValue', 0 );
		self::setGameStateInitialValue( 'gameValue', 0 );
		self::setGameStateInitialValue( 'playerAnnouncements', 0 );
		self::setGameStateInitialValue( 'pagatUltimoStatus', 0 );
		self::setGameStateInitialValue( 'kingUltimoStatus', 0 );
		self::setGameStateInitialValue( 'handsPlayed', 0 );
		self::setGameStateInitialValue( 'calledKingChosen', 0 );

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

		$this->initStat( 'player', 'games_declared', 0 );
		$this->initStat( 'player', 'games_as_partner', 0 );
		$this->initStat( 'player', 'hands_won', 0 );
		$this->initStat( 'player', 'hands_lost', 0 );
		$this->initStat( 'player', 'monds_captured', 0 );
		$this->initStat( 'player', 'monds_lost', 0 );
		$this->initStat( 'player', 'emperor_tricks_taken', 0 );
		$this->initStat( 'player', 'tricks_taken', 0 );
		$this->initStat( 'player', 'radli_cleared', 0 );
		$this->initStat( 'player', 'kings_in_talon', 0 );
		$this->initStat( 'player', 'beggars_played', 0 );
		$this->initStat( 'player', 'beggars_won', 0 );
		$this->initStat( 'player', 'solos_played', 0 );
		$this->initStat( 'player', 'solos_won', 0 );
		$this->initStat( 'player', 'valats_played', 0 );
		$this->initStat( 'player', 'valats_won', 0 );
		$this->initStat( 'player', 'announcements_made', 0 );
		$this->initStat( 'player', 'announcements_kontrad', 0 );

		$this->initStat( 'table', 'bid_klop', 0 );
		$this->initStat( 'table', 'bid_three', 0 );
		$this->initStat( 'table', 'bid_two', 0 );
		$this->initStat( 'table', 'bid_one', 0 );
		$this->initStat( 'table', 'bid_solo_three', 0 );
		$this->initStat( 'table', 'bid_solo_two', 0 );
		$this->initStat( 'table', 'bid_solo_one', 0 );
		$this->initStat( 'table', 'bid_beggar', 0 );
		$this->initStat( 'table', 'bid_solo_without', 0 );
		$this->initStat( 'table', 'bid_open_beggar', 0 );
		$this->initStat( 'table', 'bid_colour_valat_without', 0 );
		$this->initStat( 'table', 'bid_colour_valat', 0 );
		$this->initStat( 'table', 'bid_valat', 0 );
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
		$result['players'] = $this->getPlayerCollection();

		// Cards in player hand
		$result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );

		// Cards played on the table
		$result['cardsontable'] = $this->cards->getCardsInLocation( 'cardsontable' );

		// Cards in talon
		$result['cardsintalon'] = $this->cards->getCardsInLocation( 'talon' );

		$result['forehand']            = self::getGameStateValue( 'forehand' );
		$result['secondPriority']      = self::getGameStateValue( 'secondPriority' );
		$result['thirdPriority']       = self::getGameStateValue( 'thirdPriority' );
		$result['fourthPriority']      = self::getGameStateValue( 'fourthPriority' );
		$result['highBidder']          = self::getGameStateValue( 'highBidder' );
		$result['highBid']             = self::getGameStateValue( 'highBid' );
		$result['calledKing']          = self::getGameStateValue( 'calledKing' );
		$result['trulaPlayer']         = self::getGameStateValue( 'trulaPlayer' );
		$result['trulaValue']          = self::getGameStateValue( 'trulaValue' );
		$result['kingsPlayer']         = self::getGameStateValue( 'kingsPlayer' );
		$result['kingsValue']          = self::getGameStateValue( 'kingsValue' );
		$result['kingUltimoPlayer']    = self::getGameStateValue( 'kingUltimoPlayer' );
		$result['kingUltimoValue']     = self::getGameStateValue( 'kingUltimoValue' );
		$result['pagatUltimoPlayer']   = self::getGameStateValue( 'pagatUltimoPlayer' );
		$result['pagatUltimoValue']    = self::getGameStateValue( 'pagatUltimoValue' );
		$result['valatPlayer']         = self::getGameStateValue( 'valatPlayer' );
		$result['valatValue']          = self::getGameStateValue( 'valatValue' );
		$result['playerAnnouncements'] = self::getGameStateValue( 'playerAnnouncements' );
		$result['gameValue']           = self::getGameStateValue( 'gameValue' );
		$result['compulsoryKlop']      = self::getGameStateValue( 'compulsoryKlop' );

		return $result;
	}

	function getGameProgression() {
		$handsPlayed = self::getGameStateValue( 'handsPlayed' );
		$totalHands  = self::getGameStateValue( 'gameLength' );

		if ( $handsPlayed >= $totalHands ) {
			return 100;
		} else {
			return round( $handsPlayed * 100 / $totalHands, 0 );
		}
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

	function getCardSuitSymbol( $suit ) {
		return $this->colors[ $suit ]['symbol'];
	}

	function getCardPointValue( $card ) {
		return $card['type'] == SUIT_TRUMP
			? $this->trump_point_values[ intval( $card['type_arg'] ) ]
			: $this->point_values[ intval( $card['type_arg'] ) ];
	}

	function countScoresForPlayer( $cards ) {
		$totalPoints = 0;
		foreach ( $cards as $card ) {
			$totalPoints += $this->getCardPointValue( $card );
		}
		$cardCount    = count( $cards );
		$totalPoints -= intdiv( $cardCount, 3 ) * 2;
		if ( $cardCount % 3 !== 0 ) {
			$totalPoints -= 1;
		}
		return $totalPoints;
	}

	function countScoresForTeams( $teamCards ) {
		$scores = array();
		foreach ( $teamCards as $team => $cards ) {
			$scores[ $team ] = $this->countScoresForPlayer( $cards );
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

	function getCardsBasedOnColorValue( $deck, $cards ) {
		$returnCards = array();
		foreach ( $cards as $card ) {
			foreach ( $deck as $deckCard ) {
				if ( $deckCard['type'] == $card['color'] && $deckCard['type_arg'] == $card['value'] ) {
					$returnCards[] = $deckCard;
					continue;
				}
			}
		}
		return $returnCards;
	}

	function updateScores() {
		$newScores = self::getCollectionFromDb( 'SELECT player_id, player_score FROM player', true );
		self::notifyAllPlayers( 'newScores', '', array( 'newScores' => $newScores ) );
	}

	function updateRadli() {
		$newRadli = self::getCollectionFromDb( 'SELECT player_id, player_radli FROM player', true );
		self::notifyAllPlayers( 'newRadli', '', array( 'newRadli' => $newRadli ) );
	}

	function highestValuePlayed( $currentTrickColor ) {
		$cardsOnTable = $this->cards->getCardsInLocation( 'cardsontable' );

		$highestValuePlayed = 0;
		foreach ( $cardsOnTable as $card ) {
			if ( $card['type'] == $currentTrickColor ) {
				$highestValuePlayed = max( $highestValuePlayed, $card['type_arg'] );
			}
			if ( $card['type'] == SUIT_TRUMP ) {
				$highestValuePlayed = max( $highestValuePlayed, $card['type_arg'] + 14 );
			}
		}
		return $highestValuePlayed;
	}

	function highestValueInHand( $cardsInHand, $currentTrickColor ) {
		$highestValueInHand = 0;
		foreach ( $cardsInHand as $card ) {
			if ( $card['type'] == $currentTrickColor ) {
				$cardValue = $card['type_arg'];
				if ( $currentTrickColor == SUIT_TRUMP ) {
					$cardValue += 14;
				}
				$highestValueInHand = max( $highestValueInHand, $cardValue );
			}
		}
		if ( ! $highestValueInHand ) {
			foreach ( $cardsInHand as $card ) {
				if ( $card['type'] == SUIT_TRUMP ) {
					$highestValueInHand = max( $highestValueInHand, $card['type_arg'] + 14 );
				}
			}
		}
		return $highestValueInHand;
	}

	function countSuitInHand( $playerId, $suit ) {
		$cardsInHand  = $this->cards->getCardsInLocation( 'hand', $playerId );
		$trumpsInHand = 0;
		foreach ( $cardsInHand as $card ) {
			if ( $card['type'] == $suit ) {
				$trumpsInHand++;
			}
		}
		return $trumpsInHand;
	}

	function isCardOnTable( $color, $value ) {
		$cardsOnTable = $this->cards->getCardsInLocation( 'cardsontable' );
		foreach ( $cardsOnTable as $card ) {
			if ( $card['type'] == $color && $card['type_arg'] == $value ) {
				return true;
			}
		}
		return false;
	}

	function checkAvoidanceRules( $currentCard, $currentTrickColor, $playerId ) {
		$cardsInHand = $this->cards->getCardsInLocation( 'hand', $playerId );

		$highestValuePlayed = $this->highestValuePlayed( $currentTrickColor );
		$highestValueInHand = $this->highestValueInHand( $cardsInHand, $currentTrickColor );
		$currentCardValue   = $currentCard['type_arg'];
		if ( $currentCard['type'] == SUIT_TRUMP ) {
			$currentCardValue += 14;
		}
		self::trace( "hvp: $highestValuePlayed hvi: $highestValueInHand ccv: $currentCardValue " );
		if ( $highestValueInHand > $highestValuePlayed && $currentCardValue < $highestValuePlayed ) {
			throw new BgaUserException( 'You must beat the highest card on the table!' );
		}

		if ( $currentCard['type'] == SUIT_TRUMP && $currentCard['type_arg'] == 1 ) {
			// You can only play Pagat if:
			// - It's your only card.
			// - You have no other trump cards.
			// - It's an Emperor's trick.
			$pagatAllowed = false;
			if ( $this->countSuitInHand( $playerId, SUIT_TRUMP ) == 1 ) {
				$pagatAllowed = true;
			}
			if ( count( $cardsInHand ) == 1 ) {
				$pagatAllowed = true;
			}
			if ( $this->isCardOnTable( SUIT_TRUMP, 22 ) && $this->isCardOnTable( SUIT_TRUMP, 21 ) ) {
				$pagatAllowed = true;
			}
			if ( ! $pagatAllowed ) {
				throw new BgaUserException( 'You can only play Pagat if you have no other trump cards, or if it\'s an Emperor\'s trick.' );
			}
		}
	}

	function checkNormalRules( $currentCard, $currentTrickColor, $playerId, $zombie = false ) {
		$cardsInHand = $this->cards->getCardsInLocation( 'hand', $playerId );

		if ( $currentTrickColor > 0 ) {
			if ( $this->haveColorInHand( $currentTrickColor, $cardsInHand )
				&& intval( $currentCard['type'] ) !== $currentTrickColor ) {
				if ( $zombie ) {
					return false;
				} else {
					throw new BgaUserException( self::_( 'You must play a ' ) . $this->colors[ $currentTrickColor ]['name'] . '.' );
				}
			}
			if ( $currentTrickColor !== SUIT_TRUMP
				&& intval( $currentCard['type'] ) !== SUIT_TRUMP
				&& ! $this->haveColorInHand( $currentTrickColor, $cardsInHand )
				&& $this->haveColorInHand( SUIT_TRUMP, $cardsInHand ) ) {
				if ( $zombie ) {
					return false;
				} else {
					throw new BgaUserException( self::_( 'You must play a ' ) . $this->colors[ SUIT_TRUMP ]['name'] . '.' );
				}
			}
		}

		return true;
	}

	function checkUltimo( $currentCard, $playerId ) {
		$pagatUltimo = self::getGameStateValue( 'pagatUltimoPlayer' );

		if ( $pagatUltimo == $playerId
			&& $currentCard['type'] == SUIT_TRUMP
			&& $currentCard['type_arg'] == 1
			&& $this->countSuitInHand( $playerId, SUIT_TRUMP ) > 1
			) {
				throw new BgaUserException( self::_( 'You have announced pagat ultimo. You can only play Pagat if you have no other trump cards.' ) );
		}

		$kingUltimo = self::getGameStateValue( 'kingUltimoPlayer' );
		$kingSuit   = self::getGameStateValue( 'calledKing' );

		if ( $kingUltimo == $playerId
			&& $currentCard['type'] == $kingSuit
			&& $currentCard['type_arg'] == 14
			&& $this->countSuitInHand( $playerId, $kingSuit ) > 1
			) {
				throw new BgaUserException( self::_( 'You have announced king ultimo. You can only play the king if it\'s your only option.' ) );
		}
	}

	function beggarCountingAndScoring() {
		$players    = self::loadPlayersBasicInfos();
		$declarer   = self::getGameStateValue( 'declarer' );
		$failed     = self::getGameStateValue( 'tricksByDeclarer' );
		$currentBid = self::getGameStateValue( 'highBid' );

		$points = $this->radliAdjustment( $this->bid_point_values[ $currentBid ], $declarer );

		if ( $failed ) {
			$sql = "UPDATE player SET score = score - $points WHERE player_id = $declarer";
			self::notifyAllPlayers(
				'score',
				clienttranslate( '${player_name} failed and lost ${points} points.' ),
				array(
					'player_name' => $players[ $declarer ]['player_name'],
					'points'      => $points,
				)
			);
			$this->incStat( 1, 'hands_lost', $declarer );
		} else {
			$sql = "UPDATE player SET score + score - $points WHERE player_id = $declarer";
			self::notifyAllPlayers(
				'score',
				clienttranslate( '${player_name} succeeded and won ${points} points.' ),
				array(
					'player_name' => $players[ $declarer ]['player_name'],
					'points'      => $points,
				)
			);
			$this->removeRadli( $declarer, $players[ $declarer ]['player_name'] );
			$this->incStat( 1, 'beggars_won', $declarer );
			$this->incStat( 1, 'hands_won', $declarer );
		}
	}

	function valatCountingAndScoring() {
		$players    = self::loadPlayersBasicInfos();
		$declarer   = self::getGameStateValue( 'declarer' );
		$tricks     = self::getGameStateValue( 'tricksByDeclarer' );
		$currentBid = self::getGameStateValue( 'highBid' );

		$points = $this->radliAdjustment( $this->bid_point_values[ $currentBid ], $declarer );

		if ( $tricks < HAND_SIZE ) {
			$sql = "UPDATE player SET score = score - $points WHERE player_id = $declarer";
			self::notifyAllPlayers(
				'score',
				clienttranslate( '${player_name} failed and lost ${points} points.' ),
				array(
					'player_name' => $players[ $declarer ]['player_name'],
					'points'      => $points,
				)
			);
			$this->incStat( 1, 'hands_lost', $declarer );
		} else {
			$sql = "UPDATE player SET score + score - $points WHERE player_id = $declarer";
			self::notifyAllPlayers(
				'score',
				clienttranslate( '${player_name} succeeded and won ${points} points.' ),
				array(
					'player_name' => $players[ $declarer ]['player_name'],
					'points'      => $points,
				)
			);
			$this->removeRadli( $declarer, $players[ $declarer ]['player_name'] );
			$this->incStat( 1, 'valats_won', $declarer );
			$this->incStat( 1, 'hands_won', $declarer );
		}
	}

	function klopCountingAndScoring() {
		$players = self::loadPlayersBasicInfos();
		$winners = array();
		$loser   = 0;
		$scores  = array();
		foreach ( $players as $player_id => $player ) {
			$playerCards = $this->cards->getCardsInLocation( "cardswon", $player_id );
			$playerScore = $this->countScoresForPlayer( $playerCards );
			if ( $playerScore > 35 ) {
				$loser = $player_id;
			}
			if ( $playerScore == 0 ) {
				$winners[] = $player_id;
			}
			$scores[ $player_id ] = $playerScore;
			self::notifyAllPlayers(
				'score',
				clienttranslate( '${player_name} scores ${score} card points.' ),
				array(
					'player_name' => $player['player_name'],
					'score'       => $playerScore,
				)
			);
		}
		if ( $loser ) {
			$points = $this->radliAdjustment( $this->bid_point_values[ BID_KLOP ], $loser );
			$sql    = "UPDATE player SET player_score=player_score-$points  WHERE player_id='$loser'";
			self::DbQuery($sql);
			self::notifyAllPlayers(
				'points',
				clienttranslate( '${player_name} loses ${points} points.' ),
				array (
					'player_name' => $players[ $loser ][ 'player_name' ],
					'points'      => $points,
				)
			);
			$this->incStat( 1, 'hands_lost', $loser );
		}
		if ( count ( $winners ) > 0 ) {
			foreach ( $winners as $winner_id ) {
				$points = $this->radliAdjustment( $this->bid_point_values[ BID_KLOP ], $winner_id );
				$sql    = "UPDATE player SET player_score=player_score+$points  WHERE player_id='$winner_id'";
				self::DbQuery($sql);
				self::notifyAllPlayers(
					'points',
					clienttranslate( '${player_name} wins ${points} points.' ),
					array (
						'player_name' => $players[ $winner_id ][ 'player_name' ],
						'points'      => $points,
					)
				);
				$this->removeRadli( $declarer, $players[ $winner_id ][ 'player_name' ] );
				$this->incStat( 1, 'hands_won', $winner_id );
			}
		}
		if ( ! $loser && count( $winners ) == 0 ) {
			foreach ( $scores as $player_id => $score ) {
				$points = $this->radliAdjustment( $this->roundToNearestFive( $score ), $player_id );
				$sql    = "UPDATE player SET player_score=player_score-$points  WHERE player_id='$player_id'";
				self::DbQuery($sql);
				self::notifyAllPlayers(
					'points',
					clienttranslate( '${player_name} loses ${points} points.' ),
					array (
						'player_name' => $players[ $player_id ][ 'player_name' ],
						'points'      => $points,
					)
				);

			}
		}
	}

	function regularCountingAndScoring() {
		$teamCards       = array();
		$declarer        = intval( self::getGameStateValue( 'declarer' ) );
		$declarerTeam    = array( $declarer );
		$declarerPartner = intval( self::getGameStateValue( 'declarerPartner' ) );
		$currentBid      = intval( self::getGameStateValue( 'highBid' ) );

		if ( $declarerPartner && $declarerPartner !== $declarer ) {
			$declarerTeam[] = $declarerPartner;
		}

		$cards = $this->cards->getCardsInLocation( "cardswon" );
		foreach ( $cards as $card ) {
			$team = in_array( $card['location_arg'], $declarerTeam ) ? 'Declarer' : 'Opponents';

			$teamCards[ $team ][] = $card;
		}
		$opponentCards          = $this->cards->getCardsInLocation('opponents');
		$teamCards['Opponents'] = array_merge( $teamCards['Opponents'], $opponentCards );

		$teamScores = $this->countScoresForTeams( $teamCards );

		foreach ( $teamScores as $team => $score ) {
			self::notifyAllPlayers(
				'score',
				clienttranslate( '${team} team scores ${score} card points.' ),
				array(
					'team'  => $team,
					'score' => $score,
				)
			);
		}

		$points = $this->bid_point_values[ $currentBid ];
		if ( $currentBid >= BID_THREE && $currentBid <= BID_SOLO_ONE ) {
			$difference = abs( $teamScores['Declarer'] - 35 );
			$points    += $this->roundToNearestFive( $difference );
		}

		$points *= intval( self::getGameStateValue( 'gameValue' ) );

		if ( $teamScores['Declarer'] <= 35 ) {
			$points = -$points;
			$this->incStat( 1, 'hands_lost', $declarer );
		}

		$points = $this->scoreBonuses( $points, $teamCards );

		$points = $this->radliAdjustment( $points, $declarer );

		if ( $teamScores['Declarer'] > 35 ) {
			$players = self::loadPlayersBasicInfos();
			$this->removeRadli( $declarer, $players[ $declarer ][ 'player_name' ] );
			$this->incStat( 1, 'hands_won', $declarer );
			if ( $currentBid >= BID_SOLO_THREE && $currentBid <= BID_SOLO_ONE ) {
				$this->incStat( 1, 'solos_won', $declarer );
			}
		}

		$this->adjustPoints( $points, $declarer, $declarerPartner );
	}

	function hasTrula( $cards ) {
		$trulaCards = 0;
		foreach ( $cards as $card ) {
			if ( $card['type'] == SUIT_TRUMP
				&& in_array( intval( $card['type_arg'] ), array( 1, 21, 22 ) ) ) {
				$trulaCards++;
			}
		}
		return $trulaCards == 3;
	}

	function hasKings( $cards ) {
		$kings = 0;
		foreach ( $cards as $card ) {
			if ( $card['type_arg'] == '14' ) {
				$kings++;
			}
		}
		return $kings == 4;
	}

	function adjustPoints( $points, $declarer, $declarerPartner = 0 ) {
		if ( $points == 0 ) {
			return;
		}

		if ( $points > 0 ) {
			$notification = clienttranslate( 'Declarer gains ${points} points' );
			if ( $declarerPartner ) {
				$notification = clienttranslate( 'Declarer\'s team gains ${points} points in total' );
			}
		} else {
			$notification = clienttranslate( 'Declarer loses ${points} points' );
			if ( $declarerPartner ) {
				$notification = clienttranslate( 'Declarer\'s team loses ${points} points in total' );
			}
		}

		$sql = "UPDATE player SET player_score=player_score+$points  WHERE player_id='$declarer'";
		self::DbQuery($sql);

		$declarerScore = self::getUniqueValueFromDb(
			"SELECT player_score FROM player WHERE player_id='$declarer'"
		);

		$partnerScore = -1;

		if ( $declarerPartner ) {
			$sql = "UPDATE player SET player_score=player_score+$points  WHERE player_id='$declarerPartner'";
			self::DbQuery($sql);

			$partnerScore = self::getUniqueValueFromDb(
				"SELECT player_score FROM player WHERE player_id='$declarerPartner'"
			);
		}

		self::notifyAllPlayers(
			'points',
			$notification,
			array ( 'points' => $points )
		);

		if ( intval( $declarerScore ) === 0 || intval( $partnerScore ) === 0 ) {
			self::notifyAllPlayers(
				'points',
				clienttranslate( 'Next round is a compulsory klop because a score is exactly 0.', ),
				array()
			);
			self::setGameStateValue( 'compulsoryKlop', 1 );
		}
	}

	function roundToNearestFive( $number ) {
		return floor( $number / 5 ) * 5;
	}

	function determineTrickWinner( $trickCount ) {
		$players = self::loadPlayersBasicInfos();

		$currentBid = self::getGameStateValue( 'highBid' );

		list(
			'bestValue'         => $bestValue,
			'bestValuePlayerId' => $bestValuePlayerId,
			'bestValueIsTrump'  => $bestValueIsTrump,
			'pagatPlayer'       => $pagatPlayer,
			'mondPlayer'        => $mondPlayer,
			'kingPlayer'        => $kingPlayer,
		) = $this->analyzeTrick( $currentBid );

		$this->incStat( 1, 'tricks_taken', $bestValuePlayerId );

		$bestValuePlayerId = $this->checkForEmperorsTrick( $pagatPlayer, $mondPlayer, $bestValuePlayerId, $players );
		$this->checkForMondCapture( $currentBid, $mondPlayer, $bestValuePlayerId, $players );

		if ( $trickCount == HAND_SIZE ) {
			$this->checkUltimoStatus( $pagatPlayer, $kingPlayer, $bestValuePlayerId, $players );
		}

		$this->gamestate->changeActivePlayer( $bestValuePlayerId );
		$this->cards->moveAllCardsInLocation( 'cardsontable', 'cardswon', null, $bestValuePlayerId );

		if ( $kingPlayer ) {
			$this->maybeRevealIdentity( 'King', '', $kingPlayer );
		}
		$kingChosen = intval( self::getGameStateValue( 'calledKingChosen' ) );
		if ( $bestValuePlayerId === $kingPlayer && $kingChosen ) {
			$this->cards->moveAllCardsInLocation( 'talon', 'cardswon', null, $bestValuePlayerId );
			self::notifyAllPlayers(
				'calledKingWon',
				clienttranslate( '${player_name} collects the talon cards for winning the trick with the called king.' ),
				array ( 'player_name' => $players[ $bestValuePlayerId ][ 'player_name' ] )
			);
		}
		if ( $kingPlayer && $kingPlayer !== $bestValuePlayerId && $kingChosen ) {
			$this->cards->moveAllCardsInLocation( 'talon', 'opponents' );
			self::notifyAllPlayers(
				'calledKingLost',
				clienttranslate( '${player_name} loses the trick with the called king, talon cards go to the opponent.' ),
				array ( 'player_name' => $players[ $kingPlayer ][ 'player_name' ] )
			);
		}

		$declarer = intval( self::getGameStateValue( 'declarer' ) );
		if ( $declarer == $bestValuePlayerId ) {
			self::incGameStateValue( 'tricksByDeclarer', 1 );
		}

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

		if ( $currentBid == BID_KLOP ) {
			$this->dealVitamins( $bestValuePlayerId, $players );
		}

		return $bestValuePlayerId;
	}

	function analyzeTrick( $currentBid ) {
		$currentTrickColor = intval( self::getGameStateValue( 'trickColor' ) );
		$cardsOnTable      = $this->cards->getCardsInLocation( 'cardsontable' );
		$bestValue         = 0;
		$bestValuePlayerId = null;
		$bestValueIsTrump  = false;
		$kingColor         = intval( self::getGameStateValue( 'calledKing' ) );

		$mondPlayer  = 0;
		$pagatPlayer = 0;
		$kingPlayer  = 0;

		foreach ( $cardsOnTable as $card ) {
			$cardValue = $card['type_arg'];
			$cardColor = intval( $card['type'] );
			if ( $cardValue == 1 && $cardColor == SUIT_TRUMP ) {
				$pagatPlayer = $card['location_arg'];
			}
			if ( $cardValue == 21 && $cardColor == SUIT_TRUMP ) {
				$mondPlayer = $card['location_arg'];
			}
			if ( $cardValue == 14 && $cardColor == $kingColor ) {
				$kingPlayer = $card['location_arg'];
			}
			if ( $cardColor === $currentTrickColor && ! $bestValueIsTrump ) {
				if ( $cardValue > $bestValue ) {
					$bestValue         = $cardValue;
					$bestValuePlayerId = $card['location_arg'];
				}
			}
			if ( $cardColor == SUIT_TRUMP && ! in_array( $currentBid, array( BID_COLOUR_VALAT, BID_COLOUR_VALAT_WITHOUT ) ) ) {
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

		return array(
			'bestValue'         => $bestValue,
			'bestValuePlayerId' => $bestValuePlayerId,
			'bestValueIsTrump'  => $bestValueIsTrump,
			'pagatPlayer'       => $pagatPlayer,
			'mondPlayer'        => $mondPlayer,
			'kingPlayer'        => $kingPlayer,
		);
	}

	function checkForEmperorsTrick( $pagatPlayer, $mondPlayer, $bestValuePlayerId, $players ) {
		if ( $pagatPlayer > 0 && $mondPlayer > 0 && $mondPlayer !== $bestValuePlayerId ) {
			$bestValuePlayerId = $pagatPlayer;
			self::notifyAllPlayers(
				'emperorsTrick',
				clienttranslate( '${player_name} got the Emperor\'s trick!' ),
				array(
					'player_id'   => $bestValuePlayerId,
					'player_name' => $players[ $bestValuePlayerId ]['player_name'],
				)
			);
			$this->incStat( 1, 'emperor_tricks_taken', $bestValuePlayerId );
		}
		return $bestValuePlayerId;
	}

	function checkForMondCapture( $currentBid, $mondPlayer, $bestValuePlayerId, $players ) {
		if ( in_array( $currentBid, array( BID_THREE, BID_TWO, BID_ONE, BID_SOLO_THREE, BID_SOLO_TWO, BID_SOLO_ONE, BID_SOLO_WITHOUT ) )
		&& $mondPlayer > 0 && $mondPlayer !== $bestValuePlayerId ) {
			$mond_penalty = CAPTURED_MOND_PENALTY;
			$sql          = "UPDATE player SET player_score=player_score-$mond_penalty  WHERE player_id='$mondPlayer'";
			self::DbQuery($sql);
			self::notifyAllPlayers(
				'capturedMond',
				clienttranslate( '${player_name} captured the Mond!' ),
				array(
					'player_id'   => $bestValuePlayerId,
					'player_name' => $players[ $bestValuePlayerId ]['player_name'],
				)
			);
			$this->updateScores();
			$this->incStat( 1, 'monds_captured', $bestValuePlayerId );
			$this->incStat( 1, 'monds_lost', $mondPlayer );
		}
	}

	function dealVitamins( $bestValuePlayerId, $players ) {
		if ( $this->cards->countCardsInLocation( 'talon' ) > 0 ) {
			$vitamin = $this->cards->pickCardForLocation( 'talon', 'cardswon', $bestValuePlayerId );
			self::trace("NotifyAllPlayers: vitamin");
			self::notifyAllPlayers(
				'vitamin',
				clienttranslate( '${player_name} got some vitamins: ${card_suit_symbol}${card_display_value}' ),
				array(
					'player_id'          => $bestValuePlayerId,
					'player_name'        => $players[ $bestValuePlayerId ]['player_name'],
					'card_display_value' => $this->getCardDisplayValue( $vitamin['type'], $vitamin['type_arg'] ),
					'card_suit_symbol'   => $this->getCardSuitSymbol( $vitamin['type'] ),
					'color'              => $vitamin['type'],
					'value'              => $vitamin['type_arg'],
				)
			);
			self::trace("NotifyAllPlayers: giveVitamin to $bestValuePlayerId");
			self::notifyAllPlayers(
				'giveVitamin',
				'',
				array(
					'player_id' => $bestValuePlayerId,
				)
			);
		}
	}

	function checkBeggarAndValat( $currentBid ) {
		self::trace( 'checkBeggarAndValat' );

		$declarer         = self::getGameStateValue( 'declarer' );
		$trickCount       = self::getGameStateValue( 'trickCount' );
		$tricksByDeclarer = self::getGameStateValue( 'tricksByDeclarer' );
		$valatPlayer      = self::getGameStateValue( 'valatPlayer' );

		self::trace( "checkBeggarAndValat: declarer=$declarer, trickCount=$trickCount, tricksByDeclarer=$tricksByDeclarer, valatPlayer=$valatPlayer " );
		if ( $currentBid == BID_VALAT || $valatPlayer == $declarer ) {
			if ( $tricksByDeclarer < $trickCount ) {
				self::notifyAllPlayers(
					'valatFailed',
					clienttranslate( 'The declarer missed a trick! Valat failed!' ),
					array()
				);
				return true;
			}
			return false;
		}

		if ( $valatPlayer ) {
			if ( $tricksByDeclarer > 0 ) {
				self::notifyAllPlayers(
					'valatFailed',
					clienttranslate( 'The valat player missed a trick! Valat failed!' ),
					array()
				);
				return true;
			}
			return false;
		}

		// Beggars:
		if ( $tricksByDeclarer > 0 ) {
			self::notifyAllPlayers(
				'beggarFailed',
				clienttranslate( 'The declarer took a trick! Beggar failed!' ),
				array()
			);
			return true;
		}

		return false;
	}

	function increaseBidStat( $highBid ) {
		$statToInc = '';
		switch ( intval( $highBid ) ) {
			case BID_KLOP:
				$statToInc = 'bid_klop';
				break;
			case BID_THREE:
				$statToInc = 'bid_three';
				break;
			case BID_TWO:
				$statToInc = 'bid_two';
				break;
			case BID_ONE:
				$statToInc = 'bid_one';
				break;
			case BID_SOLO_THREE:
				$statToInc = 'bid_solo_three';
				break;
			case BID_SOLO_TWO:
				$statToInc = 'bid_solo_two';
				break;
			case BID_SOLO_ONE:
				$statToInc = 'bid_solo_one';
				break;
			case BID_BEGGAR:
				$statToInc = 'bid_beggar';
				break;
			case BID_SOLO_WITHOUT:
				$statToInc = 'bid_solo_without';
				break;
			case BID_OPEN_BEGGAR:
				$statToInc = 'bid_open_beggar';
				break;
			case BID_COLOUR_VALAT_WITHOUT:
				$statToInc = 'bid_colour_valat_without';
				break;
			case BID_COLOUR_VALAT:
				$statToInc = 'bid_colour_valat';
				break;
			case BID_VALAT:
				$statToInc = 'bid_valat';
				break;
		}
		if ( $statToInc ) {
			$this->incStat( 1, $statToInc );
		}
	}

	/**
	 * Checks if player identity should be revealed after an announcement.
	 *
	 * Also checks if the revelation causes extra revelations.
	 */
	public function maybeRevealIdentity( $announcement, $currentValue, $playerId ) {
		$reveal = false;

		if ( $announcement == 'King' ) {
			// Playing the called king reveals identity.
			$reveal = true;
		}

		if ( $currentValue > ANNOUNCEMENT_BASIC ) {
			// Any kontra announcement reveals identity.
			$reveal = true;
		}

		if ( $announcement == ANNOUNCEMENT_KINGULTIMO ) {
			// King ultimo announcement reveals identity.
			$reveal = true;
		}

		if ( $reveal ) {
			$team = $this->getPlayerTeam( $playerId );
			$sql  = "UPDATE player SET player_identity = '$team' WHERE player_id = $playerId";
			self::DbQuery( $sql );

			if ( $team == 'declarer' ) {
				// Declarer's partner revealed, reveal opponents.
				$sql = "UPDATE player SET player_identity = 'opponent' WHERE player_identity != 'declarer'";
				self::DbQuery( $sql );
			}

			if ( $team == 'opponent' ) {
				$sql   = "SELECT COUNT(*) FROM player WHERE player_identity = 'opponent_hidden'";
				$count = self::getUniqueValueFromDb( $sql );
				if ( $count == 0 ) {
					// All opponents revealed, reveal declarer's partner.
					$sql = "UPDATE player SET player_identity = 'declarer' WHERE player_identity = 'declarer_hidden'";
					self::DbQuery( $sql );
				}
			}

			$this->updatePlayerData();
		}
	}

	public function updatePlayerData( $message = '', $player_name = '' ) {
		self::trace( "updatePlayerData: '$message' - '$player_name' " );

		// TODO: Bad data gets transmitted here.

		$players   = $this->getPlayerCollection();
		$arguments = array( 'players' => $players );
		if ( $player_name ) {
			$arguments['player_name'] = $player_name;
		}
		self::notifyAllPlayers(
			'playerDataUpdate',
			$message,
			$arguments
		);
	}

	public function scoreBonuses( $gamePoints, $teamCards ) {
		$trulaPlayer       = intval( self::getGameStateValue( 'trulaPlayer' ) );
		$kingsPlayer       = intval( self::getGameStateValue( 'kingsPlayer' ) );
		$kingUltimoPlayer  = intval( self::getGameStateValue( 'kingUltimoPlayer' ) );
		$pagatUltimoPlayer = intval( self::getGameStateValue( 'pagatUltimoPlayer' ) );
		$valatPlayer       = intval( self::getGameStateValue( 'valatPlayer' ) );
		$tricksWon         = intval( self::getGameStateValue( 'tricksByDeclarer' ) );

		$points = 0;

		if ( $tricksWon == HAND_SIZE || $tricksWon == 0 || $valatPlayer > 0 ) {
			$valatTeam = $valatPlayer ? $this->getPlayerTeam( $valatPlayer ) : null;
			$points    = $this->announcements[ ANNOUNCEMENT_VALAT ]['points'];

			if ( ! $valatPlayer ) {
				if ( $tricksWon == 0 ) {
					$points = -$points;
				}
			} else {
				$points = $points * 2;
			}

			if ( $tricksWon < HAND_SIZE && $valatTeam == 'declarer' ) {
				$points = -$points;
			}

			if ( $tricksWon == 0 && $valatTeam == 'opponent' ) {
				$points = -$points;
			}

			$points = $points * self::getGameStateValue( 'valatValue' );

			self::notifyAllPlayers(
				'bonusPoints',
				clienttranslate( 'The declarer gets ${points} points for valat.' ),
				array( 'points' => $points )
			);

			// If valat, no more bonuses and discard the game points.
			return $points;
		}

		self::notifyAllPlayers(
			'gamePoints',
			clienttranslate( 'The declarer scores ${points} points for game.' ),
			array( 'points' => $gamePoints )
		);

		$declarerHasTrula   = $this->hasTrula( $teamCards['Declarer'] );
		$opponentsHaveTrula = $this->hasTrula( $teamCards['Opponents'] );

		if ( $declarerHasTrula || $opponentsHaveTrula || $trulaPlayer > 0 ) {
			$trulaTeam   = $trulaPlayer ? $this->getPlayerTeam( $trulaPlayer ) : null;
			$trulaPoints = $this->announcements[ ANNOUNCEMENT_TRULA ]['points'];

			if ( ! $trulaPlayer ) {
				if ( $opponentsHaveTrula ) {
					$trulaPoints = -$trulaPoints;
				}
			} else {
				$trulaPoints = $trulaPoints * 2;
			}

			if ( $opponentsHaveTrula ) {
				$trulaPoints = -$trulaPoints;
			}

			if ( $trulaTeam == 'declarer' && ! $declarerHasTrula ) {
				$trulaPoints = -$trulaPoints;
			}

			$trulaPoints = $trulaPoints * self::getGameStateValue( 'trulaValue' );
			$points     += $trulaPoints;

			self::notifyAllPlayers(
				'bonusPoints',
				clienttranslate( 'The declarer gets ${points} points for trula.' ),
				array( 'points' => $trulaPoints	)
			);
		}

		$declarerHasKings   = $this->hasKings( $teamCards['Declarer'] );
		$opponentsHaveKings = $this->hasKings( $teamCards['Opponents'] );

		if ( $declarerHasKings || $opponentsHaveKings || $kingsPlayer > 0 ) {
			$kingsTeam   = $kingsPlayer ? $this->getPlayerTeam( $kingsPlayer ) : null;
			$kingsPoints = $this->announcements[ ANNOUNCEMENT_KINGS ]['points'];

			if ( ! $kingsPlayer ) {
				if ( $opponentsHaveKings ) {
					$kingsPoints = -$kingsPoints;
				}
			} else {
				$kingsPoints = $kingsPoints * 2;
			}

			if ( $opponentsHaveKings ) {
				$kingsPoints = -$kingsPoints;
			}

			if ( $kingsTeam == 'declarer' && ! $declarerHasKings ) {
				$kingsPoints = -$kingsPoints;
			}

			$kingsPoints = $kingsPoints * self::getGameStateValue( 'kingsValue' );
			$points     += $kingsPoints;

			self::notifyAllPlayers(
				'bonusPoints',
				clienttranslate( 'The declarer gets ${points} points for kings.' ),
				array( 'points' => $kingsPoints	)
			);
		}

		$pagatUltimoStatus = self::getGameStateValue( 'pagatUltimoStatus' );

		if ( $pagatUltimoStatus ) {
			$pagatUltimoTeam = $pagatUltimoPlayer ? $this->getPlayerTeam( $pagatUltimoPlayer ) : null;
			$pagatPoints     = $this->announcements[ ANNOUNCEMENT_PAGATULTIMO ]['points'];

			if ( $pagatUltimoTeam == 'declarer' && $pagatUltimoStatus == BONUS_FAILURE ) {
				$pagatPoints = -$pagatPoints;
			}

			if ( $pagatUltimoTeam == 'opponent' && $pagatUltimoStatus == BONUS_SUCCESS ) {
				$pagatPoints = -$pagatPoints;
			}

			if ( $pagatUltimoPlayer ) {
				$pagatPoints = $pagatPoints * 2;
			}

			$points += $pagatPoints;

			self::notifyAllPlayers(
				'bonusPoints',
				clienttranslate( 'The declarer gets ${points} points for pagat ultimo.' ),
				array( 'points' => $pagatPoints	)
			);
		}

		$kingUltimoStatus = self::getGameStateValue( 'kingUltimoStatus' );

		if ( $kingUltimoStatus ) {
			$kingUltimoTeam = $kingUltimoPlayer ? $this->getPlayerTeam( $kingUltimoPlayer ) : null;
			$kingPoints     = $this->announcements[ ANNOUNCEMENT_KINGULTIMO ]['points'];

			if ( $kingUltimoTeam == 'declarer' && $kingUltimoStatus == BONUS_FAILURE ) {
				$kingPoints = -$kingPoints;
			}

			if ( $kingUltimoTeam == 'opponent' && $kingUltimoStatus == BONUS_SUCCESS ) {
				$kingPoints = -$kingPoints;
			}

			if ( $kingUltimoPlayer ) {
				$kingPoints = $kingPoints * 2;
			}

			$points += $kingPoints;

			self::notifyAllPlayers(
				'bonusPoints',
				clienttranslate( 'The declarer gets ${points} points for king ultimo.' ),
				array( 'points' => $kingPoints )
			);
		}

		return $gamePoints + $points;
	}

	/**
	 * When valat is announced, lower announcements are cleared.
	 */
	public function clearAnnouncements() {
		self::setGameStateValue( 'gameValue', 1 );
		self::setGameStateValue( 'trulaValue', 0 );
		self::setGameStateValue( 'trulaPlayer', 0 );
		self::setGameStateValue( 'kingsValue', 0 );
		self::setGameStateValue( 'kingsPlayer', 0 );
		self::setGameStateValue( 'kingUltimoValue', 0 );
		self::setGameStateValue( 'kingUltimoPlayer', 0 );
		self::setGameStateValue( 'pagatUltimoValue', 0 );
		self::setGameStateValue( 'pagatUltimoPlayer', 0 );
	}

	public function getPlayerTeam( $player_id ) {
		$sql    = "SELECT player_identity FROM player WHERE player_id = $player_id";
		$result = self::getUniqueValueFromDB( $sql );
		return str_replace( '_hidden', '', $result );
	}

	public function playerIdentityHidden( $player_id ) {
		$sql    = "SELECT player_identity FROM player WHERE player_id = $player_id";
		$result = self::getUniqueValueFromDB( $sql );
		return strpos( $result, '_hidden' ) !== false;
	}

	public function setPlayerTeam( $player_id, $team, $hidden ) {
		if ( $hidden ) {
			$team .= '_hidden';
		}
		$sql = "UPDATE player SET player_identity = '$team' WHERE player_id = $player_id";
		self::DbQuery( $sql );
	}

	public function getPlayerCollection() {
		$sql = 'SELECT player_id id, player_score score, player_radl radl, player_identity team FROM player ';
		return self::getCollectionFromDb( $sql );
	}

	public function checkUltimoStatus( $pagatPlayer, $kingPlayer, $bestValuePlayerId, $players ) {
		self::trace( 'checkUltimoStatus' );

		self::trace( "pagatPlayer: $pagatPlayer - kingPlayer: $kingPlayer - bestValuePlayerId: $bestValuePlayerId " );

		if ( $pagatPlayer == $bestValuePlayerId ) {
			self::setGameStateValue( 'pagatUltimoStatus', BONUS_SUCCESS );
			self::notifyAllPlayers(
				'pagatUltimo',
				clienttranslate( '${player_name} won the pagat ultimo bonus' ),
				array(
					'player_name' => $players[ $pagatPlayer ]['player_name'],
				)
			);
		} elseif ( $pagatPlayer ) {
			self::setGameStateValue( 'pagatUltimoStatus', BONUS_FAILURE );
			self::notifyAllPlayers(
				'pagatUltimo',
				clienttranslate( '${player_name} lost the pagat ultimo bonus' ),
				array(
					'player_name' => $players[ $pagatPlayer ]['player_name'],
				)
			);
		}

		if ( $kingPlayer ) {
			if ( $this->getPlayerTeam( $kingPlayer ) == $this->getPlayerTeam( $bestValuePlayerId ) ) {
				self::setGameStateValue( 'kingUltimoStatus', BONUS_SUCCESS );
				self::notifyAllPlayers(
					'kingUltimo',
					clienttranslate( '${player_name} won the king ultimo bonus' ),
					array(
						'player_name' => $players[ $kingPlayer ]['player_name'],
					)
				);
			} else {
				self::setGameStateValue( 'kingUltimoStatus', BONUS_FAILURE );
				self::notifyAllPlayers(
					'kingUltimo',
					clienttranslate( '${player_name} lost the king ultimo bonus' ),
					array(
						'player_name' => $players[ $kingPlayer ]['player_name'],
					)
				);
			}
		}
	}

	public function addRadli() {
		$sql = "UPDATE player SET player_radl = player_radl + 1";
		self::DbQuery( $sql );
	}

	public function getRadli( $playerId ) {
		$sql    = "SELECT player_radl FROM player WHERE player_id = $playerId";
		$result = intval( self::getUniqueValueFromDB( $sql ) );
		return $result;
	}

	public function removeRadli( $playerId, $playerName ) {
		if ( $this->getRadli( $playerId ) > 0 ) {
			$sql = "UPDATE player SET player_radl = player_radl - 1 WHERE player_id = $playerId";
			self::DbQuery( $sql );
			$this->updatePlayerData( clienttranslate( '${player_name} cancels one radlc.' ), $playerName );
			$this->incStat( 1, 'radli_cleared', $playerId );
		}
	}

	public function radliAdjustment( $points, $playerId ) {
		if ( $this->getRadli( $playerId ) ) {
			$points = $points * 2;
		}
		return $points;
	}

	//////////////////////////////////////////////////////////////////////////////
	//////////// Player actions
	////////////

	function bid( $bid ) {
		if ( $bid !== 'pass' ) {
			self::checkAction( 'bid' );
		} else {
			self::checkAction( 'pass' );
		}

		$playerId = self::getActivePlayerId();

		if ( $bid === 'pass' ) {
			self::notifyAllPlayers(
				'someonePasses',
				clienttranslate( '${player_name} passes' ),
				array(
					'player_id'   => $playerId,
					'player_name' => self::getActivePlayerName(),
				)
			);
			$firstPasser = self::getGameStateValue( 'firstPasser' );
			if ( $firstPasser == 0 ) {
				self::setGameStateValue( 'firstPasser', $playerId );
			} else {
				$secondPasser = self::getGameStateValue( 'secondPasser' );
				if ( $secondPasser == 0 ) {
					self::setGameStateValue( 'secondPasser', $playerId );
				} else {
					self::setGameStateValue( 'thirdPasser', $playerId );
				}
			}

			self::trace( 'playerBid->pass' );
			$this->gamestate->nextState( 'pass' );
		} else {
			$highBid    = self::getGameStateValue( 'highBid' );
			$highBidder = self::getGameStateValue( 'highBidder' );

			if ( $highBidder == $playerId ) {
				throw new BgaUserException( 'You cannot overbid your own bid!' );
			}
			if ( $bid < $highBid ) {
				throw new BgaUserException( 'Your bid is too low!' );
			}

			self::setGameStateValue( 'highBid', $bid );
			self::setGameStateValue( 'highBidder', $playerId );

			self::notifyAllPlayers(
				'updateBids',
				clienttranslate( '${player_name} bids ${bid_name}' ),
				array(
					'highBidder'  => $playerId,
					'player_name' => self::getActivePlayerName(),
					'highBid'     => $bid,
					'bid_name'    => $this->bid_names[ $bid ],
				)
			);

			self::trace( 'playerBid->bid' );
			$this->gamestate->nextState( 'bid' );
		}
	}

	function finalBid( $bid ) {
		$playerId = self::getActivePlayerId();
		$highBid  = self::getGameStateValue( 'highBid' );
		$players  = self::loadPlayersBasicInfos();

		if ( $highBid > 2 && $bid < $highBid ) {
			// It's ok to downgrade three to a klop.
			throw new BgaUserException( 'The bid is too low!' );
		}

		self::setGameStateValue( 'highBid', $bid );
		self::giveExtraTime( $playerId );

		$this->increaseBidStat( $bid );

		self::notifyAllPlayers(
			'declarer',
			clienttranslate( '${player_name} is the declarer and chooses to play ${contract}.' ),
			array(
				'player_id'   => $playerId,
				'player_name' => $players[ $playerId ]['player_name'],
				'contract'    => $this->bid_names[ $bid ],
			)
		);
		$this->incStat( 1, 'games_declared', $playerId );

		if ( $bid == BID_BEGGAR || $bid == BID_OPEN_BEGGAR ) {
			$this->incStat( 1, 'beggars_played', $playerId );
		}

		if ( $bid >= BID_COLOUR_VALAT ) {
			$this->incStat( 1, 'valats_played', $playerId );
		}

		$transition = 'toKingCalling';
		if ( $bid == BID_KLOP || $bid >= BID_BEGGAR ) {
			// In klop or bids above beggar, no king calling, exchange or announcements.
			$transition = 'toTrickTaking';
		}
		if ( $bid >= BID_SOLO_THREE && $bid < BID_BEGGAR ) {
			// In solo bids, there's no king calling.
			$transition = 'toExchange';
			$this->incStat( 1, 'solos_played', $playerId );
		}
		self::trace( 'finalBid->' . $transition );
		$this->gamestate->nextState( $transition );
	}

	function playCard( $card_id ) {
		self::checkAction( 'playCard' );
		$playerId          = self::getActivePlayerId();
		$currentCard       = $this->cards->getCard( $card_id );
		$currentTrickColor = intval( self::getGameStateValue( 'trickColor' ) );
		$currentBid        = self::getGameStateValue( 'highBid' );

		$this->checkNormalRules( $currentCard, $currentTrickColor, $playerId );
		if ( in_array( $currentBid, array( BID_KLOP, BID_BEGGAR, BID_OPEN_BEGGAR ) ) ) {
			$this->checkAvoidanceRules( $currentCard, $currentTrickColor, $playerId );
		}

		$this->checkUltimo( $currentCard, $playerId );

		$this->doPlayCard( $currentCard, $playerId, $card_id, $currentTrickColor );
	}

	function doPlayCard( $currentCard, $playerId, $card_id, $currentTrickColor ) {
		$this->cards->moveCard( $card_id, 'cardsontable', $playerId );

		if ( $currentTrickColor === 0 ) {
			self::setGameStateValue( 'trickColor', $currentCard['type'] );
		}

		self::notifyAllPlayers(
			'playCard',
			clienttranslate( '${player_name} plays ${color_displayed}${value_displayed}' ),
			array(
				'i18n'            => array( 'value_displayed' ),
				'card_id'         => $card_id,
				'player_id'       => $playerId,
				'player_name'     => self::getActivePlayerName(),
				'value'           => $currentCard['type_arg'],
				'value_displayed' => $this->getCardDisplayValue( $currentCard['type'], $currentCard['type_arg'] ),
				'color'           => $currentCard['type'],
				'color_displayed' => $this->getCardSuitSymbol( $currentCard['type'] ),
			),
		);

		self::trace( 'playCard->playCard' );
		$this->gamestate->nextState( 'playCard' );
	}

	function discardCard( $card_id ) {
		self::checkAction( 'discardCard' );
		$playerId    = self::getActivePlayerId();
		$currentCard = $this->cards->getCard( $card_id );
		$cardsInHand = $this->cards->getCardsInLocation( 'hand', $playerId );

		if ( $this->getCardPointValue( $currentCard ) === 5 ) {
			throw new BgaUserException( self::_( 'You cannot discard a five-point card.' ) );
		}

		$this->cards->moveCard( $card_id, 'cardswon', $playerId );

		if ( $currentCard['type'] == SUIT_TRUMP ) {
			self::notifyAllPlayers(
				'discardTrump',
				clienttranslate( '${player_name} discards ${color_displayed}${value_displayed}' ),
				array(
					'i18n'            => array( 'value_displayed' ),
					'card_id'         => $card_id,
					'player_id'       => $playerId,
					'player_name'     => self::getActivePlayerName(),
					'value'           => $currentCard['type_arg'],
					'value_displayed' => $this->getCardDisplayValue( $currentCard['type'], $currentCard['type_arg'] ),
					'color'           => $currentCard['type'],
					'color_displayed' => $this->getCardSuitSymbol( $currentCard['type'] ),
				),
			);
		} else {
			self::notifyAllPlayers(
				'discardNontrump',
				clienttranslate( '${player_name} discards a non-trump card' ),
				array(
					'player_id'       => $playerId,
					'player_name'     => self::getActivePlayerName(),
				),
			);

		}

		self::notifyPlayer(
			$playerId,
			'discardCard',
			'',
			array(
				'card_id' => $card_id,
				'value'   => $currentCard['type_arg'],
				'color'   => $currentCard['type'],
			)
		);

		if ( $this->cards->countCardInLocation( 'hand', $playerId ) > HAND_SIZE ) {
			self::trace( 'discardCard->discardCard' );
			$this->gamestate->nextState( 'discardCard' );
		} else {
			$currentBid = self::getGameStateValue( 'highBid' );
			if ( $currentBid >= BID_SOLO_THREE && $currentBid <= BID_SOLO_ONE ) {
				self::trace( 'discardCard->toColourValat' );
				$this->gamestate->nextState( 'toColourValat' );
			} else {
				self::trace( 'discardCard->doneDiscarding' );
				$this->gamestate->nextState( 'doneDiscarding' );
			}
		}
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

		self::setGameStateValue( 'calledKing', $color );

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

		$declarer = self::getGameStateValue( 'declarer' );
		$sql      = "UPDATE player SET player_identity = 'opponent_hidden' WHERE player_id != $declarer";
		self::DbQuery( $sql );

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

			$this->setPlayerTeam( $partner, 'declarer', true );
			$this->incStat( 1, 'games_as_partner', $partner );
		}

		if ( $partner == 'talon' ) {
			$this->incStat( 1, 'kings_in_talon', $declarer );
		}

		self::trace('callKing->kingChosen->newTrick');
		$this->gamestate->nextState( 'kingChosen' );
	}

	function chooseCards( $cards, $talon ) {
		self::checkAction( 'chooseCards' );
		self::trace( 'chooseCards' );

		$unpackedCards = array();
		foreach ( $cards as $card ) {
			$card            = explode( '_', $card );
			$unpackedCards[] = array(
				'color' => $card[0],
				'value' => $card[1],
			);
		}

		$playerId   = self::getActivePlayerId();
		$talonCards = $this->cards->getCardsInLocation( 'talon' );

		$cardObjects = $this->getCardsBasedOnColorValue( $talonCards, $unpackedCards );

		if ( count( $cardObjects ) !== count( $unpackedCards ) ) {
			throw new BgaUserException( self::_( 'All cards were not found in the talon' ) );
		}

		$calledKing       = self::getGameStateValue( 'calledKing' );
		$calledKingChosen = false;

		foreach ( $cardObjects as $card ) {
			if ( $card['location'] !== 'talon' ) {
				throw new BgaUserException( self::_('This card is not in the talon!') );
			}
			if ( $card['type'] == $calledKing && $card['type_arg'] == '14' ) {
				$calledKingChosen = true;
				self::setGameStateValue( 'calledKingChosen', 1 );
			}
			$this->cards->moveCard( $card['id'], 'hand', $playerId );
			$cardsTaken[] = $this->getCardSuitSymbol( $card['type'] ) .
				$this->getCardDisplayValue( $card['type'], $card['type_arg'] );
		}

		if ( ! $calledKingChosen ) {
			$this->cards->moveAllCardsInLocation( 'talon', 'opponents' );
		} else {
			self::notifyAllPlayers(
				'calledKingChosen',
				clienttranslate( '${player_name} chooses the called king from the talon. The talon is set aside.' ),
				array( 'player_name'     => self::getActivePlayerName() )
			);
		}

		$cardsTaken = implode( ', ', $cardsTaken );

		self::notifyPlayer(
			$playerId,
			'newHand',
			'',
			array( 'cards' => $this->cards->getCardsInLocation( 'hand', $playerId ) )
		);

		self::notifyAllPlayers(
			'talonChosen',
			clienttranslate( '${player_name} takes cards from the talon: ${cards}' ),
			array(
				'player_id'   => $playerId,
				'player_name' => self::getActivePlayerName(),
				'talon_id'    => $talon,
				'cards'       => $cardsTaken,
			)
		);

		self::trace( 'chooseCards->discardCards' );
		$this->gamestate->nextState( 'chooseCards' );
	}

	public function upgradeToColourValat() {
		self::checkAction( 'upgradeToColourValat' );
		self::trace( 'upgradeToColourValat' );

		$playerId = self::getActivePlayerId();
		self::setGameStateValue( 'highBid', BID_COLOUR_VALAT );

		self::notifyAllPlayers(
			'upgradeToColourValat',
			clienttranslate( '${player_name} upgrades their bid to colour valat' ),
			array(
				'player_id'   => $playerId,
				'player_name' => self::getActivePlayerName(),
			)
		);

		self::trace( 'upgradeToColourValat->newTrick' );
		$this->gamestate->nextState( 'upgradeToColourValat' );
	}

	public function keepCurrentBid() {
		self::checkAction( 'keepCurrentBid' );
		self::trace( 'keepCurrentBid' );

		$playerId = self::getActivePlayerId();
		self::notifyAllPlayers(
			'keepCurrentBid',
			clienttranslate( '${player_name} keeps their current bid' ),
			array(
				'player_id'   => $playerId,
				'player_name' => self::getActivePlayerName(),
			)
		);

		self::trace( 'keepCurrentBid->newTrick' );
		$this->gamestate->nextState( 'keepCurrentBid' );
	}

	public function makeAnnouncement( $announcement ) {
		self::checkAction( 'makeAnnouncement' );
		self::trace( 'makeAnnouncement: ' . $announcement );

		$playerId = self::getActivePlayerId();

		$currentValue = self::getGameStateValue( $this->announcements[ $announcement ]['value'] );
		if ( $currentValue == 0 ) {
			$currentValue = 1;
		}
		$currentValue = $currentValue * 2;
		if ( $announcement != ANNOUNCEMENT_GAME ) {
			self::setGameStateValue(
				$this->announcements[ $announcement ]['player'],
				$playerId
			);
		}
		if ( $announcement == ANNOUNCEMENT_VALAT ) {
			$this->clearAnnouncements();
		}

		self::setGameStateValue( $this->announcements[ $announcement ]['value'], $currentValue );
		$playerAnnouncements = self::incGameStateValue( 'playerAnnouncements', 1 );

		if ( $this->playerIdentityHidden( $playerId ) ) {
			$this->maybeRevealIdentity( $announcement, $currentValue, $playerId );
		}

		$announcedValue = $this->announcement_values[ $currentValue ]
			. ' ' . $this->announcements[ $announcement ]['name'];

		self::notifyAllPlayers(
			'makeAnnouncement',
			clienttranslate( '${player_name} announces ${announcementDisplay}' ),
			array(
				'player_id'           => $playerId,
				'player_name'         => self::getActivePlayerName(),
				'announcementDisplay' => $announcedValue,
				'announcement'        => $announcement,
				'newValue'            => $currentValue,
				'playerAnnouncements' => $playerAnnouncements,
			)
		);

		self::trace( 'announcements->announcements' );
		$this->gamestate->nextState( 'makeAnnouncement' );
	}

	public function passAnnouncement( $type ) {
		self::checkAction( 'passAnnouncement' );
		self::trace( 'passAnnouncement' );

		$playerId = self::getActivePlayerId();

		self::setGameStateValue( 'playerAnnouncements', 0 );

		if ( $type == 'pass' ) {
			$firstPasser = self::getGameStateValue( 'firstPasser' );
			if ( $firstPasser == 0 ) {
				self::setGameStateValue( 'firstPasser', $playerId );
			} else {
				$secondPasser = self::getGameStateValue( 'secondPasser' );
				if ( $secondPasser == 0 ) {
					self::setGameStateValue( 'secondPasser', $playerId );
				} else {
					self::setGameStateValue( 'thirdPasser', $playerId );
				}
			}
			self::notifyAllPlayers(
				'passAnnouncement',
				clienttranslate( '${player_name} passes' ),
				array(
					'player_id'   => $playerId,
					'player_name' => self::getActivePlayerName(),
				)
			);
		} else {
			self::notifyAllPlayers(
				'passAnnouncement',
				clienttranslate( '${player_name} makes no more announcements' ),
				array(
					'player_id'   => $playerId,
					'player_name' => self::getActivePlayerName(),
				)
			);
		}

		self::trace( 'announcements->announcementsNextPlayer' );
		$this->gamestate->nextState( 'passAnnouncement' );
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
		self::setGameStateValue( 'trickColor', 0 );
		self::setGameStateValue( 'trickCount', 0 );
		self::setGameStateValue( 'tricksByDeclarer', 0 );
		self::setGameStateValue( 'gameValue', 1 );

		$sql = "UPDATE player SET player_identity = ''";
		self::DbQuery( $sql );

		$this->cards->moveAllCardsInLocation( null, 'deck' );
		$this->cards->shuffle('deck');

		$dealer = intval( self::getGameStateValue( 'dealer' ) );

		if ( $dealer === 0 ) {
			$playerTable = $this->getNextPlayerTable();
			$dealer      = $this->getPlayerBefore( $playerTable[0] );
			$players     = self::loadPlayersBasicInfos();
			$klop        = self::getGameStateValue( 'compulsoryKlop' );

			self::notifyAllPlayers(
				'newDealer',
				clienttranslate( '${player_name} is the first dealer' ),
				array(
					'player_id'      => $dealer,
					'player_name'    => $players[ $dealer ]['player_name'],
					'compulsoryKlop' => $klop,
				)
			);
		}

		$players    = self::loadPlayersBasicInfos();
		$trumpCount = HAND_SIZE + 1;
		$dealCount  = 0;
		do {
			$dealCount++;
			// Deal 12 cards to each player.
			foreach ( $players as $player_id => $player ) {
				$cards = $this->cards->pickCards( HAND_SIZE, 'deck', $player_id );
				self::notifyPlayer(
					$player_id,
					'newHand',
					'',
					array(
						'cards' => $cards
					)
				);
				$trumpCount = min( $this->countSuitInHand( $player_id, SUIT_TRUMP ), $trumpCount );
			}
		} while ( $trumpCount == 0 );

		if ( $dealCount > 1 ) {
			self::notifyAllPlayers(
				'newDeal',
				clienttranslate( 'Someone was dealt a hand with no trump, this round has compulsory klop.' ),
				array( 'compulsoryKlop' => 1 )
			);
			self::setGameStateValue( 'compulsoryKlop', 1 );
		}

		$this->talon = $this->cards->pickCardsForLocation( 6, 'deck', 'talon' );

		self::trace( 'stNewHand->startBidding' );
		$this->gamestate->nextState();
	}

	function stStartBidding() {
		self::trace( 'stStartBidding' );
		$dealer   = intval( self::getGameStateValue( 'dealer' ) );
		$forehand = $this->getPlayerAfter( $dealer );
		self::setGameStateValue( 'forehand', $forehand );
		$this->gamestate->changeActivePlayer( $this->getPlayerAfter( $forehand ) );
		self::trace( 'forehand: ' . $forehand );

		$secondPriority = $this->getPlayerAfter( $forehand );
		$thirdPriority  = $this->getPlayerAfter( $secondPriority );
		$fourthPriority = $this->getPlayerAfter( $thirdPriority );

		self::setGameStateValue( 'secondPriority', $secondPriority );
		self::setGameStateValue( 'thirdPriority', $thirdPriority );
		self::setGameStateValue( 'fourthPriority', $fourthPriority );
		self::setGameStateValue( 'highBidder', $forehand );
		self::setGameStateValue( 'highBid', BID_THREE );
		self::setGameStateValue( 'firstPasser', 0 );
		self::setGameStateValue( 'secondPasser', 0 );
		self::setGameStateValue( 'thirdPasser', 0 );

		self::trace( "notifying players" );
		self::notifyAllPlayers(
			'setPriorityOrder',
			'',
			array(
				'forehand'	     => $forehand,
				'secondPriority' => $secondPriority,
				'thirdPriority'  => $thirdPriority,
				'fourthPriority' => $fourthPriority,
			)
		);

		self::trace( 'stStartBidding->playerBid' );
		$this->gamestate->nextState();
	}

	function stNextBid() {
		$thirdPasser = self::getGameStateValue( 'thirdPasser' );

		if ( $thirdPasser != 0 ) {
			// Three passes, so end the bidding.
			$highBidder = self::getGameStateValue( 'highBidder' );
			$highBid    = self::getGameStateValue( 'highBid' );
			$players    = self::loadPlayersBasicInfos();

			$this->setPlayerTeam( $highBidder, 'declarer', false );

			self::setGameStateValue( 'declarer', $highBidder );
			self::setGameStateValue( 'declarerPartner', 0 );
			self::setGameStateValue( 'firstPasser', 0 );
			self::setGameStateValue( 'secondPasser', 0 );
			self::setGameStateValue( 'thirdPasser', 0 );


			self::notifyAllPlayers(
				'updateBids',
				clienttranslate( 'All players have passed, ${player_name} is the high bidder.' ),
				array(
					'highBidder'  => $highBidder,
					'highBid'     => $highBid,
					'player_name' => $players[ $highBidder ]['player_name'],
				)
			);
			$this->gamestate->changeActivePlayer( $highBidder );

			$nextState = 'allPass';
			if ( $highBid > BID_THREE && intval( self::getGameStateValue( 'allowUpgrades' ) ) == VALUE_NO ) {
				// Forehand can still upgrade a bid of three, even if upgrading is not allowed.
				$nextState = 'allPassNoUpgrade';
			}
			self::trace( 'stNextBid->' . $nextState );
			$this->gamestate->nextState( $nextState );
		} else {
			$firstPasser  = self::getGameStateValue( 'firstPasser' );
			$secondPasser = self::getGameStateValue( 'secondPasser' );

			$passers = array( $firstPasser, $secondPasser, $thirdPasser );

			$activePlayer = self::getActivePlayerId();
			$nextPlayer   = $this->getPlayerAfter( $activePlayer );
			while ( in_array( $nextPlayer, $passers ) )	{
				$nextPlayer = $this->getPlayerAfter( $nextPlayer );
				self::trace( 'Trying next player: ' . $nextPlayer );
			}
			self::trace( "Next player is: " . $nextPlayer );

			$this->gamestate->changeActivePlayer( $nextPlayer );
			self::giveExtraTime( $nextPlayer );

			self::trace( 'stNextBid->nextBidder' );
			$this->gamestate->nextState( 'nextBidder' );
		}
	}

	public function stNoFinalBid() {
		$bid = self::getGameStateValue( 'highBid' );

		$this->increaseBidStat( $bid );

		$transition = 'toKingCalling';
		if ( $bid == BID_KLOP || $bid >= BID_BEGGAR ) {
			// In klop or bids above beggar, no king calling, exchange or announcements.
			$transition = 'toTrickTaking';
		}
		if ( $bid >= BID_SOLO_THREE && $bid < BID_BEGGAR ) {
			// In solo bids, there's no king calling.
			$transition = 'toExchange';
		}
		self::trace( 'noFinalBid->' . $transition );
		$this->gamestate->nextState( $transition );
	}

	public function stExchange() {
		self::trace( 'stExchange' );

		self::notifyAllPlayers(
			'newTalon',
			'',
			array(
				'talon' => $this->cards->getCardsInLocation( 'talon' )
			)
		);

		self::trace( 'stExchange->exchange' );
		$this->gamestate->nextState( 'exchange' );
	}

	public function stStartAnnouncements() {
		self::setGameStateValue( 'trulaPlayer', 0 );
		self::setGameStateValue( 'trulaValue', 1 );
		self::setGameStateValue( 'kingsPlayer', 0 );
		self::setGameStateValue( 'kingsValue', 1 );
		self::setGameStateValue( 'kingUltimoPlayer', 0 );
		self::setGameStateValue( 'kingUltimoValue', 1 );
		self::setGameStateValue( 'kingUltimoStatus', 0 );
		self::setGameStateValue( 'pagatUltimoPlayer', 0 );
		self::setGameStateValue( 'pagatUltimoValue', 1 );
		self::setGameStateValue( 'pagatUltimoStatus', 0 );
		self::setGameStateValue( 'valatPlayer', 0 );
		self::setGameStateValue( 'valatValue', 1 );

		self::setGameStateValue( 'firstPasser', 0 );
		self::setGameStateValue( 'secondPasser', 0 );
		self::setGameStateValue( 'thirdPasser', 0 );

		self::trace( 'stStartAnnouncements->announcements' );
		$this->gamestate->nextState();
	}

	public function stAnnouncementsNextPlayer() {
		self::trace( 'stAnnouncementsNextPlayer' );

		if ( self::getGameStateValue( 'thirdPasser' ) > 0 ) {
			self::notifyAllPlayers(
				'allAnnouncementsPassed',
				clienttranslate( 'Three passes in a row, announcements over' ),
				array()
			);

			self::trace( 'announcementsNextPlayer->newTrick' );
			$this->gamestate->nextState( 'allAnnouncementsPassed' );
		} else {
			$player_id = self::activeNextPlayer();
			self::giveExtraTime( $player_id );
			self::trace( 'announcementsNextPlayer->announcements' );
			$this->gamestate->nextState( 'nextPlayer' );
		}
	}

	function stNewTrick() {
		self::setGameStateValue( 'trickColor', 0 );
		if ( self::getGameStateValue( 'trickCount' ) < 1 ) {
			$bid         = self::getGameStateValue( 'highBid' );
			$firstPlayer = self::getGameStateValue( 'forehand' );
			if ( $bid >= BID_BEGGAR ) {
				$firstPlayer = self::getGameStateValue( 'declarer' );
			}
			$this->gamestate->changeActivePlayer( $firstPlayer );
		}
		self::trace( 'stNewTrick->playerTurn' );
		$this->gamestate->nextState();
	}

	function stNextPlayer() {
		if ( intval( $this->cards->countCardInLocation( 'cardsontable' ) ) === 4 ) {
			self::trace('Trick over, determine winner.');
			$trickCount = self::incGameStateValue( 'trickCount', 1 );

			$winnerId = $this->determineTrickWinner( $trickCount );

			$handFinished = intval( $this->cards->countCardInLocation( 'hand' ) ) === 0;
			$currentBid   = self::getGameStateValue( 'highBid' );
			$valatPlayer  = self::getGameStateValue( 'valatPlayer' );
			if ( ! $handFinished
				&& (in_array( $currentBid, array( BID_BEGGAR, BID_OPEN_BEGGAR, BID_VALAT ) )
					|| $valatPlayer)
				) {
				$handFinished = $this->checkBeggarAndValat( $currentBid );
			}

			if ( $handFinished ) {
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

		$currentBid = self::getGameStateValue( 'highBid' );

		switch( $currentBid ) {
			case BID_KLOP:
				$this->klopCountingAndScoring();
				break;
			case BID_BEGGAR:
			case BID_OPEN_BEGGAR:
				$this->beggarCountingAndScoring();
				break;
			case BID_VALAT:
				$this->valatCountingAndScoring();
				break;
			default:
				$this->regularCountingAndScoring();
				break;
		}

		$valatActive = self::getGameStateValue( 'valatPlayer' );
		if ( $currentBid == BID_KLOP || $currentBid >= BID_BEGGAR || $valatActive ) {
			$this->addRadli();
			$this->updatePlayerData( clienttranslate( 'Everybody gets a radlc' ) );
		}
		$this->updateScores();

		self::trace( 'stCountingAndScoring->endHand' );
		$this->gamestate->nextState();
	}

	function stEndHand() {
		$handsPlayed = intval( self::incGameStateValue( 'handsPlayed', 1 ) );
		$gameLength  = intval( self::getGameStateValue( 'gameLength' ) );

		if ( $handsPlayed == $gameLength ) {
			self::trace( 'stEndHand->radlScoring' );
			$this->gamestate->nextState( 'gameEnd' );
		} else {
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
	}

	function stRadliScoring() {
		$players    = self::loadPlayersBasicInfos();
		$radliValue = intval( self::getGameStateValue( 'radliValue' ) );
		foreach( $players as $player_id => $player ) {
			$radli = $this->getRadli( $player_id );
			if ( $radli > 0 ) {
				$points = $radli * $radliValue;
				$sql    = "UPDATE player SET player_score=player_score-$points WHERE player_id='$player_id'";
				self::DbQuery($sql);
				self::notifyAllPlayers(
					'points',
					clienttranslate( '${player_name} loses ${points} points for having ${radli} radli' ),
					array(
						'player_id'   => $player_id,
						'player_name' => $player['player_name'],
						'points'      => $points,
						'radli'       => $radli,
					)
				);
			}
		}
	}

	//////////////////////////////////////////////////////////////////////////////
	//////////// Zombie
	////////////

	function zombieTurn( $state, $active_player ) {
		$statename = $state['name'];

		if ( $state['type'] === 'activeplayer' ) {
			switch ( $statename ) {
				case 'playerBid':
					// In bidding, zombie will pass.
					$this->gamestate->nextState( 'pass' );
					break;
				case 'finalBid':
					// Zombie forehand plays klop.
					self::setGameStateValue( 'highBid', BID_KLOP );
					$this->gamestate->nextState( 'toTrickTaking' );
					break;
				case 'announcements':
					// Zombie never makes announcements.
					$this->gamestate->nextState( 'passAnnouncement' );
					break;
				case 'playerTurn':
					// Zombie plays a random legal card.
					$this->playZombieCard( $active_player );
					break;
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

	function playZombieCard( $playerId ) {
		$currentTrickColor = intval( self::getGameStateValue( 'trickColor' ) );
		$currentBid        = self::getGameStateValue( 'highBid' );

		$cardPlayed = false;

		$cards = $this->cards->getCardsInLocation( 'hand', $playerId );
		shuffle( $cards );
		foreach ( $cards as $card ) {
			if ( $currentTrickColor === 0 ) {
				$cardPlayed = $card;
				break;
			}
			$card_ok = $this->checkNormalRules( $card, $currentTrickColor, $playerId, true );
			if ( in_array( $currentBid, array( BID_KLOP, BID_BEGGAR, BID_OPEN_BEGGAR ) ) ) {
				$card_ok = $this->checkAvoidanceRules( $card, $currentTrickColor, $playerId, true );
			}
			if ( $card_ok ) {
				$cardPlayed = $card;
				break;
			}
		}

		$cardId = $this->getCardId( $cardPlayed['type'], $cardPlayed['type_arg'] );
		$this->doPlayCard( $cardPlayed, $playerId, $cardId, $currentTrickColor );
	}

	function getCardId( $color, $value ) {
		$sql = "SELECT card_id FROM cards WHERE card_type='$color' AND card_type_arg='$value'";
		return self::getUniqueValueFromDB( $sql );
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
