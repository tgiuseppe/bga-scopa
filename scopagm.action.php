<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ScopaGM implementation : © Giuseppe Madonia <tgiuseppe94@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * scopagm.action.php
 *
 * ScopaGM main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/scopagm/scopagm/myAction.html", ...)
 *
 */
  
  
  class action_scopagm extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "scopagm_scopagm";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
    // TODO: defines your action entry points there
    
    public function playCard() {
      self::setAjaxMode();
      
      $card_id = self::getArg("id", AT_posint, true);
      $chosen_cards_ids_raw = self::getArg("chosen_ids", AT_numberlist, true);

      if ($chosen_cards_ids_raw == "") {
        $chosen_cards_ids = array();
      } else {
        $chosen_cards_ids = explode(";", $chosen_cards_ids_raw);
      }

      $this->game->playCard($card_id, $chosen_cards_ids);
      self::ajaxResponse();
    }
    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

  }
  

