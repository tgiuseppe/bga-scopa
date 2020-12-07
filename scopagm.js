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
            this.cardwidth = 72;
            this.cardheight = 96;
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

            this.deckType = 'fr';

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
            }
            
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
                let color = player.color;
                if (this.scopatables[color] == null) {
                    this.scopatables[color] = new ebg.stock();
                    this.scopatables[color].create(this, $('scopatablecards_' + color), this.cardwidth, this.cardheight);
                    this.scopatables[color].image_items_per_row = 10;
                    this.scopatables[color].setSelectionMode(0);
                    this.scopatables[color].setOverlap(40,0);
                }
            }

            // Create cards types
            for (let suit = 1; suit <= 4; suit++) {
                for (let value = 1; value <= 10; value++) {
                    let card_type = this.getCardType(suit, value);
                    this.playerHand.addItemType(card_type, card_type, this.getDeckImagePath(this.deckType), card_type);
                    this.board.addItemType(card_type, card_type, this.getDeckImagePath(this.deckType), card_type);
                    for ( var color in this.scopatables) {
                        let scopatable = this.scopatables[color];
                        scopatable.addItemType(card_type, card_type, this.getDeckImagePath(this.deckType), card_type);
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
                if (card.scopa) {
                    let suit = card.type;
                    let value = card.type_arg;
                    let color = gamedatas.players[card.location_arg].color;
                    this.scopatables[color].addToStockWithId(this.getCardType(suit, value), card.id);
                }
            }

            // Stock events connection
            dojo.connect(this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged');
            dojo.connect(this.board, 'onChangeSelection', this, 'onBoardSelectionChanged');
 
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
           
           
            case 'dummmy':
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
        
        getDeckImagePath: function(deckType) {
            switch (deckType) {
                case 'fr':
                    return g_gamethemeurl + 'img/cards_fr.jpg';
                case 'it':
                    return g_gamethemeurl + 'img/cards_fr.jpg';
            }
        },

        getCardType: function(suit, value) {
            return (suit - 1) * 10 + (value - 1);
        },

        playCardOnBoard: function(player_id, suit, value, card_id) {
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
            let chosen_cards = this.board.getSelectedItems();

            if (player_cards.length > 0) {
                let action = 'playCard';
                if (this.checkAction(action, true)) {
                    let card_id = player_cards[0].id;
                    let chosen_card_ids = chosen_cards.map(card => card.id).join(";");
                    
                    this.ajaxcall(
                        "/" + this.game_name + "/" +this.game_name + "/" + action + ".html",
                        { id: card_id, chosen_ids: chosen_card_ids , lock: true },
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
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
