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
 * gameoptions.inc.php
 *
 * ScopaGM game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in scopagm.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('Game length'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name'      => totranslate('Short game - 11 points to win'), 
                                        'tmdisplay' => totranslate('Short game - 11 points to win')  ),

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name'      => totranslate('Medium game - 21 points to win'), 
                                        'tmdisplay' => totranslate('Medium game - 21 points to win') ),

                            // Another value, with other options:
                            //  description => this text will be displayed underneath the option when this value is selected to explain what it does
                            //  beta=true => this option is in beta version right now (there will be a warning)
                            //  alpha=true => this option is in alpha version right now (there will be a warning, and starting the game will be allowed only in training mode except for the developer)
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name'      => totranslate('Long game - 31 points to win'), 
                                        'tmdisplay' => totranslate('Long game - 31 points to win') ),
                            // 'description' => totranslate('this option does X'), 
                            // 'beta' => true, 'nobeginner' => true )

                            4 => array( 'name'      => totranslate('Single round'), 
                                        'tmdisplay' => totranslate('Single round') )
                        ),
                'default' => 2
            ),

    101 => array(
                    'name' => totranslate('Ace takes all'),    
                    'values' => array(  0 => array( 'name'      => totranslate('Off') ),
                                        1 => array( 'name'      => totranslate('On - NOT YET IMPLEMENTED'),
                                                    'tmdisplay' => totranslate('On - NOT YET IMPLEMENTED'),
                                                    'description' => totranslate('Playing an Ace gets all the cards in play'),
                                                    'nobeginner'  => true) ),
                    'default' => 0 
            ),

    102 => array(
                    'name' => totranslate('Napola'),    
                    'values' => array(  0 => array( 'name'      => totranslate('Off') ),
                                        1 => array( 'name'      => totranslate('On'),
                                                    'tmdisplay' => totranslate('On'),
                                                    'description' => totranslate('Get extra points for collecting Coins in a sequence, starting from Ace, two and three of Coins'),
                                                    'nobeginner'  => true) ),
                    'default' => 0                          
            )
    
);


