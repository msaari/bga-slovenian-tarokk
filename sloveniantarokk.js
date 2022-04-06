/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SlovenianTarokk implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * sloveniantarokk.js
 *
 * SlovenianTarokk user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.sloveniantarokk", ebg.core.gamegui, {
        constructor: function(){
            console.log('sloveniantarokk constructor');

            this.cardwidth = 100;
            this.cardheight = 137;

        },

        /*
            setup:

            This method must set up the game user interface according to current game situation specified
            in parameters.

            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)

            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        setup : function(gamedatas) {
            console.log("Starting game setup");

            // Player hand
            this.playerHand = new ebg.stock();
            this.playerHand.create(this, $('myhand'), this.cardwidth, this.cardheight);
            this.playerHand.image_items_per_row = 22;

            dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' );

            // Create cards types:
            for (var color = 1; color <= 5; color++) {
                if (color < 5) {
                    for (var value = 7; value <= 14; value++) {
                        // Build card type id
                        var card_type_id = this.getCardUniqueId(color, value);
                        this.playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.png', card_type_id);
                    }
                } else {
                    for (var value = 1; value <= 22; value++) {
                        // Build card type id
                        var card_type_id = this.getCardUniqueId(color, value);
                        this.playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.png', card_type_id);
                    }
                }
            }

            // Cards in player's hand
            for ( var i in this.gamedatas.hand) {
                var card = this.gamedatas.hand[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }

            // Cards played on table
            for (i in this.gamedatas.cardsontable) {
                var card = this.gamedatas.cardsontable[i];
                var color = card.type;
                var value = card.type_arg;
                var player_id = card.location_arg;
                this.playCardOnTable(player_id, color, value, card.id);
            }

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },

        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args ) {
            console.log( 'Entering state: '+stateName );

            switch( stateName ) {
                case 'exchange':
                    dojo.style('talonexchange', 'display', 'block');
                    dojo.place(this.format_block('jstpl_talonexchange_33', {}), 'talonexchange');
                    var counter = 0;
                    for (i in this.gamedatas.cardsintalon) {
                        counter++;
                        var card = this.gamedatas.cardsintalon[i];
                        var talon = counter <= 3 ? 'talon_33_1' : 'talon_33_2';
                        this.playCardInTalon(talon, card.type, card.type_arg, this.getCardUniqueId(card.type, card.type_arg))

                    }
                    dojo.connect(dojo.byId('talon_33_1'), 'onclick', this, 'onTalonClickChooseCards');
                    dojo.connect(dojo.byId('talon_33_2'), 'onclick', this, 'onTalonClickChooseCards');
                    break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName ) {
            console.log( 'Leaving state: '+stateName );

            switch (stateName) {
                case 'exchange':
                    dojo.style('talonexchange', 'display', 'none');
                    break;
            }
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function( stateName, args ) {
            console.log( 'onUpdateActionButtons: '+stateName );

            if (this.isCurrentPlayerActive()) {
                switch (stateName) {
                    case 'kingCalling':
                        this.addActionButton('call_spade_king', _('Spade'), 'onCallSpadeKing');
                        this.addActionButton('call_heart_king', _('Heart'), 'onCallHeartKing');
                        this.addActionButton('call_club_king', _('Club'), 'onCallClubKing');
                        this.addActionButton('call_diamong_king', _('Diamond'), 'onCallDiamondKing');
                        break;
                }
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods

        // Get card unique identifier based on its color and value
        getCardUniqueId: function (color, value) {
            return (color - 1) * 22 + (value - 1);
        },

        getColorAndValueFromUniqueId: function (unique_id) {
            var color = Math.floor(unique_id / 22) + 1;
            var value = unique_id % 22 + 1;
            return { color: color, value: value };
        },

        // Play a card on table for a player
        playCardOnTable : function(player_id, color, value, card_id) {
            // player_id => direction
            var card_x_pos = value - 1;
            dojo.place(this.format_block('jstpl_cardontable', {
                x : this.cardwidth * card_x_pos,
                y : this.cardheight * (color - 1),
                player_id : player_id
            }), 'playertablecard_' + player_id);

            if (player_id != this.player_id) {
                // Some opponent played a card
                // Move card from player panel
                this.placeOnObject('cardontable_' + player_id, 'overall_player_board_' + player_id);
            } else {
                // You played a card. If it exists in your hand, move card from there and remove
                // corresponding item

                if ($('myhand_item_' + card_id)) {
                    this.placeOnObject('cardontable_' + player_id, 'myhand_item_' + card_id);
                    this.playerHand.removeFromStockById(card_id);
                }
            }

            // In any case: move it to its final destination
            this.slideToObject('cardontable_' + player_id, 'playertablecard_' + player_id).play();
        },

        playCardInTalon : function(talon_id, color, value, card_id) {
            var card_x_pos = value - 1;
            dojo.place(this.format_block('jstpl_cardintalon', {
                x : this.cardwidth * card_x_pos,
                y : this.cardheight * (color - 1),
                card_id : card_id
            }), talon_id);
        },

        // /////////////////////////////////////////////////
        // // Player's action

        onPlayerHandSelectionChanged: function () {
            var items = this.playerHand.getSelectedItems();

            if (items.length > 0) {
                var action = 'playCard';
                if (this.checkAction(action, true)) {
                    // Can play a card
                    var card_id = items[0].id;
                    console.log("on playCard " + card_id);
                    this.ajaxcall(
                        "/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                            id : card_id,
                            lock : true
                        }, this, function(result) {
                        }, function(is_error) {
                    }
                    );

                    this.playerHand.unselectAll();
                } else if (this.checkAction('discardCard')) {
                    action = 'discardCard';
                    var card_id = items[0].id;
                    console.log("on discardCard " + card_id);
                    this.ajaxcall(
                        "/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                            id : card_id,
                            lock : true
                        }, this, function(result) {
                        }, function(is_error) {
                    }
                    );
                    this.playerHand.unselectAll();
                } else {
                    this.playerHand.unselectAll();
                }
            }
        },

        onCallSpadeKing: function () {
            if (!this.checkAction('callSpadeKing')) {
                return;
            }
            var action = 'callSpadeKing';
            console.log("on callSpadeKing");
            this.ajaxcall(
                "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
                {
                    lock : true
                }, this, function(result) {
                }, function(is_error) {
                }
            );
        },

        onCallClubKing: function () {
            if (!this.checkAction('callClubKing')) {
                return;
            }
            var action = 'callClubKing';
            console.log("on callClubKing");
            this.ajaxcall(
                "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
                {
                    lock : true
                }, this, function(result) {
                }, function(is_error) {
                }
            );
        },

        onCallHeartKing: function () {
            if (!this.checkAction('callHeartKing')) {
                return;
            }
            var action = 'callHeartKing';
            console.log("on callHeartKing");
            this.ajaxcall(
                "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
                {
                    lock: true
                }, this, function (result) {
                }, function (is_error) {
                }
            );
        },

        onCallDiamondKing: function () {
            if (!this.checkAction('callDiamondKing')) {
                return;
            }
            var action = 'callDiamondKing';
            console.log("on callDiamondKing");
            this.ajaxcall(
                "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
                {
                    lock: true
                }, this, function (result) {
                }, function (is_error) {
                }
            );
        },

        onTalonClickChooseCards: function (event) {
            if (!this.checkAction('chooseCards')) {
                return;
            }
            var talon = event.target;
            if (talon.classList.contains('cardontable')) {
                // Clicked a card, get the parent.
                talon = talon.parentNode;
            }
            var cards = [];
            for (var i = 0; i < talon.children.length; i++) {
                var child = talon.children[i];
                var card = this.getColorAndValueFromUniqueId(child.id.substring(12))
                cards.push(card.color + '_' + card.value);
            }
            var action = 'chooseCards';
            console.log("on chooseCards ", cards, talon.id);
            this.ajaxcall(
                "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
                {
                    talon: talon.id,
                    cards: cards.join(' '),
                    lock: true
                }, this, function (result) {
                }, function (is_error) {
                }
            );
        },

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        setupNotifications : function() {
            console.log('notifications subscriptions setup');

            dojo.subscribe('newHand', this, "notif_newHand");
            dojo.subscribe('newCards', this, "notif_newCards");
            dojo.subscribe('talonChosen', this, "notif_talonChosen");
            dojo.subscribe('discardCard', this, "notif_discardCard");
            dojo.subscribe('playCard', this, "notif_playCard");
            dojo.subscribe('trickWin', this, "notif_trickWin");
            this.notifqueue.setSynchronous('trickWin', 1000);
            dojo.subscribe('giveAllCardsToPlayer', this, "notif_giveAllCardsToPlayer");
            dojo.subscribe('newScores', this, "notif_newScores");
        },

        notif_newHand : function(notif) {
            this.playerHand.removeAll();

            for ( var i in notif.args.cards) {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }
        },

        notif_newCards: function (notif) {
            console.log( "on newCards ", notif.args.cards);
            for ( var i in notif.args.cards) {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
                console.log("new card " + this.getCardUniqueId(color, value) + " " + color + " " + value);
            }
        },

        notif_discardCard: function (notif) {
            console.log("notif_discardCard", notif.args);
            this.playerHand.removeFromStockById(notif.args.card_id)
        },

        notif_playCard : function(notif) {
            // Play a card on the table
            this.playCardOnTable(notif.args.player_id, notif.args.color, notif.args.value, notif.args.card_id);
        },

        notif_trickWin: function (notif) {
            // We do nothing here (just wait in order players can view the 4
            // cards played before they're gone.
        },

        notif_giveAllCardsToPlayer : function(notif) {
            // Move all cards on table to given table, then destroy them
            var winner_id = notif.args.player_id;
            for ( var player_id in this.gamedatas.players) {
                var anim = this.slideToObject('cardontable_' + player_id, 'overall_player_board_' + winner_id);
                dojo.connect(anim, 'onEnd', function(node) {
                    dojo.destroy(node);
                });
                anim.play();
            }
        },

        notif_newScores : function(notif) {
            // Update players' scores
            for ( var player_id in notif.args.newScores) {
                this.scoreCtrl[player_id].toValue(notif.args.newScores[player_id]);
            }
        },

        notif_talonChosen: function (notif) {
            if (notif.args.player_id != this.player_id) {
                var anim = this.slideToObject(notif.args.talon_id, 'overall_player_board_' + notif.args.player_id);
                dojo.connect(anim, 'onEnd', function (node) {
                    dojo.destroy(node);
                });
                anim.play();
            } else {
                var anim = this.slideToObject(notif.args.talon_id, 'myhand');
                dojo.connect(anim, 'onEnd', function (node) {
                    dojo.destroy(node);
                });
                anim.play();
            }
        },
   });
});
