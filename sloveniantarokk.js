/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SlovenianTarokk implementation : © Mikko Saari <mikko@mikkosaari.fi>
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
        constructor: function () {
            console.log('sloveniantarokk constructor');

            this.cardwidth = 100;
            this.cardheight = 175;

            this.bids = {};
            this.bids.klop = 1;
            this.bids.three = 2;
            this.bids.two = 3;
            this.bids.one = 4;
            this.bids.solo_three = 5;
            this.bids.solo_two = 6;
            this.bids.solo_one = 7;
            this.bids.beggar = 8;
            this.bids.solo_without = 9;
            this.bids.open_beggar = 10;
            this.bids.colour_valat_without = 11;
            this.bids.colour_valat = 12;
            this.bids.valat = 13;

            this.suits = {};
            this.suits.spades = 1;
            this.suits.clubs = 2;
            this.suits.hearts = 3;
            this.suits.diamonds = 4;
            this.suits.trump = 5;

            this.announcement_values = {}
            this.announcement_values.basic = 1;
            this.announcement_values.kontra = 2;
            this.announcement_values.rekontra = 4;
            this.announcement_values.subkontra = 8;
            this.announcement_values.mordkontra = 16;
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
        setup: function (gamedatas) {
            console.log("Starting game setup");

            // Player hand
            this.playerHand = new ebg.stock();
            this.playerHand.create(this, $('myhand'), this.cardwidth, this.cardheight);
            this.playerHand.image_items_per_row = 22;

            dojo.connect(this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged');

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
            for (var i in this.gamedatas.hand) {
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

            console.log("Ending game setup");
        },

        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function (stateName, args) {
            console.log('Entering state: ' + stateName);

            switch (stateName) {
                case 'newHand':
                    this.gamedatas.highBid = 2;
                    break;
                case 'exchange':
                    dojo.style('talonexchange', 'display', 'block');

                    var talonSplit = 3;
                    if (this.gamedatas.highBid == this.bids.three || this.gamedatas.highBid == this.bids.solo_three) {
                        dojo.place(this.format_block('jstpl_talonexchange_33', {}), 'talonexchange');
                    } else if (this.gamedatas.highBid == this.bids.two || this.gamedatas.highBid == this.bids.solo_two) {
                        dojo.place(this.format_block('jstpl_talonexchange_222', {}), 'talonexchange');
                        talonSplit = 2;
                    } else if (this.gamedatas.highBid == this.bids.one || this.gamedatas.highBid == this.bids.solo_one) {
                        dojo.place(this.format_block('jstpl_talonexchange_111111', {}), 'talonexchange');
                        talonSplit = 1;
                    }
                    var counter = 0;
                    for (i in this.gamedatas.cardsintalon) {
                        counter++;
                        var card = this.gamedatas.cardsintalon[i];
                        var talon = 'talon_';
                        switch (talonSplit) {
                            case 3:
                                talon += '33_' + Math.ceil(counter / 3);
                                break;
                            case 2:
                                talon += '222_' + Math.ceil(counter / 2);
                                break;
                            case 1:
                                talon += '111111_' + counter;
                                break;
                        }
                        this.playCardInTalon(talon, card.type, card.type_arg, this.getCardUniqueId(card.type, card.type_arg))

                    }
                    switch (talonSplit) {
                        case 3:
                            dojo.connect(dojo.byId('talon_33_1'), 'onclick', this, 'onTalonClickChooseCards');
                            dojo.connect(dojo.byId('talon_33_2'), 'onclick', this, 'onTalonClickChooseCards');
                            break;
                        case 2:
                            dojo.connect(dojo.byId('talon_222_1'), 'onclick', this, 'onTalonClickChooseCards');
                            dojo.connect(dojo.byId('talon_222_2'), 'onclick', this, 'onTalonClickChooseCards');
                            dojo.connect(dojo.byId('talon_222_3'), 'onclick', this, 'onTalonClickChooseCards');
                            break;
                        case 1:
                            dojo.connect(dojo.byId('talon_111111_1'), 'onclick', this, 'onTalonClickChooseCards');
                            dojo.connect(dojo.byId('talon_111111_2'), 'onclick', this, 'onTalonClickChooseCards');
                            dojo.connect(dojo.byId('talon_111111_3'), 'onclick', this, 'onTalonClickChooseCards');
                            dojo.connect(dojo.byId('talon_111111_4'), 'onclick', this, 'onTalonClickChooseCards');
                            dojo.connect(dojo.byId('talon_111111_5'), 'onclick', this, 'onTalonClickChooseCards');
                            dojo.connect(dojo.byId('talon_111111_6'), 'onclick', this, 'onTalonClickChooseCards');
                            break;
                    }
                    break;
                case 'playerBid':
                    if (this.gamedatas.highBid == 0) {
                        this.gamedatas.highBid = 2;
                    }
                    break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function (stateName) {
            console.log('Leaving state: ' + stateName);

            switch (stateName) {
                case 'exchange':
                    dojo.style('talonexchange', 'display', 'none');
                    break;
            }
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function (stateName, args) {
            console.log('onUpdateActionButtons: ' + stateName);

            if (this.isCurrentPlayerActive()) {
                switch (stateName) {
                    case 'kingCalling':
                        this.addActionButton('call_spade_king', _('Spade'), 'onCallSpadeKing');
                        this.addActionButton('call_heart_king', _('Heart'), 'onCallHeartKing');
                        this.addActionButton('call_club_king', _('Club'), 'onCallClubKing');
                        this.addActionButton('call_diamong_king', _('Diamond'), 'onCallDiamondKing');
                        break;
                    case 'playerBid':
                        var playerMinimumBid = Number(this.gamedatas.highBid) + Number("1")
                        if (this.gamedatas.highBidder > 0 && playerMinimumBid > 2) {
                            // Someone has already bid; if current player has a higher priority, they can bid the same
                            if (this.hasHigherPriority(this.player_id, this.gamedatas.highBidder)) {
                                playerMinimumBid = this.gamedatas.highBid;
                            }
                        }

                        var bids = this.possibleBids(playerMinimumBid)
                        for (var i in bids) {
                            var bid = bids[i];
                            this.addActionButton(bid.id, bid.name, bid.action);
                        }
                        break;
                    case 'finalBid':
                        var playerMinimumBid = this.gamedatas.highBid
                        if (this.player_id == this.gamedatas.forehand && this.gamedatas.highBid == 2) {
                            // Forehand can bid klop if the high bid is three.
                            playerMinimumBid = 1;
                        }

                        var bids = this.possibleBids(playerMinimumBid).filter(bid => bid.id != 'pass');
                        for (var i in bids) {
                            var bid = bids[i];
                            var action = bid.action.replace('onBid', 'onFinalBid');
                            this.addActionButton(bid.id, bid.name, action);
                        }
                        break;
                    case 'upgradeToColourValat':
                        this.addActionButton('upgrade_to_colour_valat', _('Raise bid to colour valat'), 'onUpgradeToColourValat');
                        this.addActionButton('keep_current_bid', _('Keep current bid'), 'onKeepCurrentBid');
                        break;
                    case 'announcements':
                        var announcement = _('game');
                        var verb = '';


                        if (this.gamedatas.valatTeam == "0") {
                            if (this.playerCanKontra('game')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.gameValue);
                                this.addActionButton('announce_game', verb + ' ' + announcement, 'onAnnounceGame');
                            }

                            announcement = _('Trula');
                            if (this.gamedatas.trulaTeam == "0") {
                                this.addActionButton('announce_trula', announcement, 'onAnnounceTrula');
                            }
                            if (this.playerCanKontra('Trula')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.trulaValue);
                                this.addActionButton('announce_trula', verb + ' ' + announcement, 'onAnnounceTrula');
                            }

                            announcement = _('Kings');
                            if (this.gamedatas.kingsTeam == "0") {
                                this.addActionButton('announce_kings', announcement, 'onAnnounceKings');
                            }
                            if (this.playerCanKontra('Kings')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.kingsValue);
                                this.addActionButton('announce_kings', verb + ' ' + announcement, 'onAnnounceKings');
                            }

                            announcement = _('King ultimo');
                            if (this.gamedatas.kingUltimoTeam == "0"
                                && this.gamedatas.calledKing != 0
                                && this.hasCardInHand(this.gamedatas.calledKing, 14)) {
                                this.addActionButton('announce_king_ultimo', announcement, 'onAnnounceKingUltimo');
                            }
                            if (this.playerCanKontra('King ultimo')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.kingUltimoValue);
                                this.addActionButton('announce_king_ultimo', verb + ' ' + announcement, 'onAnnounceKingUltimo');
                            }

                            announcement = _('Pagat ultimo');
                            if (this.gamedatas.pagatUltimoTeam == "0"
                                && this.hasCardInHand(this.suits.trump, 1)) {
                                this.addActionButton('announce_pagat_ultimo', announcement, 'onAnnouncePagatUltimo');
                            }
                            if (this.playerCanKontra('Pagat ultimo')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.pagatUltimoValue);
                                this.addActionButton('announce_pagat_ultimo', verb + ' ' + announcement, 'onAnnouncePagatUltimo');
                            }
                        }

                        announcement = _('Valat');
                        if (this.gamedatas.valatTeam == "0") {
                            this.addActionButton('announce_valat', announcement, 'onAnnounceValat');
                        }
                        if (this.playerCanKontra('Valat')) {
                            verb = this.getAnnouncementVerb(this.gamedatas.valatValue);
                            this.addActionButton('announce_valat', verb + ' ' + announcement, 'onAnnounceValat');
                        }

                        if (this.gamedatas.playerAnnouncements > 0) {
                            this.addActionButton('announce_done', _('Done'), 'onAnnounceDone');
                        } else {
                            this.addActionButton('announce_pass', _('Pass'), 'onAnnouncePass');
                        }

                        break;
                }
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods

        hasCardInHand: function (color, value) {
            var cards = this.gamedatas.hand;
            for (var i in cards) {
                var card = cards[i];
                if (card.color == color && card.value == value) {
                    return true;
                }
            }
            return false;
        },

        getPlayerTeam: function () {
            return this.gamedatas.players[this.player_id].team;
        },

        getAnnouncementVerb: function (value) {
            switch (value) {
                case this.announcement_values.basic:
                    return _('Kontra');
                case this.announcement_values.kontra:
                    return _('Rekontra');
                case this.announcement_values.rekontra:
                    return _('Subkontra');
                case this.announcement_values.subkontra:
                    return _('Mordkontra');
            }
            return '';
        },

        playerInTeam: function (team) {
            return this.getPlayerTeam().substring(0, team.length) == team;
        },

        declarerPartnerHidden: function () {
            for (var player_id in this.gamedatas.players) {
                if (this.gamedatas.players[player_id].team == 'declarer_hidden') {
                    return true;
                }
            }
            return false;
        },

        otherOpponentHidden: function () {
            for (var player_id in this.gamedatas.players) {
                if (player_id == this.player_id) {
                    continue;
                }
                if (this.gamedatas.players[player_id].team == 'opponent_hidden') {
                    return true;
                }
            }
            return false;
        },

        playerCanKontra: function (announcement) {
            var team = '';
            var value = this.announcement_values.basic;
            switch (announcement) {
                case 'game':
                    team = 'declarer';
                    value = this.gamedatas.gameValue;
                    break;
                case 'Trula':
                    team = this.gamedatas.trulaTeam;
                    value = this.gamedatas.trulaValue;
                    break;
                case 'Kings':
                    team = this.gamedatas.kingsTeam;
                    value = this.gamedatas.kingsValue;
                    break;
                case 'King ultimo':
                    team = this.gamedatas.kingUltimoTeam;
                    value = this.gamedatas.kingUltimoValue;
                    break;
                case 'Pagat ultimo':
                    team = this.gamedatas.pagatUltimoTeam;
                    value = this.gamedatas.pagatUltimoValue;
                    break;
                case 'Valat':
                    team = this.gamedatas.valatTeam;
                    value = this.gamedatas.valatValue;
                    break;
                default:
                    team = false;
            }
            if (!team) {
                return false;
            }

            // Basic level checking:
            if (value < this.announcement_values.basic) {
                // Not announced, can't kontra.
                return false;
            }
            if ((value == this.announcement_values.basic
                || value == this.announcement_values.rekontra)
                 && this.playerInTeam(team)) {
                // Can't kontra or subkontra your own team.
                return false;
            }
            if ((value == this.announcement_values.kontra
                || value == this.announcement_values.subkontra)
                 && !this.playerInTeam(team)) {
                // Can't rekontra or mordkontra the opposing team.
                return false;
            }

            if (team == 'opponent') {
                if (this.player_id = this.gamedatas.highBidder && this.declarerPartnerHidden()) {
                    // Player is declarer and partner is hidden:
                    // Declarer can't know the identity of the player who announced the trula.
                    return false;
                }
                // Declarer's partner can kontra, opponent can't.
                return this.playerInTeam('declarer');
            } else { // Declarer's team made the announcement.
                if (this.declarerPartnerHidden() && this.otherOpponentHidden()) {
                    // The other opponent and the declarer's partner is hidden:
                    // Player can't know the identity of the player who announced the trula.
                    return false;
                }
                // Player knows either the other opponent or the declarer's partner.
                return true;
            }
            return false; // Shouldn't happen...
        },

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

        playCardInVitamin: function (color, value) {
            console.log("playing vitamin " + color + " " + value);
            var card_x_pos = value - 1;
            dojo.place(this.format_block('jstpl_cardintalon', {
                x : this.cardwidth * card_x_pos,
                y: this.cardheight * (color - 1),
                card_id: 'vitamin'
            }), 'vitamin');
        },

        possibleBids: function (minimumBid) {
            var bids = [
                { name: _('Klop'), value: 1, action: 'onBidKlop', id: 'bid_klop' },
                { name: _('Three'), value: 2, action: 'onBidThree', id: 'bid_three' },
                { name: _('Two'), value: 3, action: 'onBidTwo', id: 'bid_two' },
                { name: _('One'), value: 4, action: 'onBidOne', id: 'bid_one' },
                { name: _('Solo three'), value: 5, action: 'onBidSoloThree', id: 'bid_solo_three' },
                { name: _('Solo two'), value: 6, action: 'onBidSoloTwo', id: 'bid_solo_two' },
                { name: _('Solo one'), value: 7, action: 'onBidSoloOne', id: 'bid_solo_one' },
                { name: _('Beggar'), value: 8, action: 'onBidBeggar', id: 'bid_beggar' },
                { name: _('Solo without'), value: 9, action: 'onBidSoloWithout', id: 'bid_solo_without' },
                { name: _('Open beggar'), value: 10, action: 'onBidOpenBeggar', id: 'bid_open_beggar' },
                { name: _('Colour valat without'), value: 11, action: 'onBidColourValat', id: 'bid_colour_valat' },
                { name: _('Valat without'), value: 13, action: 'onBidValat', id: 'bid_valat' },
                { name: _('Pass'), value: 14, action: 'onPass', id: 'pass' },
            ];
            return bids.filter(bid => bid.value >= minimumBid);
        },

        hasHigherPriority: function (activePlayer, highBidder) {
            // If this.priorityOrder is not an array, construct it
            if (!dojo.isArray(this.priorityOrder)) {
                this.priorityOrder = [
                    this.gamedatas.forehand,
                    this.gamedatas.secondPriority,
                    this.gamedatas.thirdPriority,
                    this.gamedatas.fourthPriority
                ];
            }

            for (var i in this.priorityOrder) {
                var player = this.priorityOrder[i];
                if (player == activePlayer) {
                    return true;
                }
                if (player == highBidder) {
                    return false;
                }
            }

            return false;
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
                } else if (this.checkAction('discardCard',true)) {
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
            this.checkAndAjaxCall('callSpadeKing');
        },

        onCallClubKing: function () {
            this.checkAndAjaxCall('callClubKing');
        },

        onCallHeartKing: function () {
            this.checkAndAjaxCall('callHeartKing');
        },

        onCallDiamondKing: function () {
            this.checkAndAjaxCall('callDiamondKing');
        },

        onBidKlop: function () {
            this.checkAndAjaxCall('bid', { bid: 1 });
        },

        onBidThree: function () {
            this.checkAndAjaxCall('bid', { bid: 2 });
        },

        onBidTwo: function () {
            this.checkAndAjaxCall('bid', { bid: 3 });
        },

        onBidOne: function () {
            this.checkAndAjaxCall('bid', { bid: 4 });
        },

        onBidSoloThree: function () {
            this.checkAndAjaxCall('bid', { bid: 5 });
        },

        onBidSoloTwo: function () {
            this.checkAndAjaxCall('bid', { bid: 6 });
        },

        onBidSoloOne: function () {
            this.checkAndAjaxCall('bid', { bid: 7 });
        },

        onBidBeggar: function () {
            this.checkAndAjaxCall('bid', { bid: 8 });
        },

        onBidSoloWithout: function () {
            this.checkAndAjaxCall('bid', { bid: 9 });
        },

        onBidOpenBeggar: function () {
            this.checkAndAjaxCall('bid', { bid: 10 });
        },

        onBidColourValat: function () {
            this.checkAndAjaxCall('bid', { bid: 11 });
        },

        onBidValat: function () {
            this.checkAndAjaxCall('bid', { bid: 13 });
        },

        onPass: function () {
            this.checkAndAjaxCall('pass');
        },

        onFinalBidKlop: function () {
            this.checkAndAjaxCall('finalBid', { bid: 1 });
        },

        onFinalBidThree: function () {
            this.checkAndAjaxCall('finalBid', { bid: 2 });
        },

        onFinalBidTwo: function () {
            this.checkAndAjaxCall('finalBid', { bid: 3 });
        },

        onFinalBidOne: function () {
            this.checkAndAjaxCall('finalBid', { bid: 4 });
        },

        onFinalBidSoloThree: function () {
            this.checkAndAjaxCall('finalBid', { bid: 5 });
        },

        onFinalBidSoloTwo: function () {
            this.checkAndAjaxCall('finalBid', { bid: 6 });
        },

        onFinalBidSoloOne: function () {
            this.checkAndAjaxCall('finalBid', { bid: 7 });
        },

        onFinalBidBeggar: function () {
            this.checkAndAjaxCall('finalBid', { bid: 8 });
        },

        onFinalBidSoloWithout: function () {
            this.checkAndAjaxCall('finalBid', { bid: 9 });
        },

        onFinalBidOpenBeggar: function () {
            this.checkAndAjaxCall('finalBid', { bid: 10 });
        },

        onFinalBidColourValat: function () {
            this.checkAndAjaxCall('finalBid', { bid: 11 });
        },

        onFinalBidValat: function () {
            this.checkAndAjaxCall('finalBid', { bid: 13 });
        },

        onUpgradeToColourValat: function () {
            this.checkAndAjaxCall('upgradeToColourValat');
        },

        onKeepCurrentBid: function () {
            this.checkAndAjaxCall('keepCurrentBid');
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

        onAnnounceGame: function () {
            this.checkAndAjaxCall('makeAnnouncement', { announcement: 1 });
        },

        onAnnounceTrula: function () {
            this.checkAndAjaxCall('makeAnnouncement', { announcement: 2 });
        },

        onAnnounceKings: function () {
            this.checkAndAjaxCall('makeAnnouncement', { announcement: 3 });
        },

        onAnnounceKingUltimo: function () {
            this.checkAndAjaxCall('makeAnnouncement', { announcement: 4 });
        },

        onAnnouncePagatUltimo: function () {
            this.checkAndAjaxCall('makeAnnouncement', { announcement: 5 });
        },

        onAnnounceValat: function () {
            this.checkAndAjaxCall('makeAnnouncement', { announcement: 6 });
        },

        onAnnounceDone: function () {
            this.checkAndAjaxCall('passAnnouncement', { announcement: 'done' });
        },

        onAnnouncePass: function () {
            this.checkAndAjaxCall('passAnnouncement', { announcement: 'pass' });
        },

        checkAndAjaxCall: function (action, parameters) {
            if (!this.checkAction(action)) {
                return;
            }
            console.log("on " + action);
            if (parameters === undefined) {
                parameters = {};
            }
            parameters.lock = true;
            this.ajaxcall(
                "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
                parameters, this, function (result) {
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
            dojo.subscribe('setPriorityOrder', this, "notif_setPriorityOrder");
            dojo.subscribe('updateBids', this, "notif_updateBids");
            dojo.subscribe('talonChosen', this, "notif_talonChosen");
            dojo.subscribe('discardCard', this, "notif_discardCard");
            dojo.subscribe('upgradeToColourValat', this, "notif_upgradeToColourValat");
            dojo.subscribe('playCard', this, "notif_playCard");
            dojo.subscribe('trickWin', this, "notif_trickWin");
            this.notifqueue.setSynchronous('trickWin', 1000);
            dojo.subscribe('vitamin', this, "notif_vitamin");
            this.notifqueue.setSynchronous('vitamin', 1000);
            dojo.subscribe('giveVitamin', this, "notif_giveVitamin");
            dojo.subscribe('giveAllCardsToPlayer', this, "notif_giveAllCardsToPlayer");
            dojo.subscribe('newScores', this, "notif_newScores");
            dojo.subscribe('callKing', this, "notif_callKing");
            dojo.subscribe('playerTeamUpdate', this, "notif_playerTeamUpdate");
            dojo.subscribe('makeAnnouncement', this, "notif_makeAnnouncement");
        },

        notif_newHand : function(notif) {
            this.playerHand.removeAll();

            for ( var i in notif.args.cards) {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }

            this.gamedatas.calledKing = 0;
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

        notif_setPriorityOrder: function (notif) {
            console.log( "on setPriorityOrder ", notif.args.priorityOrder);
            this.gamedatas.forehand = notif.args.forehand;

            this.priorityOrder = [
                this.gamedatas.forehand,
                notif.args.secondPriority,
                notif.args.thirdPriority,
                notif.args.fourthPriority
            ];

            this.gamedatas.highBidder = 0;

            console.log("received forehand ", this.forehand);
        },

        notif_updateBids: function (notif) {
            console.log("on updateBids ", notif.args.highBid + " " + notif.args.highBidder);
            this.gamedatas.highBidder = notif.args.highBidder;
            this.gamedatas.highBid = notif.args.highBid;
        },

        notif_discardCard: function (notif) {
            console.log("notif_discardCard", notif.args);
            this.playerHand.removeFromStockById(notif.args.card_id)
        },

        notif_upgradeToColourValat: function (notif) {
            this.gamedatas.highBid = this.bids.colour_valat;
        },

        notif_playCard : function(notif) {
            // Play a card on the table
            this.playCardOnTable(notif.args.player_id, notif.args.color, notif.args.value, notif.args.card_id);
        },

        notif_trickWin: function (notif) {
            // We do nothing here (just wait in order players can view the 4
            // cards played before they're gone.
        },

        notif_vitamin: function (notif) {
            console.log("Playing card to vitamin ", notif.args.color, notif.args.value);
            this.playCardInVitamin(notif.args.color, notif.args.value);
        },

        notif_giveVitamin: function (notif) {
            console.log("Giving vitamin to ", notif.args.player_id);
            var anim = this.slideToObject('cardontable_vitamin', 'overall_player_board_' + notif.args.player_id);
            dojo.connect(anim, 'onEnd', function(node) {
                dojo.destroy(node);
            });
            anim.play();
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

        notif_callKing: function (notif) {
            this.gamedatas.calledKing = notif.args.color;
        },

        notif_playerTeamUpdate: function (notif) {
            this.gamedatas.players = notif.args.players;
        },

        notif_makeAnnouncement: function (notif) {
            var teamName = notif.args.team == 1 ? "declarer" : "opponent";

            switch (notif.args.announcement) {
                case 1:
                    this.gamedatas.gameValue = notif.args.newValue;
                    break;
                case 2:
                    this.gamedatas.trulaTeam = teamName;
                    this.gamedatas.trulaValue = newValue;
                    break;
                case 3:
                    this.gamedatas.kingsTeam = teamName;
                    this.gamedatas.kingsValue = newValue;
                    break;
                case 4:
                    this.gamedatas.kingUltimoTeam = teamName;
                    this.gamedatas.kingUltimoValue = newValue;
                    break;
                case 5:
                    this.gamedatas.pagatUltimoTeam = teamName;
                    this.gamedatas.pagatUltimoValue = newValue;
                    break;
                case 6:
                    this.gamedatas.valatTeam = teamName;
                    this.gamedatas.valatValue = newValue;
                    break;
            }

            this.gamedatas.playerAnnouncements = notif.args.playerAnnouncements;
        },
   });
});
