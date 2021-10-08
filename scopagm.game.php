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
            "dealer" => 10,
            "last_player_to_take" => 11,
            "match_points" => 12,
            "round_number" => 13,
            
         
            "game_length" => 100,
            "ace_takes_all" => 101,
            "napola" => 102
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

        $teams = null;
        switch (count($players)) {
            case 2:
                $teams = array(0, 1);
            break;
            case 3:
                $teams = array(0, 1, 2);
            break;
            case 4:
                $teams = array(0, 1, 0, 1);
            break;
            case 6:
                $teams = array(0, 1, 0, 1, 0, 1);
        }
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_team) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $team = array_shift( $teams );
            $color = $default_colors[$team];

            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."','$team')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        if (count($players) < 4) {
            self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        }
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( 'dealer', 0 ); // Here just to group all global values initialization
        self::setGameStateInitialValue( 'last_player_to_take', 0); // It's impossible to end a round with no takes, so there's no need to initialize it to an id
        
        self::setGameStateInitialValue( 'round_number', 0); 

        // Set match points according to game length
        $gmlen = self::getGameStateValue('game_length');

        switch ($gmlen) {

            case 1: 
                self::setGameStateInitialValue('match_points', 11);
                break;
            case 3: 
                self::setGameStateInitialValue('match_points', 31);
                break;
            case 4: 
                self::setGameStateInitialValue('match_points', 1);
                break;
            default:
                self::setGameStateInitialValue('match_points', 21);
        }

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

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();
        self::setGameStateValue( 'dealer', $this->getActivePlayerId() );
        
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
        $sql = "SELECT player_id id, player_score score, player_team team FROM player ";
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

        // Remaining card on the deck
        $result['nbrdeck'] = $this->getCardsRemainingInDeck();

        // Dealer
        $result['dealer'] = self::getGameStateValue('dealer');

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

        // TODO: compute and return the game progression (not always games end on points threshold)
        $sql = "SELECT max(player_score) FROM player";
        $match_points = (int) self::getGameStateValue('match_points');
        $result = self::getUniqueValueFromDB( $sql );
        $result = $result * 100 / $match_points;
        $result = $result < 100 ? $result : 100;

        return $result;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function loadCompletePlayersBasicInfos() {
        $sql = "SELECT * FROM player";
        $playersRaw = self::getCollectionFromDb( $sql );
        foreach ($playersRaw as $playerRaw) {
            $player_id = $playerRaw['player_id'];
            $players[$player_id]['player_no'] = $playerRaw['player_no'];
            $players[$player_id]['player_color'] = $playerRaw['player_color'];
            $players[$player_id]['player_score'] = $playerRaw['player_score'];
            $players[$player_id]['player_name'] = $playerRaw['player_name'];
            $players[$player_id]['player_team'] = $playerRaw['player_team'];
        }

        return $players;
    }

    function getActivePlayerColor() {
        $player_id = self::getActivePlayerId();
        $players = self::loadPlayersBasicInfos();
        if( isset( $players[ $player_id ]) )
            return $players[ $player_id ]['player_color'];
        else
            return null;
    }

    function getPlayerColorById($player_id) {
        $players = self::loadPlayersBasicInfos();
        if( isset( $players[ $player_id ]) )
            return $players[ $player_id ]['player_color'];
        else
            return null;
    }

    function getPlayerTeamById($player_id) {
        $sql = "SELECT player_team team FROM player WHERE player_id = '$player_id'";
        return self::getUniqueValueFromDB( $sql );
    }

    function getCardsRemainingInDeck() {
        $sql = "SELECT count(*) remaining FROM cards WHERE card_location = 'deck' GROUP BY card_location";
        $value = self::getUniqueValueFromDB( $sql );
        return $value != null ? $value : 0;
    }

    function giveCardsToPlayers() {
        $players = self::loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $cards = $this->cards->pickCards(3, 'deck', $player_id);
            $this->notifyPlayerNewHand($player_id, $cards);
        }
    }

    function notifyPlayerNewHand($player_id, $cards) {
        self::notifyPlayer($player_id, 'newHandPlayer', '', array(
            'player_id' => $player_id,
            'cards' => $cards
        ));
    }

    function putCardsOnBoard() {
        $cards = array();
        do {
            $this->cards->moveAllCardsInLocation(null, "deck");
            $this->cards->shuffle('deck');
            $cards = $this->cards->pickCardsForLocation(4, 'deck', 'cardsonboard');
        } while ($this->isIllegalSetup($cards));

        return $cards;
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

        // Can't be scopa if it's the last played card of the round
        if ($this->cards->countCardInLocation('deck') == 0
            && $this->cards->countCardInLocation('hand') == 1) {
            return false;
        }

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

    function giveRemainingCardsOnBoard() {
        $winner_id = self::getGameStateValue('last_player_to_take');
        $team_id = $this->getPlayerTeamById($winner_id);
        $cards = $this->cards->getCardsInLocation('cardsonboard');
        $this->cards->moveAllCardsInLocation('cardsonboard', 'taken', null, $team_id);
        self::notifyAllPlayers('lastPlay', clienttranslate('This round ended. ${player_name} takes the remaining cards on the table'), array(
            'player_id' => $winner_id,
            'player_name' => self::loadPlayersBasicInfos()[$winner_id]['player_name'],
            'cards' => $cards
        ));
    }

    function updateScore() {

        // Initializing
        $players = $this->loadCompletePlayersBasicInfos();
        $teams = array();
        foreach ($players as $player_id => $player) {
            $nbr = $player['player_team'];

            if ( !isset($teams[$nbr])) {
                $teams[$nbr] = array();
                $teams[$nbr]['players'] = array();
            }

            $teams[$nbr]['players'][] = $player['player_name'];
        }

        // Calculate
        foreach ($teams as $nbr => $team) {
            $sql = "SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_scopa scopa FROM cards WHERE card_location = 'taken' AND card_location_arg = $nbr";
            $cards = self::getCollectionFromDb( $sql );
            $teams[$nbr]['cards'] = count($cards);
            $teams[$nbr]['coins'] = $this->numberCoins($cards);
            $teams[$nbr]['sevencoin'] = $this->sevencoin($cards);
            $teams[$nbr]['prime'] = $this->primePoints($cards);
            $teams[$nbr]['scopa'] = $this->scopaPoints($cards);
            $teams[$nbr]['napola'] = $this->napolaPoints($cards);

            // Winner's catogories that can be assigned immediately
            if ($teams[$nbr]['sevencoin'] == 1) {
                $winners['sevencoin'] = $nbr;
            }
        }

        // Find winner's categories
        $winners['cards'] = $this->byQuantityWinnerOf('cards', $teams);
        $winners['coins'] = $this->byQuantityWinnerOf('coins', $teams);
        $winners['prime'] = $this->byQuantityWinnerOf('prime', $teams);

        // Update score on DB
        foreach ($teams as $nbr => $team) {
            $points = 0;
            foreach($winners as $category => $winner) {
                $points += $winner == $nbr ? 1 : 0;
            }
            $points += $teams[$nbr]['scopa'];
            $points += $teams[$nbr]['napola'];
            $teams[$nbr]['total'] = $points;

            foreach($players as $player_id => $player) {
                if ($player['player_team'] == $nbr) {
                    $players[$player_id]['player_score'] += $points;
                }
            }

            $sql = "UPDATE player SET player_score = player_score + $points WHERE player_team = $nbr";
            self::DbQuery( $sql );
        }

        // Notify players

        $table = array();
        $firstRow = array('');

        if (count($players) < 4) {
            foreach($teams as $nbr => $team) {
                $firstRow[] = array( 'str' => '${player_name}',
                                      'args' => array('player_name' => $team['players'][0]),
                                      'type' => 'header'
                                    );
            }
        } else if (count($players) == 4) {
            foreach($teams as $nbr => $team) {
                $firstRow[] = array( 'str' => clienttranslate('${player_name_1} and ${player_name_2}'),
                                     'args' => array('player_name_1' => $teams[$nbr]['players'][0],
                                                     'player_name_2' => $teams[$nbr]['players'][1]
                                                 ),
                                     'type' => 'header'
                                    );
            }
        } else {
            foreach($teams as $nbr => $team) {
                $firstRow[] = array( 'str' => clienttranslate('${player_name_1}, ${player_name_2} and {player_name_3}'),
                                     'args' => array('player_name_1' => $teams[$nbr]['players'][0],
                                                     'player_name_2' => $teams[$nbr]['players'][1],
                                                     'player_name_3' => $teams[$nbr]['players'][2]
                                                    ),
                                     'type' => 'header'
                                    );
            }
        }
        array_push($table, $firstRow);

        $row['cards'] = array( clienttranslate('Cards') );
        $row['coins'] = array( clienttranslate('Coins') );
        $row['sevencoin'] = array( clienttranslate('7 of coins') );
        $row['prime'] = array( clienttranslate('Prime') );
        $row['scopa'] = array( clienttranslate('Scopa') );
        $row['napola'] = array( clienttranslate('Napola') );
        $row['total'] = array( clienttranslate('Total') );

        foreach($teams as $nbr => $team) {
            // Cards
            $pointStr = $winners['cards'] == $nbr ? 1 : 0;
            $pointStr = $pointStr.' ('.$team['cards'].')';
            array_push($row['cards'], $pointStr);

            // Coins
            $pointStr = $winners['coins'] == $nbr ? 1 : 0;
            $pointStr = $pointStr.' ('.$team['coins'].')';
            array_push($row['coins'], $pointStr);

            // Seven coin
            $pointStr = $winners['sevencoin'] == $nbr ? 1 : 0;
            array_push($row['sevencoin'], $pointStr);

            // Prime
            $pointStr = $winners['prime'] == $nbr ? 1 : 0;
            $pointStr = $pointStr.' ('.$team['prime'].')';
            array_push($row['prime'], $pointStr);

            // Scopa
            $pointStr = $team['scopa'];
            array_push($row['scopa'], $pointStr);

            // Napola
            $pointStr = $team['napola'];
            array_push($row['napola'], $pointStr);

            // Total
            $pointStr = $team['total'];
            array_push($row['total'], $pointStr);
        }
        array_push($table, $row['cards']);
        array_push($table, $row['coins']);
        array_push($table, $row['sevencoin']);
        array_push($table, $row['prime']);
        array_push($table, $row['scopa']);
        array_push($table, $row['napola']);
        array_push($table, $row['total']);

        $round_number = self::getGameStateValue('round_number');

        self::notifyAllPlayers('tableWindow', '', array(
            'id' => 'endRoundScoring',
            'title' => clienttranslate('Round ${round_number} results'),
            'table' => $table,
            'closing' => clienttranslate('Close'),
            'round_number' => $round_number
        ));

        self::notifyAllPlayers('updateScore', '', array(
            'players' => $players
        ));

        foreach($players as $player_id => $player) {
            $scores[$player_id] = $player['player_score'];
        }

        return $scores;
    }

    function numberCoins($cards) {
        $sum = 0;
        foreach ($cards as $card) {
            $card['type'] == 2 ? $sum++ : $sum;
        }
        return $sum;
    }

    function sevencoin($cards) {
        foreach ($cards as $card) {
            if ($card['type'] == 2 && $card['type_arg'] == 7) {
                return 1;
            }
        }

        return 0;
    }

    function primePoints($cards) {
        $prime = array(1=>0, 2=>0, 3=>0, 4=>0);
        
        foreach ($cards as $card) {
            $suit = $card['type'];
            $value = $card['type_arg'];
            $primeValue = $this->prime_standard[$value];

            $prime[$suit] = $prime[$suit] > $primeValue ? $prime[$suit] : $primeValue;
        }

        $sum = 0;
        foreach ($prime as $primeValue) {
            if ($primeValue == 0) {
                return 0;
            }

            $sum += $primeValue;
        }

        return $sum;
    }

    function scopaPoints($cards) {
        $sum = 0;
        foreach ($cards as $card) {
            if ($card['scopa'] == 1) {
                $sum++;
            }
        }

        return $sum;
    }

    private static function isCoins($card) {

        return ($card['type'] == 2);
    }

    private static function cardSort($card_a, $card_b) {

        $a = $card_a['type_arg'];
        $b = $card_b['type_arg'];

        if ($a==$b) return 0;

        return ($a<$b) ? -1 : 1;

    }

    function napolaPoints($cards) {

        $do_napola = (int) self::getGameStateValue('napola');

        if ( $do_napola > 0 ) {
            // keep only Coins cards
            $coins_cards = array_filter($cards, "self::isCoins");

            if ( count($coins_cards) < 3 ) {
                return 0;
            } 

            // sort by ascending order
            $coins_cards = uasort($coins_cards, "self::cardSort");

            // check run of cards in consecutive order
            $to_find = 1;

            foreach ($cards as $card) {

                if ($card["type_arg"] == $to_find) {
                    $to_find += 1;
                }
            }

            // Napola only counts if a team has got at least Ace, two, and three of Coins
            return $to_find > 3 ? ($to_find - 1) : 0;
        } 

        return 0;

    }

    function byQuantityWinnerOf($category, $teams) {
        foreach ($teams as $nbr => $team) {
            $winnerValue = isset($winner) ? $teams[$winner][$category] : null;
            $opponentValue = $teams[$nbr][$category];

            if (!isset($winner) || $opponentValue > $winnerValue) {
                $tie = false;
                $winner = $nbr;
            } else if ($winnerValue == $opponentValue) {
                $tie = true;
            }
        }

        return $tie ? -1 : $winner;
    }

    function nextDealer() {
        $actualDealer = self::getGameStateValue('dealer');
        $nextDealer = self::getPlayerBefore($actualDealer);
        self::setGameStateValue('dealer', $nextDealer);
        return $nextDealer;
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

    // Play a card from the active player's hand
    function playCard($card_id, $taken_ids) {
        $player_id = self::getActivePlayerId();
        $this->playCardFromPlayer($card_id, $taken_ids, $player_id);
    }

    function playCardFromPlayer($card_id, $taken_ids, $player_id) {
        self::checkAction("playCard");

        $playedCard = null;
        $takenCards = count($taken_ids) > 0 ? $this->cards->getCards($taken_ids) : array();
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
        $bMultipleCardsTaken = false;
        if (!$bOneCardTaken) {
            $bMultipleCardsTaken = $this->isTakingMultipleCards($playedCard, $takenCards, $cardsOnBoard);
        }

        $bIsScopa = $this->isScopa($takenCards, $cardsOnBoard);

        if ($bOneCardTaken || $bMultipleCardsTaken) {
            $sql_scopa = $bIsScopa ? 1 : 0;
            $sql_team = $this->getPlayerTeamById($player_id);
            $sql = "UPDATE `cards` SET `card_location` = 'taken', `card_location_arg` = '${sql_team}', `card_scopa` = ${sql_scopa} WHERE `card_id` = ${card_id}";
            self::DbQuery( $sql );

            $this->cards->moveCards($taken_ids, 'taken', $sql_team);

            self::setGameStateValue('last_player_to_take', $player_id);
        } else {
            $this->cards->moveCard($card_id, 'cardsonboard');
        }

        // Notifications

        $notifPlayCard = $bOneCardTaken || $bMultipleCardsTaken ? 'playCardTake' : 'playCard';
        self::notifyAllPlayers($notifPlayCard, clienttranslate('${player_name} plays ${value_displayed} of ${suit_displayed}'), array(
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
                'suit' => $playedCard['type'],
                'value' => $playedCard['type_arg'],
                'taken_ids' => $taken_ids,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'player_team' => $this->getPlayerTeamById($player_id),
                'scopa' => $bIsScopa
            ));
        }

        if ($bMultipleCardsTaken) {
            self::notifyAllPlayers('takeCards', clienttranslate('${player_name} takes a total of ${nbr} cards'), array(
                'card_id' => $card_id,
                'suit' => $playedCard['type'],
                'value' => $playedCard['type_arg'],
                'taken_ids' => $taken_ids,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'player_team' => $this->getPlayerTeamById($player_id),
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
        $dealer = $this->nextDealer();
        $this->gamestate->changeActivePlayer( self::getPlayerAfter($dealer) );

        $round_number = (int) self::getGameStateValue('round_number');
        $round_number += 1;

        self::setGameStateValue('round_number', $round_number);

        $cards = $this->putCardsOnBoard();
        $sql = "UPDATE cards SET card_scopa = 0";
        self::DbQuery( $sql );

        self::notifyAllPlayers('newRound', clienttranslate('Round ${round_number} is beginning'), array(
            'cards' => $cards,
            'dealer' => $dealer,
            'round_number' => $round_number
        ));
        $this->gamestate->nextState("");
    }

    Function stNewHand() {
        $this->giveCardsToPlayers();
        self::notifyAllPlayers('newHand', clienttranslate('All players get a new hand'), array(
            'nbrdeck' => $this->getCardsRemainingInDeck()
        ));

        $this->gamestate->nextState("");
    }
    function stNextPlayer() {
        $player_id = self::activeNextPlayer();
        self::giveExtraTime($player_id);
        if ($this->cards->countCardInLocation("hand") > 0) {
            $this->gamestate->nextState("nextPlayer");
        } else {
            if ($this->cards->countCardInLocation("deck") > 0) {
                $this->gamestate->nextState("newHand");
            } else {
                $this->giveRemainingCardsOnBoard();
                $this->gamestate->nextState("endRound");
            }
        }
    }

    function stEndRound() {
        $scores = $this->updateScore();
        $isEndGame = false;
        $match_points = (int) self::getGameStateValue('match_points');

        foreach($scores as $player_id => $score) {
            self::dump( "Player ID: ", $player_id );
            self::dump( "Score: ", $score );
            self::dump( "Match points: ", $match_points );
            if ($score > $match_points ) {
                $isEndGame = true;
                break;
            }
        }

        if ($isEndGame) {
            $this->gamestate->nextState("endGame");
        } else {
            $this->gamestate->nextState("newRound");
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
        self::trace( "Zombie turn" );
    	$statename = $state['name'];
    	
        self::dump( "Zombie state", $statename );
        self::dump( "Zombie ID", $active_player  );

        switch ($statename) {
            case 'playerTurn':

                
                // Play a random card
                $playerHand = $this->cards->getCardsInLocation("hand", $active_player);
                
                if ( count($playerHand) == 0) {
                    self::trace( "Zombie has no playable cards");
                    $this->gamestate->nextState("zombiePass");
                    return;
                }

                foreach ($playerHand as $card) {
                    self::dump( "Card in hand, id: ", $card['id']); 
                    self::dump( "Card in hand, suit: ", $card['type']); 
                    self::dump( "Card in hand, value: ", $card['type_arg']); 
                }

                $randomCard = bga_rand(0, count($playerHand) - 1);
                $keys       = array_keys($playerHand);
                $cardId    = $playerHand[$keys[$randomCard]]['id'];
                $cardSuit  = $playerHand[$keys[$randomCard]]['type'];
                $cardValue = $playerHand[$keys[$randomCard]]['type_arg'];

                $cardsOnBoard = $this->cards->getCardsInLocation("cardsonboard");

                // check if there is a single card that matches
                foreach ($cardsOnBoard as $cardOnBoard) {
                    if ($cardOnBoard['type_arg'] == $cardValue ) {

                        self::dump( "Played card, id: ", $cardId ); 
                        self::dump( "Played card, suit: ", $cardSuit); 
                        self::dump( "Played card, value: ", $cardValue); 

                        $this->playCardFromPlayer($cardId, $cardOnBoard['id'], $active_player);
                        return;
                    }
                }

                // get possible combinations
                $combinations = $this->getPossibleCombinations($cardsOnBoard);
                foreach ($combinations as $combination) {
                    if (count($combination) > 1) {
                        $sum = array_sum(array_map(function($c) {return $c["type_arg"];}, $combination));
                        if ($sum == $cardValue) {

                            self::dump( "Played card, id: ", $cardId ); 
                            self::dump( "Played card, suit: ", $cardSuit); 
                            self::dump( "Played card, value: ", $cardValue); 

                            $taken_ids = array_map(function($c) {return $c["id"];}, $combination);
                            $this->playCardFromPlayer($cardId, $taken_ids, $active_player); 
                            return;
                        }
                    }
                }

                // just discard, and take no cards
                $this->playCardFromPlayer($cardId, array(), $active_player);
                return;


                break;
            default:
                // To be implemented?
                // $this->gamestate->nextState( "zombiePass" );
            //    $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
                return;
                break;
        }


        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }

    // METHOD TESTS
    
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
