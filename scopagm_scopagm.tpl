{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- ScopaGM implementation : © Giuseppe Madonia <tgiuseppe94@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    scopagm_scopagm.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div class="generalcontainer">
    <div class="game">
        <div id="board" class="board">
            <div id="boardcards" class="boardcards">
            </div>
            <div id="mydeck" class="mydeck">
                <div id="nbrdeck" class="nbrdeck">
                </div>
            </div>
        </div>

        <div id="myhand_wrap" class="whiteblock">
            <h3>{MY_HAND}</h3>
            <div id="myhand">
            </div>
        </div>
    </div>

    <div id="scopatable">
        <!-- BEGIN scopatable -->
        <div class="scopatable whiteblock">
            <div class="scopatablename" style="color:#{TEAM_COLOR}">
                {TEAM_NAME}
            </div>
            <div class="scopatablecards" id="scopatablecards_{TEAM_NBR}">
            </div>
        </div>
        <!-- END scopatable -->
    </div>
</div>

<script type="text/javascript">

// Javascript HTML templates

var jstpl_player_board = '<div class="cp_board"><div id="dealericon_p${id}" class="dealericon"></div></div>';

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

</script>  

{OVERALL_GAME_FOOTER}
