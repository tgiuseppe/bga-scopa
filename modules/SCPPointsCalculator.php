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
  * SCPPointsCalculator.php
  *
  * This is the utility file for calculating each points category.
  *
  */
class SCPPointsCalculator {
    private const COIN_TYPE = 2;
    private const SEVENCOIN_TYPE_ARG = 7;
    private const PRIME_STANDARD_LABEL = "prime_standard";

    private $cards = array();

    function __construct(array $inCards) {
        $this->cards = $inCards;
    }

    function cardsTaken() {
        return count($this->cards);
    }

    function coinsTaken() {
        $sum = 0;
        foreach ($this->cards as $card) {
            $sum += $card['type'] == self::COIN_TYPE ? 1 : 0;
        }
        return $sum;
    }

    function sevencoinTaken() {
        foreach ($this->cards as $card) {
            if ($card['type'] == self::COIN_TYPE && $card['type_arg'] == self::SEVENCOIN_TYPE_ARG) {
                return 1;
            }
        }

        return 0;
    }

    function primeTaken(string $primeType, array $primeValues = array()) {
        if ($primeType == self::PRIME_STANDARD_LABEL) {
            $prime = array(1=>0, 2=>0, 3=>0, 4=>0);

            foreach ($this->cards as $card) {
                $suit = $card['type'];
                $value = $card['type_arg'];
                $primeValue = $primeValues[$value];
    
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
    }

    function scopasTaken() {
        $sum = 0;
        foreach ($this->cards as $card) {
            if ($card['scopa'] == 1) {
                $sum++;
            }
        }

        return $sum;
    }
}