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
   1 => array( 'name' => clienttranslate('heart'),
               'nametr' => self::_('heart') ),
   2 => array( 'name' => clienttranslate('diamond'),
               'nametr' => self::_('diamond') ),
   3 => array( 'name' => clienttranslate('club'),
               'nametr' => self::_('club') ),
   4 => array( 'name' => clienttranslate('spade'),
               'nametr' => self::_('spade') )
 );

 $this->values_label = array(
   1 => clienttranslate('A'),
   2 => '2',
   3 => '3',
   4 => '4',
   5 => '5',
   6 => '6',
   7 => '7',
   8 => clienttranslate('J'),
   9 => clienttranslate('Q'),
   10 => clienttranslate('K')
 );
 