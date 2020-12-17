/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ScopaGM implementation : © Giuseppe Madonia <tgiuseppe94@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * scopagm.js
 *
 * ScopaGM user interface script
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
    return declare("bgagame.scopagm", ebg.core.gamegui, {
        constructor: function(){
            console.log('scopagm constructor');
            this.deckType = 'it';
            this.cardwidth = this.getCardWidth(this.deckType);
            this.cardheight = this.getCardHeight(this.deckType);
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
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed
                let player_board_div = $('player_board_'+ player_id );
                dojo.place( this.format_block('jstpl_player_board', player), player_board_div );
            }

            this.updateDealer(gamedatas.dealer);
            this.addTooltipHtmlToClass('dealericon', _('Dealer of this round'), '');
            
            // TODO: Set up your game interface here, according to "gamedatas"
            
            // Player Hand
            this.playerHand = new ebg.stock();
            this.playerHand.create(this, $('myhand'), this.cardwidth, this.cardheight);
            this.playerHand.image_items_per_row = 10;
            this.playerHand.setSelectionMode(1);

            // Board
            this.board = new ebg.stock();
            this.board.create(this, $('boardcards'), this.cardwidth, this.cardheight);
            this.board.image_items_per_row = 10;
            this.board.centerItems = true;

            // Scopa tables
            this.scopatables = {};
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                let team = player.team;
                console.log(team);
                if (this.scopatables[team] == null) {
                    console.log("A");
                    this.scopatables[team] = new ebg.stock();
                    console.log("B");
                    this.scopatables[team].create(this, $('scopatablecards_' + team), this.cardwidth, this.cardheight);
                    console.log("C");
                    this.scopatables[team].image_items_per_row = 10;
                    console.log("D");
                    this.scopatables[team].setSelectionMode(0);
                    console.log("E");
                    this.scopatables[team].setOverlap(40,0);
                }
            }

            // Create cards types
            for (let suit = 1; suit <= 4; suit++) {
                for (let value = 1; value <= 10; value++) {
                    let card_type = this.getCardType(suit, value);
                    let card_weight = this.getCardWeight(suit, value);
                    this.playerHand.addItemType(card_type, card_weight, this.getDeckImagePath(this.deckType), card_type);
                    this.board.addItemType(card_type, card_weight, this.getDeckImagePath(this.deckType), card_type);
                    for ( var team in this.scopatables) {
                        let scopatable = this.scopatables[team];
                        scopatable.addItemType(card_type, card_weight, this.getDeckImagePath(this.deckType), card_type);
                    }
                }
            }

            // Cards in player's hand
            for (let i in this.gamedatas.hand) {
                let card = this.gamedatas.hand[i];
                let suit = card.type;
                let value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardType(suit, value), card.id);
            }

            // Cards on board
            for (let i in this.gamedatas.cardsonboard) {
                let card = this.gamedatas.cardsonboard[i];
                let suit = card.type;
                let value = card.type_arg;
                this.board.addToStockWithId(this.getCardType(suit, value), card.id);
            }

            // Captured cards that are scopa
            for (let i in gamedatas.taken) {
                let card = gamedatas.taken[i];
                if (card.scopa == 1) {
                    let suit = card.type;
                    let value = card.type_arg;
                    let team = card.location_arg;
                    this.scopatables[team].addToStockWithId(this.getCardType(suit, value), card.id);
                }
            }

            // Stock events connection
            dojo.connect(this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged');
            dojo.connect(this.board, 'onChangeSelection', this, 'onBoardSelectionChanged');

            // Number of card remaining in the deck
            this.updateRemainingCardsInDeck(gamedatas.nbrdeck);
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'newRound':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods

        // Cards image info
        
        getDeckImagePath: function(deckType) {
            switch (deckType) {
                case 'fr':
                    return g_gamethemeurl + 'img/cards_fr.jpg';
                case 'it':
                    return g_gamethemeurl + 'img/cards_it.jpg';
            }
        },

        getCardWidth: function(deckType) {
            switch (deckType) {
                case 'fr':
                    return 72;
                case 'it':
                    return 72;
            }
        },

        getCardHeight: function(deckType) {
            switch (deckType) {
                case 'fr':
                    return 96;
                case 'it':
                    return 123;
            }
        },

        getCardType: function(suit, value) {
            return (suit - 1) * 10 + (value - 1);
        },

        getCardWeight: function(suit, value) {
            return (value - 1) * 4 + (suit - 1);
        },

        // Player board

        updateDealer: function(dealer_id) {
            let dealers_div = document.getElementsByClassName('dealericon');
            for (let i = 0; i < dealers_div.length; i++) {
                let dealer_div = dealers_div[i];
                if (dealer_div.id === 'dealericon_p' + dealer_id) {
                    dealer_div.style.visibility = 'visible';
                } else {
                    dealer_div.style.visibility = 'hidden';
                }
            }
        },

        // Board

        updateRemainingCardsInDeck: function(newValue) {
            document.getElementById("nbrdeck").innerHTML = newValue;
        },

        playCardOnBoard: function(player_id, suit, value, card_id, taken_ids) {
            // Put card on board
            if (player_id != this.player_id) {
                // Opponent played the card
                let from = 'overall_player_board_' + player_id;
                this.board.addToStockWithId(this.getCardType(suit, value), card_id, from);
            } else {
                // I played the card
                let from = 'myhand_item_' + card_id;
                if ($(from)) {
                    this.board.addToStockWithId(this.getCardType(suit, value), card_id, from);
                    this.playerHand.removeFromStockById(card_id);
                }
            }

            this.board.addToStockWithId(this.getCardType(suit, value), card_id);

            // Highlight taken cards

            if (taken_ids.length > 0) {
                this.board.unselectAll();
                let cardsOnBoard = this.board.getAllItems();
                for (let i = 0; i < cardsOnBoard.length; i++) {
                    let id = cardsOnBoard[i].id;
                    let div_id = this.board.getItemDivId(id);
                    document.getElementById(div_id).style.opacity = 0.3;
                }
                console.log(taken_ids);
                for (let i = 0; i < taken_ids.length; i++) {
                    let id = taken_ids[i];
                    let div_id = this.board.getItemDivId(id);
                    document.getElementById(div_id).style.opacity = 1;
                }
            }
        },

        takeCardsFromBoard: function(player_id, player_team, suit, value, card_id, taken_ids, scopa) {
            let to = 'overall_player_board_' + player_id;
            if (scopa) {
                let from = 'boardcards_item_' + card_id;
                this.scopatables[player_team].addToStockWithId(this.getCardType(suit, value), card_id, from);
                this.board.removeFromStockById(card_id, undefined, true);
            } else {
                this.board.removeFromStockById(card_id, to, true);
            }

            for (let i = 0; i < taken_ids.length; i++) {
                let id = taken_ids[i];
                this.board.removeFromStockById(id, to, true);
            }

            let cardsOnBoard = this.board.getAllItems();
            for (let i = 0; i < cardsOnBoard.length; i++) {
                let id = cardsOnBoard[i].id;
                let div_id = this.board.getItemDivId(id);
                document.getElementById(div_id).style.opacity = 1;
            }

            this.board.updateDisplay();
        },

        updateScore: function(players) {
            console.log(players);

            for (let player_id in players) {
                this.scoreCtrl[player_id].setValue( players[player_id].player_score );
            }

            
        },

        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

        onPlayerHandSelectionChanged: function() {
            let player_cards = this.playerHand.getSelectedItems();
            let taken_cards = this.board.getSelectedItems();

            if (player_cards.length > 0) {
                let action = 'playCard';
                if (this.checkAction(action, true)) {
                    let card_id = player_cards[0].id;
                    let taken_card_ids = taken_cards.map(card => card.id).join(";");
                    
                    this.ajaxcall(
                        "/" + this.game_name + "/" +this.game_name + "/" + action + ".html",
                        { id: card_id, taken_ids: taken_card_ids , lock: true },
                        this,
                        function(result) {},
                        function(is_error) {}
                    );

                    this.playerHand.unselectAll();
                } else {
                    this.playerHand.unselectAll();
                }
            }
        },

        onBoardSelectionChanged: function() {
            console.log("Board clicked");
        },
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/scopagm/scopagm/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your scopagm.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods

            dojo.subscribe('playCard', this, 'notif_playCard');
            dojo.subscribe('playCardTake', this, 'notif_playCard');
            this.notifqueue.setSynchronous('playCardTake', 3000);
            dojo.subscribe('takeCards', this, 'notif_takeCards');
            dojo.subscribe('lastPlay', this, 'notif_lastPlay');
            this.notifqueue.setSynchronous('lastPlay', 1000);

            dojo.subscribe('updateScore', this, 'notif_updateScore');
            this.notifqueue.setSynchronous('updateScore', 5000);

            dojo.subscribe('newRound', this, 'notif_newRound');

            dojo.subscribe('newHand', this, 'notif_newHand');
            dojo.subscribe('newHandPlayer', this, 'notif_newHandPlayer');
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_playCard: function(notif) {
            console.log("notif_playCard");

            this.playCardOnBoard(notif.args.player_id, notif.args.suit, notif.args.value, notif.args.card_id, notif.args.taken_ids);
        },

        notif_takeCards: function(notif) {
            console.log("notif_takeCards");

            this.takeCardsFromBoard(notif.args.player_id, notif.args.player_team, notif.args.suit, notif.args.value, notif.args.card_id, notif.args.taken_ids, notif.args.scopa);
        },

        notif_lastPlay: function(notif) {
            console.log("notif_lastPlay");

            console.log(notif.args.player_id);
            let to = 'overall_player_board_' + notif.args.player_id;
            console.log(to);
            let cards = notif.args.cards;
            console.log(cards);
            for (id in cards) {
                this.board.removeFromStockById(id, to, true);
            }
        },

        notif_updateScore: function(notif) {
            console.log("notif_updateScore");

            this.updateScore(notif.args.players);
        },

        notif_newRound: function(notif) {
            console.log("notif_newRound");

            let cards = notif.args.cards;
            for (let i = 0; i < cards.length; i++) {
                let card = cards[i];
                let from = "mydeck";
                this.board.addToStockWithId(this.getCardType(card.type, card.type_arg), card.id, from);
            }

            for (let team in this.scopatables) {
                this.scopatables[team].removeAll();
            }
            
            this.board.updateDisplay();

            this.updateDealer(notif.args.dealer);
        },

        notif_newHand: function(notif) {
            console.log("notif_newHand");

            this.updateRemainingCardsInDeck(notif.args.nbrdeck);
        },

        notif_newHandPlayer: function(notif) {
            console.log("notif_newHandPlayer");

            let player_id = notif.args.player_id;
            let cards = notif.args.cards;
            if(player_id === this.player_id) {
                for (let i = 0; i < cards.length; i++) {
                    let card = cards[i];
                    let from = "mydeck";
                    this.playerHand.addToStockWithId(this.getCardType(card.type, card.type_arg), card.id, from);
                }
            }
        }
   });             
});
