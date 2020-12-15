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
 * material.inc.php
 *
 * ScopaGM game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

/*
* By convention: Cups = Hearts, Golds = Diamonds, Clubs = Clovers/Clubs, Swords = Spades
* Also, depending on which type of deck is used, values can differ. In the neapolitan type-deck:
* Knave ("Woman" is also widespread) = J = 8
* Knight = Q = 9
* King = K = 10
*/

 $this->suits = array(
   1 => array( 'name' => clienttranslate('cups'),
               'nametr' => self::_('cups') ),
   2 => array( 'name' => clienttranslate('coins'),
               'nametr' => self::_('coins') ),
   3 => array( 'name' => clienttranslate('clubs'),
               'nametr' => self::_('clubs') ),
   4 => array( 'name' => clienttranslate('swords'),
               'nametr' => self::_('swords') )
 );

 $this->values_label = array(
   1 => clienttranslate('Ace'),
   2 => '2',
   3 => '3',
   4 => '4',
   5 => '5',
   6 => '6',
   7 => '7',
   8 => clienttranslate('Knave'),
   9 => clienttranslate('Knight'),
   10 => clienttranslate('King')
 );
 
 $this->prime_standard = array(
   1 => 16,
   2 => 12,
   3 => 13,
   4 => 14,
   5 => 15,
   6 => 18,
   7 => 21,
   8 => 10,
   9 => 10,
   10 => 10
 );