{OVERALL_GAME_HEADER}

<div id="playertables">

    <!-- BEGIN player -->
    <div class="playertable whiteblock playertable_{DIR}">
        <div class="playertablename" style="color:#{PLAYER_COLOR}">
            {PLAYER_NAME}
        </div>
        <div class="playertablecard" id="playertablecard_{PLAYER_ID}">
        </div>
    </div>

    <div class="playerinfo whiteblock playerinfo_{DIR}">
        <div id="playergame_{PLAYER_ID}"></div>
        <div id="playerradl_{PLAYER_ID}"></div>
    </div>
    <!-- END player -->

    <div id="vitamin" class="playertable">
    </div>

</div>

<div id="myhand_wrap" class="whiteblock">
    <h3>{MY_HAND}</h3>
    <div id="myhand">
    </div>
</div>

<div id="talonexchange" class="whiteblock">
</div>

<script type="text/javascript">
var jstpl_cardontable =
'<div class="cardontable" id="cardontable_${player_id}" style="background-position:-${x}px -${y}px"></div>';

var jstpl_cardintalon =
'<div class="cardontable stockitem" id="cardontable_${card_id}" style="background-position:-${x}px -${y}px"></div>';

var jstpl_talonexchange_33 =
'<div id="talon_exchange_33" class="talon_exchange_wrapper"><div id="talon_33_1" class="talon_exchange_wrapper cardset"></div><div id="talon_33_2" class="talon_exchange_wrapper cardset"></div></div>';

var jstpl_talonexchange_222 =
'<div id="talon_exchange_222" class="talon_exchange_wrapper"><div id="talon_222_1" class="talon_exchange_wrapper cardset"></div><div id="talon_222_2" class="talon_exchange_wrapper cardset"></div><div id="talon_222_3" class="talon_exchange_wrapper cardset"></div></div>';

var jstpl_talonexchange_111111 =
'<div id="talon_exchange_111111" class="talon_exchange_wrapper"><div id="talon_111111_1" class="talon_exchange_wrapper cardset"></div><div id="talon_111111_2" class="talon_exchange_wrapper cardset"></div><div id="talon_111111_3" class="talon_exchange_wrapper cardset"></div><div id="talon_111111_4" class="talon_exchange_wrapper cardset"></div><div id="talon_111111_5" class="talon_exchange_wrapper cardset"></div><div id="talon_111111_6" class="talon_exchange_wrapper cardset"></div></div>';

var jstpl_radl =
'<div class="radl">${player_radl}</div>';

var jstpl_game =
'<div class="game ${game_class}">${player_game}</div>';

</script>

{OVERALL_GAME_FOOTER}
