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
				'trulaTeam'           => 27,
				'trulaValue'          => 28,
				'kingsTeam'           => 28,
				'kingsValue'          => 29,
				'kingUltimoTeam'      => 30,
				'kingUltimoValue'     => 31,
				'pagatUltimoTeam'     => 32,
				'pagatUltimoValue'    => 33,
				'valat'	              => 34,
				'valatValue'          => 35,
				'gameValue'           => 36,
				'playerAnnouncements' => 37,
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
		self::setGameStateInitialValue( 'trulaTeam', 0 );
		self::setGameStateInitialValue( 'trulaValue', 0 );
		self::setGameStateInitialValue( 'kingsTeam', 0 );
		self::setGameStateInitialValue( 'kingsValue', 0 );
		self::setGameStateInitialValue( 'kingUltimoTeam', 0 );
		self::setGameStateInitialValue( 'kingUltimoValue', 0 );
		self::setGameStateInitialValue( 'pagatUltimoTeam', 0 );
		self::setGameStateInitialValue( 'pagatUltimoValue', 0 );
		self::setGameStateInitialValue( 'valatTeam', 0 );
		self::setGameStateInitialValue( 'valatValue', 0 );
		self::setGameStateInitialValue( 'gameValue', 0 );

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
		$sql = 'SELECT player_id id, player_score score, player_radl radl, player_identity team FROM player ';

		$result['players'] = self::getCollectionFromDb( $sql );

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
		$result['trulaTeam']           = self::getGameStateValue( 'trulaTeam' );
		$result['trulaValue']          = self::getGameStateValue( 'trulaValue' );
		$result['kingsTeam']           = self::getGameStateValue( 'kingsTeam' );
		$result['kingsValue']          = self::getGameStateValue( 'kingsValue' );
		$result['kingUltimoTeam']      = self::getGameStateValue( 'kingUltimoTeam' );
		$result['kingUltimoValue']     = self::getGameStateValue( 'kingUltimoValue' );
		$result['pagatUltimoTeam']     = self::getGameStateValue( 'pagatUltimoTeam' );
		$result['pagatUltimoValue']    = self::getGameStateValue( 'pagatUltimoValue' );
		$result['valatTeam']           = self::getGameStateValue( 'valatTeam' );
		$result['valatValue']          = self::getGameStateValue( 'valatValue' );
		$result['playerAnnouncements'] = self::getGameStateValue( 'playerAnnouncements' );

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

	function countTrumpsInHand( $playerId ) {
		$cardsInHand  = $this->cards->getCardsInLocation( 'hand', $playerId );
		$trumpsInHand = 0;
		foreach ( $cardsInHand as $card ) {
			if ( $card['type'] == SUIT_TRUMP ) {
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
			if ( $this->countTrumpsInHand( $playerId ) == 1 ) {
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

	function checkNormalRules( $currentCard, $currentTrickColor, $playerId ) {
		$cardsInHand = $this->cards->getCardsInLocation( 'hand', $playerId );

		if ( $currentTrickColor > 0 ) {
			if ( $this->haveColorInHand( $currentTrickColor, $cardsInHand )
				&& intval( $currentCard['type'] ) !== $currentTrickColor ) {
				throw new BgaUserException( self::_( 'You must play a ' ) . $this->colors[ $currentTrickColor ]['name'] . '.' );
			}
			if ( $currentTrickColor !== SUIT_TRUMP
				&& intval( $currentCard['type'] ) !== SUIT_TRUMP
				&& ! $this->haveColorInHand( $currentTrickColor, $cardsInHand )
				&& $this->haveColorInHand( SUIT_TRUMP, $cardsInHand ) ) {
				throw new BgaUserException( self::_( 'You must play a ' ) . $this->colors[ SUIT_TRUMP ]['name'] . '.' );
			}
		}
	}

	function beggarCountingAndScoring() {
		$players    = self::loadPlayersBasicInfos();
		$declarer   = self::getGameStateValue( 'declarer' );
		$failed     = self::getGameStateValue( 'tricksByDeclarer' );
		$currentBid = self::getGameStateValue( 'highBid' );

		$points = $this->bid_point_values[ $currentBid ];

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
		}
	}

	function valatCountingAndScoring() {
		$players    = self::loadPlayersBasicInfos();
		$declarer   = self::getGameStateValue( 'declarer' );
		$tricks     = self::getGameStateValue( 'tricksByDeclarer' );
		$currentBid = self::getGameStateValue( 'highBid' );

		$points = $this->bid_point_values[ $currentBid ];

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
				clienttranslate( '${player_name} scores ${score}' ),
				array(
					'player_name' => $player['player_name'],
					'score'       => $playerScore,
				)
			);
		}
		if ( $loser ) {
			$points = $this->bid_point_values[ BID_KLOP ];
			$sql    = "UPDATE player SET player_score=player_score-$points  WHERE player_id='$loser'";
			self::DbQuery($sql);
			self::notifyAllPlayers(
				'points',
				clienttranslate( '${player_name} loses ${points} points' ),
				array (
					'player_name' => $players[ $loser ][ 'player_name' ],
					'points'      => $points,
				)
			);
		}
		if ( count ( $winners ) > 0 ) {
			foreach ( $winners as $winner_id ) {
				$points = $this->bid_point_values[ BID_KLOP ];
				$sql    = "UPDATE player SET player_score=player_score+$points  WHERE player_id='$winner_id'";
				self::DbQuery($sql);
				self::notifyAllPlayers(
					'points',
					clienttranslate( '${player_name} wins ${points} points' ),
					array (
						'player_name' => $players[ $winner_id ][ 'player_name' ],
						'points'      => $points,
					)
				);
			}
		}
		if ( ! $loser && count( $winners ) == 0 ) {
			foreach ( $scores as $player_id => $score ) {
				$points = $this->roundToNearestFive( $score );
				$sql    = "UPDATE player SET player_score=player_score-$points  WHERE player_id='$player_id'";
				self::DbQuery($sql);
				self::notifyAllPlayers(
					'points',
					clienttranslate( '${player_name} loses ${points} points' ),
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
				clienttranslate( '${team} team scores ${score}' ),
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

		if ( $teamScores['Declarer'] > 35 ) {
			$notification = clienttranslate( 'Declarer gains ${points} points' );
			$sql          = "UPDATE player SET player_score=player_score+$points  WHERE player_id='$declarer'";
			self::DbQuery($sql);
			if ( $declarerPartner ) {
				$notification = clienttranslate( 'Declarer\'s team gains ${points} points' );
				$sql          = "UPDATE player SET player_score=player_score+$points  WHERE player_id='$declarerPartner'";
				self::DbQuery($sql);
			}
			self::notifyAllPlayers(
				'points',
				$notification,
				array ( 'points' => $points )
			);
		} else {
			$notification = clienttranslate( 'Declarer lost ${points} points' );
			$sql          = "UPDATE player SET player_score=player_score-$points  WHERE player_id='$declarer'";
			self::DbQuery($sql);
			if ( $declarerPartner ) {
				$notification = clienttranslate( 'Declarer\'s team lost ${points} points' );
				$sql          = "UPDATE player SET player_score=player_score-$points  WHERE player_id='$declarerPartner'";
				self::DbQuery($sql);
			}
			self::notifyAllPlayers(
				'points',
				$notification,
				array ( 'points' => $points )
			);
		}
	}

	function roundToNearestFive( $number ) {
		return floor( $number / 5 ) * 5;
	}

	function determineTrickWinner() {
		$players = self::loadPlayersBasicInfos();

		$currentBid = self::getGameStateValue( 'highBid' );

		list(
			'bestValue'         => $bestValue,
			'bestValuePlayerId' => $bestValuePlayerId,
			'bestValueIsTrump'  => $bestValueIsTrump,
			'pagatPlayer'       => $pagatPlayer,
			'mondPlayer'        => $mondPlayer,
		) = $this->analyzeTrick( $currentBid );

		$bestValuePlayerId = $this->checkForEmperorsTrick( $pagatPlayer, $mondPlayer, $bestValuePlayerId, $players );
		$this->checkForMondCapture( $currentBid, $mondPlayer, $bestValuePlayerId, $players );

		$this->gamestate->changeActivePlayer( $bestValuePlayerId );
		$this->cards->moveAllCardsInLocation( 'cardsontable', 'cardswon', null, $bestValuePlayerId );

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
	}

	function analyzeTrick( $currentBid ) {
		$currentTrickColor = intval( self::getGameStateValue( 'trickColor' ) );
		$cardsOnTable      = $this->cards->getCardsInLocation( 'cardsontable' );
		$bestValue         = 0;
		$bestValuePlayerId = null;
		$bestValueIsTrump  = false;

		$mondPlayer  = 0;
		$pagatPlayer = 0;

		foreach ( $cardsOnTable as $card ) {
			$cardValue = $card['type_arg'];
			$cardColor = intval( $card['type'] );
			if ( $cardValue == 1 && $cardColor == SUIT_TRUMP ) {
				$pagatPlayer = $card['location_arg'];
			}
			if ( $cardValue == 21 && $cardColor == SUIT_TRUMP ) {
				$mondPlayer = $card['location_arg'];
			}
			if ( $cardColor === $currentTrickColor && ! $bestValueIsTrump ) {
				if ( $cardValue > $bestValue ) {
					$bestValue         = $cardValue;
					$bestValuePlayerId = $card['location_arg'];
				}
			}
			if ( $cardColor === 5 && ! in_array( $currentBid, array( BID_COLOUR_VALAT, BID_COLOUR_VALAT_WITHOUT ) ) ) {
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
		}
	}

	function dealVitamins( $bestValuePlayerId, $players ) {
		if ( $this->cards->countCardsInLocation( 'talon' ) > 0 ) {
			$vitamin = $this->cards->pickCardForLocation( 'talon', 'cardswon', $bestValuePlayerId );
			self::trace("NotifyAllPlayers: vitamin");
			self::notifyAllPlayers(
				'vitamin',
				clienttranslate( '${player_name} got some vitamins: ${card_display_color} ${card_display_value}' ),
				array(
					'player_id'          => $bestValuePlayerId,
					'player_name'        => $players[ $bestValuePlayerId ]['player_name'],
					'card_display_value' => $this->getCardDisplayValue( $vitamin['type'], $vitamin['type_arg'] ),
					'card_display_color' => $this->colors[ $vitamin['type'] ]['name'],
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
		$declarer         = self::getGameStateValue( 'declarer' );
		$trickCount       = self::getGameStateValue( 'trickCount' );
		$tricksByDeclarer = self::getGameStateValue( 'tricksByDeclarer' );

		if ( $currentBid == BID_VALAT ) {
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

		self::notifyAllPlayers(
			'declarer',
			clienttranslate( '${player_name} is the declarer and chooses to play ${contract}.' ),
			array(
				'player_id'   => $playerId,
				'player_name' => $players[ $playerId ]['player_name'],
				'contract'    => $this->bid_names[ $bid ],
			)
		);

		$transition = 'toKingCalling';
		if ( $bid == BID_KLOP || $bid >= BID_BEGGAR ) {
			// In klop or bids above beggar, no king calling, exchange or announcements.
			$transition = 'toTrickTaking';
		}
		if ( $bid >= BID_SOLO_THREE && $bid < BID_BEGGAR ) {
			// In solo bids, there's no king calling.
			$transition = 'toExchange';
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

		$this->cards->moveCard( $card_id, 'cardsontable', $playerId );

		if ( $currentTrickColor === 0 ) {
			self::setGameStateValue( 'trickColor', $currentCard['type'] );
		}

		self::notifyAllPlayers(
			'playCard',
			clienttranslate( '${player_name} plays ${color_displayed} ${value_displayed}' ),
			array(
				'i18n'            => array( 'color_displayed','value_displayed' ),
				'card_id'         => $card_id,
				'player_id'       => $playerId,
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
				clienttranslate( '${player_name} discards ${color_displayed} ${value_displayed}' ),
				array(
					'i18n'            => array( 'color_displayed','value_displayed' ),
					'card_id'         => $card_id,
					'player_id'       => $playerId,
					'player_name'     => self::getActivePlayerName(),
					'value'           => $currentCard['type_arg'],
					'value_displayed' => $this->getCardDisplayValue( $currentCard['type'], $currentCard['type_arg'] ),
					'color'           => $currentCard['type'],
					'color_displayed' => $this->colors[ $currentCard['type'] ]['name']
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

			$sql = "UPDATE player SET player_identity = 'declarer_hidden' WHERE player_id = $partner";
			self::DbQuery( $sql );
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

		foreach ( $cardObjects as $card ) {
			if ( $card['location'] !== 'talon' ) {
				throw new BgaUserException( self::_('This card is not in the talon!') );
			}
			$this->cards->moveCard( $card['id'], 'hand', $playerId );
			$cardsTaken[] = $this->getCardDisplayValue( $card['type'], $card['type_arg'] );
		}

		$this->cards->moveAllCardsInLocation( 'talon', 'opponents' );

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
		self::setGameStateValue( 'trulaTeam', 0 );
		self::setGameStateValue( 'trulaValue', 0 );
		self::setGameStateValue( 'kingsTeam', 0 );
		self::setGameStateValue( 'kingsValue', 0 );
		self::setGameStateValue( 'kingUltimoTeam', 0 );
		self::setGameStateValue( 'kingUltimoValue', 0 );
		self::setGameStateValue( 'pagatUltimoTeam', 0 );
		self::setGameStateValue( 'pagatUltimoValue', 0 );
		self::setGameStateValue( 'valatTeam', 0 );
		self::setGameStateValue( 'valatValue', 0 );
		self::setGameStateValue( 'gameValue', 0 );

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
				$trumpCount = min( $this->countTrumpsInHand( $player_id ), $trumpCount );
			}
		} while ( $trumpCount == 0 );

		if ( $dealCount > 1 ) {
			self::notifyAllPlayers(
				'newDeal',
				clienttranslate( 'Someone was dealt a hand with no trump, this round has compulsory klop.' ),
				array()
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
		$firstPasser  = self::getGameStateValue( 'firstPasser' );
		$secondPasser = self::getGameStateValue( 'secondPasser' );
		$thirdPasser  = self::getGameStateValue( 'thirdPasser' );

		if ( $thirdPasser != 0 ) {
			// Three passes, so end the bidding.
			$highBidder = self::getGameStateValue( 'highBidder' );
			$highBid    = self::getGameStateValue( 'highBid' );
			$players    = self::loadPlayersBasicInfos();

			$sql = "UPDATE player SET player_identity = 'declarer' WHERE player_id = $highBidder";
			self::DbQuery( $sql );

			self::setGameStateValue( 'declarer', $highBidder );
			self::setGameStateValue( 'declarerPartner', 0 );
			self::setGameStateValue( 'firstPasser', 0 );
			self::setGameStateValue( 'secondPasser', 0 );
			self::setGameStateValue( 'thirdPasser', 0 );

			self::trace( 'stNextBid->allPass' );

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
			self::trace( 'stNextBid->allPass' );
			$this->gamestate->nextState('allPass');
		} else {
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
			self::incGameStateValue( 'trickCount', 1 );

			$this->determineTrickWinner();

			$handFinished = intval( $this->cards->countCardInLocation( 'hand' ) ) === 0;
			$currentBid   = self::getGameStateValue( 'highBid' );
			if ( ! $handFinished && in_array( $currentBid, array( BID_BEGGAR, BID_OPEN_BEGGAR, BID_VALAT ) ) ) {
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

		$this->updateScores();

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
