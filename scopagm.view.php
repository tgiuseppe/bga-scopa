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
 * scopagm.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in scopagm_scopagm.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_scopagm_scopagm extends game_view
  {
    function getGameName() {
        return "scopagm";
    }    

    function getPlayerTeamById($player_id) {
      $sql = "SELECT player_team team FROM player WHERE player_id = '$player_id'";
      return self::getUniqueValueFromDB( $sql );
    }

  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/

        $template = self::getGameName()."_".self::getGameName();

        $this->page->begin_block($template, "scopatable");
        $teams = array();
        foreach($players as $player_id => $player) {
          $nbr = $this->getPlayerTeamById($player_id);
          $teams[$nbr]['names'][] = $player['player_name'];
          $teams[$nbr]['color'] = $players[$player_id]['player_color'];
        }
          
        $name = array();

        foreach ($teams as $nbr => $team) {
          if (count($players) == 4) {
            $name[$nbr] = $team['names'][0]." - ".$team['names'][1];
          } else if (count($players) == 6) {
            $name[$nbr] = $team['names'][0]." - ".$team['names'][1]." - ".$team['names'][2];
          } else {
            $name[$nbr] = $team['names'][0];
          }
          $this->page->insert_block("scopatable", array(
            "TEAM_NAME" => $name[$nbr],
            "TEAM_COLOR" => $teams[$nbr]['color'],
            "TEAM_NBR" => $nbr
          ));
        }
        
        /*********** Translations ***********/

        $this->tpl['MY_HAND'] = self::_("My hand");

        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "scopagm_scopagm", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */



        /*********** Do not change anything below this line  ************/
  	}
  }
  

