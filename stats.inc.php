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
 * stats.inc.php
 *
 * ScopaGM game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "rounds_number" => array("id"=> 10,
                    "name" => totranslate("Number of rounds"),
                    "type" => "int" ),

/*
        Examples:


        "table_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("table test stat 1"), 
                                "type" => "int" ),
                                
        "table_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("table test stat 2"), 
                                "type" => "float" )
*/  
    ),
    
    // Statistics existing for each player
    "player" => array(

        "cards_taken" => array("id"=> 10,
                    "name" => totranslate("Number of cards taken"),
                    "type" => "int" ),
        "cards_taken_per_round" => array("id"=> 11,
                    "name" => totranslate("Number of cards taken per round"),
                    "type" => "float" ),
        "coins_taken" => array("id"=> 12,
                    "name" => totranslate("Number of coins cards taken"),
                    "type" => "int" ),
        "coins_taken_per_round" => array("id"=> 13,
                    "name" => totranslate("Number of coins cards taken per round"),
                    "type" => "float" ),
        "prime_points" => array("id"=> 14,
                    "name" => totranslate("Total of prime points gained"),
                    "type" => "int" ),
        "prime_points_per_round" => array("id"=> 15,
                    "name" => totranslate("Average of prime points gained per round"),
                    "type" => "float" ),
        "sevencoins_taken" => array("id"=> 16,
                    "name" => totranslate("Total of seven of coins taken"),
                    "type" => "int" ),
        "scopa_points" => array("id"=> 17,
                    "name" => totranslate("Total of scopa gained"),
                    "type" => "int" ),
        "scopa_points_per_round" => array("id"=> 18,
                    "name" => totranslate("Average of scopa gained per round"),
                    "type" => "float" ),
                    
    
/*
        Examples:    
        
        
        "player_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("player test stat 1"), 
                                "type" => "int" ),
                                
        "player_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("player test stat 2"), 
                                "type" => "float" )

*/    
    )

);
