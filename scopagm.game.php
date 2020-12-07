<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * ScopaGM implementation : © Giuseppe Madonia <tgiuseppe94@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * scopagm.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class ScopaGM extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
            //    Variables
            "dealer" => 10
            
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("cards");
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "scopagm";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( 'dealer', 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here
       
       // Create Cards
        $cards = array();
        foreach ($this->suits as $suit_id => $suit) {
           for ($value = 1; $value <= 10; $value++) {
               $cards[] = array('type' => $suit_id, 'type_arg' => $value, 'nbr' => 1);
           }
        }

       $this->cards->createCards($cards, 'deck');

       // Begin round
       $this->putCardsOnBoard();

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        // Cards in player hand
        $result['hand'] = $this->cards->getCardsInLocation('hand', $current_player_id);

        // Cards on board
        $result['cardsonboard'] = $this->cards->getCardsInLocation('cardsonboard');

        // Cards that are scopa
        $sql = "SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_scopa scopa 
        FROM cards WHERE card_location = 'taken'";
        $result['taken'] = self::getCollectionFromDb( $sql );

  
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
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function giveCardsToPlayers() {
        $players = self::loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $cards = $this->cards->pickCards(3, 'deck', $player_id);
        }
    }

    function putCardsOnBoard() {
        do {
            $this->cards->moveAllCardsInLocation(null, "deck");
            $this->cards->shuffle('deck');
            $cards = $this->cards->pickCardsForLocation(4, 'deck', 'cardsonboard');
        } while ($this->isIllegalSetup($cards));
    }

    function isIllegalSetup($cards) {
        $nbrKings = 0;
        foreach ($cards as $card) {
            if ($card['type_arg'] == 10) {
                $nbrKings++;
            }
        }

        return $nbrKings > 2;
    }

    function isScopa($takenCards, $cardsOnBoard) {
        // Put this method after checking capture rules

        // Can't be scopa if cards are not taken
        if (count($takenCards) == 0) {
            return false;
        }

        // TODO If the capture is made with the last card in the round, it can't be scopa

        // After checking the rules, it's scopa if the player is emptying the board
        if (count($takenCards) == count($cardsOnBoard)) {
            return true;
        } else {
            return false;
        }
    }

    // Recursive
    function getPossibleCombinations($cardValueList) {
        if (count($cardValueList) == 0) {
            return array();

        } else {
            $cardValueListMutable = array_values($cardValueList);

            $elementToAdd = array();
            array_push($elementToAdd, $cardValueListMutable[0]);
            array_splice($cardValueListMutable, 0, 1);

            $combinations = $this->getPossibleCombinations($cardValueListMutable);
            unset($cardValueListMutable);

            $result = array();
            array_push($result, $elementToAdd);
            foreach ($combinations as $combination) {
                array_push($result, $combination);

                $merge = array_merge($elementToAdd, $combination);
                // We don't want useless work
                if (array_sum($merge) <= 10) {
                    array_push($result, $merge);
                }
            }

            return $result;
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Rules methods
////////////    
    /*
        Methods that check if rules are followed
    */

    function isTakingOneCard($playedCard, $takenCards, $cardsOnBoard) {
        $cardValue = $playedCard['type_arg'];

        // Player is taking a card with the same value, rule followed
        // Or is taking a card with the wrong value, rule broken
        if (count($takenCards) == 1) {
            $key = array_keys($takenCards)[0];
            if ($takenCards[$key]['type_arg'] == $cardValue) {
                return true;
            } else {
                throw new BgaUserException(self::_("You are trying to take a card with a different value than your selected card"));
            }
        }

        // Player is not taking a card with the same value, rule broken
        foreach ($cardsOnBoard as $card) {
            if ($card['type_arg'] == $cardValue) {
                throw new BgaUserException(self::_("There is at least one card on the table with the same value as your selected card"));
            }
        }

        // No cards with the same value present on board, rule followed
        return false;
    }

    function isTakingMultipleCards($playedCard, $takenCards, $cardsOnBoard) {
        $cardValue = $playedCard['type_arg'];

        // Player is taking the exact value sum of his card, rule followed
        // Or the wrong sum, rule broken
        if (count($takenCards) > 1) {
            $sum = 0;
            foreach ($takenCards as $card) {
                $sum += $card['type_arg'];
            }
            if ($sum == $cardValue) {
                return true;
            } else {
                throw new BgaUserException(self::_("You are trying to take cards which sum doesn't match with the value of your selected card"));
            }
        }

        // Player is not taking cards which sum matches with his card, rule broken
        $combinations = $this->getPossibleCombinations(array_map( function($card) { return $card['type_arg']; }, $cardsOnBoard));
        foreach ($combinations as $combination) {
            if (count($combination) > 1) {
                $sum = array_sum($combination);
                if ($sum == $cardValue) {
                    throw new BgaUserException(self::_("It's possible to take multiple cards with your selected card, you must take them"));
                }
            }
        }

        return false;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in scopagm.action.php)
    */

    function playCard($card_id, $taken_ids) {
        self::checkAction("playCard");
        $player_id = self::getActivePlayerId();

        $playedCard = null;
        $takenCards = $this->cards->getCards($taken_ids);
        $playerHand = $this->cards->getCardsInLocation("hand", $player_id);
        $cardsOnBoard = $this->cards->getCardsInLocation("cardsonboard");

        // Do you really have this card in hand?
        $bIsInHand = false;
        foreach ($playerHand as $card) {
            if ($card['id'] == $card_id) {
                $bIsInHand = true;
                $playedCard = $card;
                break;
            }
        }
        if (! $bIsInHand) {
            throw new feException(self::_("This card is not in your hand"));
        }

        // Are your taken cards really on the board?
        if (count($takenCards) > 0) {
            $bAreOnBoard = false;
            foreach ($takenCards as $card) {
                $bFoundCard = false;
                foreach ($cardsOnBoard as $cardOnBoard) {
                    if ($cardOnBoard['id'] == $card['id']) {
                        $bFoundCard = true;
                        break;
                    }
                }
                if ($bFoundCard) {
                    $bAreOnBoard = true;
                } else {
                    $bAreOnBoard = false;
                    break;
                }
            }

            if (!$bAreOnBoard) {
                throw new feException(self::_("Cards not found on the board"));
            }
        }

        // If present, must capture a card with the same value
        $bOneCardTaken = $this->isTakingOneCard($playedCard, $takenCards, $cardsOnBoard);

        // If there is a possible sum, must capture those with the played card
        $bMultipleCardsTaken = $this->isTakingMultipleCards($playedCard, $takenCards, $cardsOnBoard);

        $bIsScopa = $this->isScopa($takenCards, $cardsOnBoard);

        // TODO Change cards location and arguments in the DB

        // Notifications

        self::notifyAllPlayers('playCard', clienttranslate('${player_name} plays ${value_displayed} ${suit_displayed}'), array(
            'i18n' => array( 'suit_displayed', 'value_displayed'),
            'card_id' => $card_id,
            'taken_ids' => $taken_ids,
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'value' => $playedCard['type_arg'],
            'value_displayed' => $this->values_label[ $playedCard['type_arg'] ],
            'suit' => $playedCard['type'],
            'suit_displayed' => $this->suits[ $playedCard['type'] ]['name']
        ));

        if ($bOneCardTaken) {
            self::notifyAllPlayers('takeCards', clienttranslate('${player_name} takes a pair'), array(
                'card_id' => $card_id,
                'taken_ids' => $taken_ids,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'scopa' => $bIsScopa
            ));
        }

        if ($bMultipleCardsTaken) {
            self::notifyAllPlayers('takeCards', clienttranslate('${player_name} takes a total of ${nbr} cards'), array(
                'card_id' => $card_id,
                'taken_ids' => $taken_ids,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'nbr' => count($taken_ids) + 1,
                'scopa' => $bIsScopa
            ));
        }

        if ($bIsScopa) {
            self::notifyAllPlayers('isScopa', clienttranslate('${player_name} makes a Scopa!'), array(
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
            ));
        }

        $this->gamestate->nextState('playCard');

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

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    // TODO Check this state for global values
    function stNewRound() {
        $this->putCardsOnBoard();
        $this->gamestate->nextState("");
    }

    Function stNewHand() {
        $this->giveCardsToPlayers();
        $this->gamestate->nextState("");
    }
    function stNextPlayer() {
        // TODO see if player get the cards

        // Standard case
        $player_id = self::activeNextPlayer();
        self::giveExtraTime($player_id);
        if ($this->cards->countCardInLocation("hand") > 0) {
            $this->gamestate->nextState("nextPlayer");
        } else {
            if ($this->cards->countCardInLocation("deck") > 0) {
                $this->gamestate->nextState("newHand");
            } else {
                // ! Not yet implemented
                // TODO
                $this->gamestate->nextState("endRound");
            }
        }
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

    function zombieTurn( $state, $active_player )
    {
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
    
    function upgradeTableDb( $from_version )
    {
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
