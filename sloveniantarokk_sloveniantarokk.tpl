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
    <!-- END player -->

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
'<div class="cardontable" id="cardontable_${card_id}" style="background-position:-${x}px -${y}px"></div>';

var jstpl_talonexchange_33 =
'<div id="talon_exchange_33" class="talon_exchange_wrapper"><div id="talon_33_1" class="talon_exchange_wrapper cardset"></div><div id="talon_33_2" class="talon_exchange_wrapper cardset"></div></div>';

</script>

{OVERALL_GAME_FOOTER}
