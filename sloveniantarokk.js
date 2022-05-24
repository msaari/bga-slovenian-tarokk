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

            this.updateRadli();
            this.updatePlayerGame();

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
                    this.gamedatas.gamevalue = 1;
                    this.gamedatas.trulaPlayer = 0;
                    this.gamedatas.trulaValue = 1;
                    this.gamedatas.kingsPlayer = 0;
                    this.gamedatas.kingsValue = 1;
                    this.gamedatas.kingUltimoPlayer = 0;
                    this.gamedatas.kingUltimoValue = 1;
                    this.gamedatas.pagatUltimoPlayer = 0;
                    this.gamedatas.pagatUltimoValue = 1;
                    this.gamedatas.valatPlayer = 0;
                    this.gamedatas.valatValue = 0;
                    dojo.empty("talonexchange");
                    this.emptyPlayerGame();
                    this.emptyBeggarHand();
                    for (var player_id in this.gamedatas.players) {
                        this.gamedatas.players[player_id].team = '';
                    }
                    break;
                case 'exchange':
                    dojo.style('talonexchange', 'display', 'block');

                    var talonSplit = 3;
                    if (this.gamedatas.highBid == this.bids.three || this.gamedatas.highBid == this.bids.solo_three) {
                        dojo.place(this.format_block('jstpl_talonexchange_33', {}), "talonexchange");
                    } else if (this.gamedatas.highBid == this.bids.two || this.gamedatas.highBid == this.bids.solo_two) {
                        dojo.place(this.format_block('jstpl_talonexchange_222', {}), "talonexchange");
                        talonSplit = 2;
                    } else if (this.gamedatas.highBid == this.bids.one || this.gamedatas.highBid == this.bids.solo_one) {
                        dojo.place(this.format_block('jstpl_talonexchange_111111', {}), "talonexchange");
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
                case 'newTrick':
                    if (this.gamedatas.highBid == this.bids.open_beggar) {
                        dojo.style('openbeggarhand', 'display', 'block');
                    }
                    break;
                case 'endHand':
                    dojo.style('openbeggarhand', 'display', 'none');
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

                        console.log(this.gamedatas);

                        if (this.gamedatas.valatPlayer == "0") {
                            if (this.playerCanKontra('game')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.gameValue);
                                this.addActionButton('announce_game', verb + ' ' + announcement, 'onAnnounceGame');
                            }

                            announcement = _('Trula');
                            if (this.gamedatas.trulaPlayer == "0") {
                                this.addActionButton('announce_trula', announcement, 'onAnnounceTrula');
                            }
                            if (this.playerCanKontra('Trula')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.trulaValue);
                                this.addActionButton('announce_trula', verb + ' ' + announcement, 'onAnnounceTrula');
                            }

                            announcement = _('Kings');
                            if (this.gamedatas.kingsPlayer == "0") {
                                this.addActionButton('announce_kings', announcement, 'onAnnounceKings');
                            }
                            if (this.playerCanKontra('Kings')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.kingsValue);
                                this.addActionButton('announce_kings', verb + ' ' + announcement, 'onAnnounceKings');
                            }

                            announcement = _('King ultimo');
                            if (this.gamedatas.kingUltimoPlayer == "0"
                                && this.gamedatas.calledKing != 0
                                && this.hasCardInHand(this.gamedatas.calledKing, 14)) {
                                this.addActionButton('announce_king_ultimo', announcement, 'onAnnounceKingUltimo');
                            }
                            if (this.playerCanKontra('King ultimo')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.kingUltimoValue);
                                this.addActionButton('announce_king_ultimo', verb + ' ' + announcement, 'onAnnounceKingUltimo');
                            }

                            announcement = _('Pagat ultimo');
                            if (this.gamedatas.pagatUltimoPlayer == "0"
                                && this.hasCardInHand(this.suits.trump, 1)) {
                                this.addActionButton('announce_pagat_ultimo', announcement, 'onAnnouncePagatUltimo');
                            }
                            if (this.playerCanKontra('Pagat ultimo')) {
                                verb = this.getAnnouncementVerb(this.gamedatas.pagatUltimoValue);
                                this.addActionButton('announce_pagat_ultimo', verb + ' ' + announcement, 'onAnnouncePagatUltimo');
                            }
                        }

                        announcement = _('Valat');
                        if (this.gamedatas.valatPlayer == "0") {
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
            console.log("Looking for " + color + ", " + value + " in ", cards);
            for (var i in cards) {
                var card = cards[i];
                if (card.type == color && card.type_arg == value) {
                    return true;
                }
            }
            return false;
        },

        getPlayerTeam: function (player_id = 0) {
            if (parseInt(player_id, 10) == 0) {
                player_id = this.player_id;
            }
            return this.gamedatas.players[player_id].team;
        },

        getAnnouncementVerb: function (value) {
            switch (parseInt(value, 10)) {
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

        getTeamName: function (team) {
            if (team == 1) {
                return 'declarer';
            }
            if (team == 2) {
                return 'opponent';
            }
            return false;
        },

        playerCanKontra: function (announcement) {
            var team = '';
            var value = this.announcement_values.basic;
            switch (announcement) {
                case 'game':
                    team = 'declarer';
                    if (this.gamedatas.gameValue == undefined) {
                        this.gamedatas.gameValue = 1;
                    }
                    value = this.gamedatas.gameValue;
                    break;
                case 'Trula':
                    team = this.getPlayerTeam(this.gamedatas.trulaPlayer);
                    value = this.gamedatas.trulaValue;
                    break;
                case 'Kings':
                    team = this.getPlayerTeam(this.gamedatas.kingsPlayer);
                    value = this.gamedatas.kingsValue;
                    break;
                case 'King ultimo':
                    team = this.getPlayerTeam(this.gamedatas.kingUltimoPlayer);
                    value = this.gamedatas.kingUltimoValue;
                    break;
                case 'Pagat ultimo':
                    team = this.getPlayerTeam(this.gamedatas.pagatUltimoPlayer);
                    value = this.gamedatas.pagatUltimoValue;
                    break;
                case 'Valat':
                    team = this.getPlayerTeam(this.gamedatas.valatPlayer);
                    value = this.gamedatas.valatValue;
                    break;
                default:
                    team = false;
            }
            if (!team) {
                return false;
            }

            console.log("player in team " + team + ": " + this.playerInTeam(team));

            console.log(announcement, team, value)
            // Basic level checking:
            if (value < this.announcement_values.basic) {
                // Not announced, can't kontra.
                console.log(announcement + ' not announced, can\'t kontra.');
                return false;
            }
            if ((value == this.announcement_values.basic
                || value == this.announcement_values.rekontra)
                && this.playerInTeam(team)) {
                console.log(announcement + ' cannot kontra or subkontra your own team.');
                // Can't kontra or subkontra your own team.
                return false;
            }
            if ((value == this.announcement_values.kontra
                || value == this.announcement_values.subkontra)
                 && !this.playerInTeam(team)) {
                console.log(announcement + ' cannot rekontra or mordkontra opposing team.');
                // Can't rekontra or mordkontra the opposing team.
                return false;
            }

            if (announcement == 'game') {
                console.log('game announcement can be kontrad.');
                return true;
            }

            if (team == 'opponent' || team == 'opponent_hidden') {
                if (this.player_id == this.gamedatas.highBidder && this.declarerPartnerHidden()) {
                    console.log( announcement + ' cannot kontra when partner is hidden.');
                    // Player is declarer and partner is hidden:
                    // Declarer can't know the identity of the player who announced the trula.
                    return false;
                }
                console.log(announcement + ' ' + this.playerInTeam('declarer'));
                // Declarer's partner can kontra, opponent can't.
                return this.playerInTeam('declarer');
            } else { // Declarer's team made the announcement.
                if (team == 'declarer_hidden' && this.otherOpponentHidden()) {
                    // The other opponent and the declarer's partner is hidden:
                    // Player can't know the identity of the player who announced the trula.
                    console.log(announcement + ' cannot kontra when partner and other opponent are hidden.');
                    return false;
                } else {
                    // Announced by declarer, can kontra.
                    console.log(announcement + ' announced by declarer, can kontra.');
                    return true;
                }
            }
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
            if (this.gamedatas.compulsoryKlop == 1) {
                bids = bids.filter(bid => bid.value == 1 && bid.value >= 9);
            }
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

        updateRadli: function () {
            for (var player_id in this.gamedatas.players) {
                var radl_count = parseInt(this.gamedatas.players[player_id].radl, 10);
                var radl_note = _('Radl') + ':<br />' + '⚬'.repeat(radl_count);

                if (radl_count < 1) {
                    radl_note += '–';
                }
                dojo.empty('playerradl_' + player_id);
                dojo.place(this.format_block('jstpl_radl', {
                    player_radl: radl_note
                }), 'playerradl_' + player_id);
            }
        },

        updatePlayerGame: function () {
            for (var player_id in this.gamedatas.players) {
                var game_note = '';
                var game_class = '';
                if (player_id == this.gamedatas.highBidder) {
                    console.log("highbid is " + this.gamedatas.highBid);
                    switch (parseInt(this.gamedatas.highBid)) {
                        case this.bids.three:
                            game_note = "3";
                            break;
                        case this.bids.two:
                            game_note = "2";
                            break;
                        case this.bids.one:
                            game_note = "1";
                            break;
                        case this.bids.solo_three:
                            game_note = "S3";
                            break;
                        case this.bids.solo_two:
                            game_note = "S2";
                            break;
                        case this.bids.solo_one:
                            game_note = "S1";
                            break;
                        case this.bids.beggar:
                            game_note = "B";
                            break;
                        case this.bids.solo_without:
                            game_note = "S0";
                            break;
                        case this.bids.open_beggar:
                            game_note = "OB";
                            break;
                        case this.bids.colour_valat_without:
                            game_note = "CV0";
                            break;
                        case this.bids.colour_valat:
                            game_note = "CV";
                            break;
                        case this.bids.valat:
                            game_note = "V";
                            break;
                    }
                    game_class = 'declarer';
                } else if (this.getPlayerTeam(player_id) == 'declarer') {
                    game_class = 'partner';
                    game_note = 'K';
                }

                if (game_note == '') {
                    dojo.empty('playergame_' + player_id);
                } else {
                    dojo.place(this.format_block('jstpl_game', {
                        player_game: game_note,
                        game_class: game_class,
                    }), 'playergame_' + player_id);
                }
            }
        },

        emptyPlayerGame: function () {
            console.log("empty player game");
            for (var player_id in this.gamedatas.players) {
                console.log("empty playergame_" + player_id);
                dojo.empty('playergame_' + player_id);
            }
        },

        emptyBeggarHand: function () {
            console.log("empty beggar hand");
            dojo.empty('ob_hand');

        },

        getSuitValue: function (card) {
            var value = card.type_arg;
            var color = '';
            switch (card.type) {
                case "1":
                    color = '♠';
                    break;
                case "2":
                    color = '♣';
                    break;
                case "3":
                    color = '<span style="color: #D22B2B">♥</span>';
                    break;
                case "4":
                    color = '<span style="color: #D22B2B">♦</span>';
                    break;
            }
            return color + value;
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

            dojo.subscribe('newDealer', this, "notif_checkCompulsoryKlop");
            dojo.subscribe('newHand', this, "notif_newHand");
            dojo.subscribe('newDeal', this, "notif_checkCompulsoryKlop");
            dojo.subscribe('newCards', this, "notif_newCards");
            dojo.subscribe('newTalon', this, "notif_newTalon");
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
            dojo.subscribe('playerDataUpdate', this, "notif_playerDataUpdate");
            dojo.subscribe('makeAnnouncement', this, "notif_makeAnnouncement");
            dojo.subscribe('passAnnouncement', this, "notif_passAnnouncement");
            dojo.subscribe('openBeggar', this, "notif_openBeggar");
        },

        notif_checkCompulsoryKlop: function (notif) {
            this.gamedatas.compulsoryKlop = notif.args.compulsoryKlop;
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

        notif_newTalon: function (notif) {
            console.log( "on newTalon ", notif.args.talon);
            this.gamedatas.cardsintalon = notif.args.talon;
        },

        notif_setPriorityOrder: function (notif) {
            console.log( "on setPriorityOrder ");
            this.gamedatas.forehand = notif.args.forehand;

            this.priorityOrder = [
                this.gamedatas.forehand,
                notif.args.secondPriority,
                notif.args.thirdPriority,
                notif.args.fourthPriority
            ];

            this.gamedatas.highBidder = 0;

            console.log("received forehand ", this.gamedatas.forehand);
        },

        notif_updateBids: function (notif) {
            console.log("on updateBids ", notif.args.highBid + " " + notif.args.highBidder);
            this.gamedatas.highBidder = notif.args.highBidder;
            this.gamedatas.highBid = notif.args.highBid;
            this.updatePlayerGame();
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
            this.updatePlayerGame();
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

        notif_playerDataUpdate: function (notif) {
            console.log("notif_playerDataUpdate", notif.args);
            console.log("before: ", this.gamedatas.players);
            for (var player in notif.args.players) {
                this.gamedatas.players[player].score = notif.args.players[player].score;
                this.gamedatas.players[player].radl = notif.args.players[player].radl;
                this.gamedatas.players[player].team = notif.args.players[player].team;
            }
            console.log("after: ", this.gamedatas.players);
            this.updateRadli();
        },

        notif_makeAnnouncement: function (notif) {
            console.log("makeAnnouncement received");
            console.log(notif.args);

            switch (notif.args.announcement) {
                case "1":
                    this.gamedatas.gameValue = notif.args.newValue;
                    break;
                case "2":
                    this.gamedatas.trulaPlayer = notif.args.player_id;
                    this.gamedatas.trulaValue = notif.args.newValue;
                    break;
                case "3":
                    this.gamedatas.kingsPlayer = notif.args.player_id;
                    this.gamedatas.kingsValue = notif.args.newValue;
                    break;
                case "4":
                    this.gamedatas.kingUltimoPlayer = notif.args.player_id;
                    this.gamedatas.kingUltimoValue = notif.args.newValue;
                    break;
                case "5":
                    this.gamedatas.pagatUltimoPlayer = notif.args.player_id;
                    this.gamedatas.pagatUltimoValue = notif.args.newValue;
                    break;
                case "6":
                    this.gamedatas.valatPlayer = notif.args.player_id;
                    this.gamedatas.valatValue = notif.args.newValue;
                    break;
            }

            this.gamedatas.playerAnnouncements = notif.args.playerAnnouncements;
        },

        notif_passAnnouncement: function (notif) {
            console.log("passAnnouncement received");
            this.gamedatas.playerAnnouncements = 0;
        },

        notif_openBeggar: function (notif) {
            console.log("openBeggar received");
            var beggar_hand = '<div id="ob_hand">' + _("Beggar hand") + ': ';
            var beggar_cards = [];
            for ( var i in notif.args.beggar_cards) {
                var card = notif.args.beggar_cards[i];
                beggar_cards.push(this.getSuitValue(card));
            }
            beggar_cards.sort();
            beggar_hand += beggar_cards.join(', ') + '</div>';
            console.log(beggar_hand);
            dojo.place(beggar_hand, "ob_hand", "replace");
        }
   });
});
