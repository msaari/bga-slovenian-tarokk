<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SlovenianTarokk implementation : © Mikko Saari <mikko@mikkosaari.fi>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * sloveniantarokk.action.php
 *
 * SlovenianTarokk main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/sloveniantarokk/sloveniantarokk/myAction.html", ...)
 */

class action_sloveniantarokk extends APP_GameAction {
	// Constructor: please do not modify
	public function __default() {
		if( self::isArg( 'notifwindow' ) ) {
			$this->view              = 'common_notifwindow';
			$this->viewArgs['table'] = self::getArg( 'table', AT_posint, true );
		} else {
			$this->view = 'sloveniantarokk_sloveniantarokk';
			self::trace( 'Complete reinitialization of board game' );
		}
	}

	public function playCard() {
		self::setAjaxMode();
		$card_id = self::getArg( 'id', AT_posint, true );
		$this->game->playCard( $card_id );
		self::ajaxResponse();
	}

	public function callSpadeKing() {
		self::setAjaxMode();
		$this->game->callSpadeKing();
		self::ajaxResponse();
	}

	public function callHeartKing() {
		self::setAjaxMode();
		$this->game->callHeartKing();
		self::ajaxResponse();
	}

	public function callDiamondKing() {
		self::setAjaxMode();
		$this->game->callDiamondKing();
		self::ajaxResponse();
	}

	public function callClubKing() {
		self::setAjaxMode();
		$this->game->callClubKing();
		self::ajaxResponse();
	}

	public function chooseCards() {
		self::setAjaxMode();
		self::trace( 'chooseCards' );
		$cards = self::getArg( 'cards', AT_alphanum, true );
		$cards = explode( ' ', $cards );
		$talon = self::getArg( 'talon', AT_alphanum, true );
		self::trace( 'chooseCards: ' . $talon );
		$this->game->chooseCards( $cards, $talon );
		self::ajaxResponse();
	}
}
