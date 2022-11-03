<?php
/**
 * Plugin Name:   Parcheggio  Partner
 * Plugin URI:    https://www.nethomelive.it
 * Description:   Plugin che contiene il form di ricerca di Cerco Parcheggio
 * Version:       2.1
 * Author:        Nethome
 * Author URI:    https://www.nethomelive.it
 */


class Partner_Parcheggio {


  // Set up the widget name and description.
  public function __construct() {

  register_activation_hook(__FILE__, array($this,'plugin_activate')); //activate hook
  register_deactivation_hook(__FILE__, array($this,'plugin_deactivate')); //deactivate hook
  add_action('init', array($this,'register_parcheggio_shortcodes')); //shortcodes
  add_action( 'wp_enqueue_scripts', array($this,'add_bootstrap_scripts' ));
  add_action('wp_footer', array($this,'parcheggio_script'));
  add_action( 'plugins_loaded', array($this,'cercaparcheggio_load_textdomain' ));

  }

  //triggered on activation of the plugin (called only once)
public function plugin_activate(){

}

//trigered on deactivation of the plugin (called only once)
public function plugin_deactivate(){

}

function cercaparcheggio_load_textdomain() {
  load_plugin_textdomain( 'cercoparcheggio', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

public function register_parcheggio_shortcodes(){
    if ( ! is_admin() ) {
    add_shortcode('ricerca', array($this,'parcheggio_shortcode_output'));
    add_shortcode('parkingrequest', array($this,'net_parking_connector_request'));
    add_shortcode('prenotazione', array($this,'net_parking_connector_prenotazione'));
    add_shortcode('ricercaheader', array($this,'parcheggio_search_header'));
    }
}

//shortcode display
public function parcheggio_shortcode_output($atts, $content = '', $tag){

    //build default arguments
    $atts = shortcode_atts(array(
        'id' => '',
        'css' => 'partner',
        'column' => '4',
        'columndate' => '4',
        'columntime' => '4',
        'columntipo' => '8',
        'offset' => '2',
        'button' => '8',
        'top' => '40',
        'buttoncss' => '#fff',
        'horizontal' => '0',
        'columcitta' => '10',
         )
    ,$atts,$tag);

    //uses the main output function of the location class
    $parcheggio_conf_options = get_option( 'parcheggio_conf_option_name' );
    $select_car = $parcheggio_conf_options['select_car_option_0'];
    $select_motor = $parcheggio_conf_options['select_motor_option_0'];
    $select_van = $parcheggio_conf_options['select_van_option_0'];
    $api_key = $parcheggio_conf_options['partner_key'];
    $partner_id = $parcheggio_conf_options['partner_id'];
    $pagina_ricerca_id = $parcheggio_conf_options['pagina_ricerca_id'];
    $gestionale_url = 'https://gestionale.parcheggioincloud.it/api.php';
    $url_veicolo = $gestionale_url.'?command=GetVeicoli';
    $tipo_veicolo = json_decode(file_get_contents($url_veicolo),true);
    $url_tipo = $gestionale_url.'?command=GetType';
    $tipo_parcheggio = json_decode(file_get_contents($url_tipo),true);
    $url_citta = $gestionale_url.'?command=GetCitta';
    $citta_parcheggio = json_decode(file_get_contents($url_citta),true);
    $html = '';
    if ($_REQUEST['data_inizio_date'] && $_REQUEST['data_inizio_time']) {
    $inizio_time = urldecode($_REQUEST['data_inizio_time']);
    $inizio_date = urldecode($_REQUEST['data_inizio_date']);
    }  else {
    $timestamp = strtotime(date( 'H' ).':00') + 60*60*3;
    $inizio_time = date('H:i', $timestamp);
    $inizio_date = date('d-m-Y');
    }

    if ($_REQUEST['data_fine_date'] && $_REQUEST['data_fine_time']) {
    $fine_time = urldecode($_REQUEST['data_fine_time']);
    $fine_date = urldecode($_REQUEST['data_fine_date']);
    } else {
    $timestamp1 = strtotime(date( 'H' ).':00') + 60*60*3;
    $fine_time = date('H:i', $timestamp1);
    $fine_date = date('d-m-Y', mktime(0,0,0,date('m'),date('d')+1,date('Y')));
    }
    ob_start();
    ?>
<div class="bootstrap ricerca-<?php echo $atts['css'] ?>">
<div class="containers">
        <div class="row top<?php echo $atts['top'] ?>">

            <div class="col-md-12 col-md-offset-<?php echo $atts['offset'] ?>">
                <form role="form" id="formDestinationBooking" method="get" action="<?php echo esc_url( get_permalink($pagina_ricerca_id) ); ?>">
                    <input type="hidden" name="api_key" value="<?php echo $api_key ?>" id="api_key" />
                    <input type="hidden" name="partner_id" value="<?php echo $partner_id ?>" id="partner_id" />
                    <input type="hidden" name="ricerca_post" value="1" id="ricerca_post" />
                    <!--<legend class="text-center">Register</legend> -->

                    <fieldset>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    <div class="row">
                    <?php } ?>
                    <div class="form-group col-md-<?php echo $atts['columcitta'] ?>">
                            <label for="citta">Città</label>
                            <select id="citta" class="form-control testo_form_input tipologia_select orario_form_noborder blu refill selectpicker" name="citta" autocomplete="off" data-size="5" data-live-search="true">
                            <option value=""></option>
                            <?php foreach ($citta_parcheggio as $key => $row_citta) { ?>
                            <option value="<?php echo $row_citta['citta']; ?>" <?php if($_REQUEST['citta']==$row_citta['citta']){ print ' selected="selected"'; }?>><?php echo ucfirst($row_citta['citta']); ?> </option>
                            <?php } ?>
                            </select>
                    </div>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    </div>
                    <?php } ?>
                        <?php
                        if ($atts['horizontal'] == 1) {
                        ?>
                       <div class="row">
                        <?php } ?>
                        <div class="form-group col-md-<?php echo $atts['columndate'] ?>">
                            <label for="data-inizio-date">Arrivo in parcheggio</label>
                            <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></div>
                            <input id="data-inizio-date" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="data_inizio_date" value="<?php echo $inizio_date;?>" placeholder="Data ingresso" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group col-md-<?php echo $atts['columntime'] ?>">
                            <label for="data-inizio-time">Ora</label>
                            <select id="data-inizio-time" class="form-control testo_form_input orario_form_select blu refill selectpicker" name="data_inizio_time" autocomplete="off" data-size="5">
                            <option value="">Ingresso</option>
                            <?php echo $this->get_times_cerco($inizio_time, '+15 minutes'); ?>
                            </select>
                        </div>
                        <?php
                        if ($atts['horizontal'] == 1) {
                        ?>
                       </div>
                        <?php } ?>

                        <?php
                        if ($atts['horizontal'] == 1) {
                        ?>
                       <div class="row">
                        <?php } ?>
                        <div class="form-group col-md-<?php echo $atts['columndate'] ?>">
                            <label for="data-fine-date">Partenza dal parcheggio</label>
                            <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></div>
                            <input id="data-fine-date" class="form-control testo_form_input blu refill tooltip_onload" data-placement="bottom" type="text" name="data_fine_date" value="<?php echo  $fine_date;?>" placeholder="Data uscita" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group col-md-<?php echo $atts['columntime'] ?>">
                            <label for="confirm_password">Ora</label>
                            <select id="data-fine-time" class="form-control testo_form_input orario_form_select orario_form_noborder blu refill selectpicker" name="data_fine_time" autocomplete="off" data-size="5">
                            <option value="">Uscita</option>
                            <?php echo $this->get_times_cerco($fine_time,'+15 minutes'); ?>
                            </select>
                        </div>
                        <?php
                        if ($atts['horizontal'] == 1) {
                        ?>
                       </div>
                       <?php } ?>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    <div class="row">
                    <?php } ?>
                    <div class="form-group col-md-<?php echo $atts['columntipo'] ?>">
                            <label for="tipologia">Veicolo </label><br>
                         <!--    <select id="tipologia" class="form-control testo_form_input tipologia_select orario_form_noborder blu refill selectpicker" name="tipologia" autocomplete="off" data-size="5">
                            <?php //foreach ($tipo_veicolo as $key => $row_veicolo) { ?>
                            <option value="<?php  //echo $row_veicolo['id']; ?>" <?php //if($_REQUEST['veicolo']==$row_veicolo['id']){ //print ' selected="selected"'; }?>><?php // echo $row_veicolo['nome']; ?> </option>
                            <?php //} ?>
                            </select> -->
                        <div class="vehicle-selector">
                            <input type="radio" name="tipologia" id="tipologia1" class=" selectpicker" value="1" <?php if($_REQUEST['tipologia']== "1"){ print 'checked'; }?> > 
                            <label class="image-radio tipologia1" for="tipologia1"> 
                                <i class="<?php echo $select_car ?> fa-2xl" aria-hidden="true"></i><br> Auto 
                            </label>
                            <input type="radio" name="tipologia" id="tipologia2" class=" selectpicker" value="2" <?php if($_REQUEST['tipologia']== "2"){ print 'checked'; }?> > 
                            <label class="image-radio tipologia2" for="tipologia2">
                                <i class="<?php echo $select_motor ?> fa-2xl" aria-hidden="true"></i><br> Moto 
                            </label>   
                            <input type="radio" name="tipologia" id="tipologia3" value="3" class="selectpicker"  <?php if($_REQUEST['tipologia']== "3"){ print 'checked'; }?> >
                            <label class="image-radio tipologia3" for="tipologia3">  
                               <i class="<?php echo $select_van ?> fa-2xl" aria-hidden="true"></i><br> Camper 
                            </label>          
                        </div>
                    </div>


                    <div class="form-group col-md-<?php echo $atts['columntipo'] ?>">
                            <label for="park_type">Tipo</label>
                            <div id="tipo_parcheggio">
                                <?php if(isset($_REQUEST['tipologia']) && $_REQUEST['tipologia'] != 1){
                                    if($_REQUEST['tipologia'] == 2 || $_REQUEST['tipologia'] == 3){
                                        ?>
                                            <select id="park_type" class="form-control testo_form_input orario_form_select orario_form_noborder blu refill selectpicker" name="park_type" autocomplete="off" tabindex="-98">
                                            </select>
                                        <?php
                                    }
                                }
                                else{ ?>
                                <select id="park_type" class="form-control testo_form_input orario_form_select orario_form_noborder blu refill selectpicker" name="park_type" autocomplete="off">
                                <?php foreach ($tipo_parcheggio as $key => $row_tipi) { ?>
                                <option value="<?php echo $row_tipi['id']; ?>" <?php if ($_REQUEST['park_type']==$row_tipi['id']) { echo 'selected="selected"';} ?>><?php echo $row_tipi['nome']; ?></option>
                                <?php } ?>
                                </select>
                            <?php } ?>
                            </div>
                    </div>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    </div>
                    <?php } ?>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    <div class="row">
                    <?php } ?>
                    <div class="form-group col-md-<?php echo $atts['button'] ?>">
                    <label for="submit-search" style="color: <?php echo $atts['buttoncss'] ?>;"> &nbsp; </label>
                        <div class="bottone-cerca">
                                   <div class="col-xs-12 center_align_text botone-sl">
                                       <button class="btn btn-warning btn-block" id="submit-search"><?php _e('Cerca Parcheggio', 'cercoparcheggio'); ?></button>
                                   </div>
                                   <div class="col-xs-12 top_margin_15 hidden-lg hidden-md"></div>
                        </div>
                    </div>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    </div>
                    <?php } ?>
                   </fieldset>
                </form>
            </div>

        </div>
    </div>
</div>

    <?php

    return ob_get_clean();
    }

public function net_parking_connector_request($atts, $content = '', $tag){

    $gestionale_url = 'https://gestionale.parcheggioincloud.it/api.php';

    $parcheggio_conf_options = get_option( 'parcheggio_conf_option_name' );
    $pagina_prenotazione_id = $parcheggio_conf_options['pagina_prenotazione_id'];
    $select_car = $parcheggio_conf_options['select_car_option_0'];


    $url_veicolo = $gestionale_url.'?command=GetVeicoli';
    $tipo_veicolo = json_decode(file_get_contents($url_veicolo),true);

    if ($_REQUEST['data_inizio_date'] && $_REQUEST['data_inizio_time']) {
    $inizio_time = urldecode($_REQUEST['data_inizio_time']);
    $inizio_date = urldecode($_REQUEST['data_inizio_date']);
    } else if ($_REQUEST['data_arrivo'] && $_REQUEST['ora_arrivo']) {
    $inizio_time = urldecode($_REQUEST['ora_arrivo']);
    $inizio_date = urldecode($_REQUEST['data_arrivo']);
    }

    if ($_REQUEST['data_fine_date'] && $_REQUEST['data_fine_time']) {
    $fine_time = urldecode($_REQUEST['data_fine_time']);
    $fine_date = urldecode($_REQUEST['data_fine_date']);
    } else if ($_REQUEST['data_part'] && $_REQUEST['ora_part']) {
    $inizio_time = urldecode($_REQUEST['ora_part']);
    $inizio_date = urldecode($_REQUEST['data_part']);
    }

    //$tipologia = urldecode($_REQUEST['tipologia']);
    $tipologia = urldecode($_REQUEST['mezzo']);
    $tipologia_veicolo = urldecode($_REQUEST['tipologia']);
    $tipo = $tipologia_veicolo;
    $park_type = $_REQUEST['park_type'];
    $citta = urlencode($_REQUEST['citta']);


    /*$differenze = $this->ConteggioDifferenza(date('d-m-Y'),date('H:i'),$inizio_date,$inizio_time);

    if ($differenze['d'] == 0 && $differenze['h'] <= 12) {
    $blocco = 1;
    } else {
    $blocco = 0;
    }*/

    $blocco = 0;

    $atts = shortcode_atts(array(
        'id' => '',
        'data_inizio_time' => $inizio_time,
        'data_inizio_date' => $inizio_date,
        'data_fine_time' => $fine_time,
        'data_fine_date' => $fine_date,
        'veicolo' => $tipo,
        'park_type' => $park_type,
        'citta' => $citta,
        )
    ,$atts,$tag);

    if ($_REQUEST['ricerca_post']) {

    //$url = "http://gestionale.cercoparcheggio.it/api.php?API_KEY=9253a27b5c9268d6e5216bff55a2b66e0b514dd939b504c795f34388e81f47fe7f95f9adf479aa48013bc3790f69b3d38815&citta=".$atts['citta']."&indirizzo=".$atts['indirizzo']."&DataIn=".$atts['DataIn']."&DataUs=".$atts['DataUs']."&settore=".$atts['settore']."&poi=".$atts['poi']."&idUtente=".$atts['idUtente']."&encode=json&command=SearchRequest";
    $url_coperto = $gestionale_url."?command=BookingSearchMulti&data_inizio_date=".$atts['data_inizio_date']."&data_fine_date=".$atts['data_fine_date']."&data_inizio_time=".$atts['data_inizio_time']."&data_fine_time=".$atts['data_fine_time']."&tipo=".$atts['veicolo']."&park_type=".$atts['park_type']."&citta_parcheggio=".$atts['citta']."&sconto_prenotazione_tipo=1";
    $parck_coperto = json_decode(file_get_contents($url_coperto),true);
    //echo $url_coperto;

    //print_r($parck_coperto);

    $url_coperto_chiusure = $gestionale_url."?command=GetChiusure&data_inizio_date=".$atts['data_inizio_date']."&data_fine_date=".$atts['data_fine_date']."".$atts['veicolo']."&park_type=".$atts['park_type']."";
    $parck_coperto_chiusure = json_decode(file_get_contents($url_coperto_chiusure),true);

    //print_r($parck_scoperto_chiusure);
    if (empty($parck)) {
    foreach ($parck_coperto as $key => $value) {

    $wp_parcheggio = $value['wp_parcheggio'];
    $url_parcheggio = esc_url( get_permalink( $wp_parcheggio ) );
    $excerpt = get_the_excerpt($wp_parcheggio);
    //$excerpt = substr($excerpt, 0, 260);
    //$result = substr($excerpt, 0, strrpos($excerpt, ' '));
    $listini_parcheggio = $value['servizi_parcheggio'];
    $get_the_post_thumbnail_url = get_the_post_thumbnail_url( $wp_parcheggio, 'medium' );
    $attachment_id = mfn_get_attachment_id_url( get_the_post_thumbnail_url( $wp_parcheggio, 'full' ) );
    $attachment_src = wp_get_attachment_image_src( $attachment_id, 'full' );
    //$net_get_image_id = $this->net_get_image_id($get_the_post_thumbnail_url);
    $src = $attachment_src[0];
    $width = $attachment_src[1];
	$height = $attachment_src[2];
    $srcset = mfn_srcset( mfn_get_attachment_id_url( $src ), true );
    //echo $net_get_image_id.'aaaaaaaaaaaaaa';
    ?>

    <div class="section mcb-section mfn-default-section mcb-section-n320virv4 default-width" style="">
    <div class="mcb-background-overlay"></div>
    <div class="section_wrapper mcb-section-inner mcb-section-inner-n320virv4">
      <div class="wrap mcb-wrap mcb-wrap-768b5eetk one-third tablet-one-third mobile-one valign-top clearfix" data-desktop-col="one-third" data-tablet-col="tablet-one-third" data-mobile-col="mobile-one" style="">
        <div class="mcb-wrap-inner mcb-wrap-inner-768b5eetk">
          <div class="mcb-wrap-background-overlay"></div>
          <div class="column mcb-column mcb-item-m4uo763lt one tablet-one mobile-one column_image" style="">
            <div class="mcb-column-inner mcb-column-inner-m4uo763lt mcb-item-image-inner">
              <div class="image_frame element_classes image_item scale-with-grid aligncenter no_border hover-disable mfn-mask-shape" role="link" tabindex="0">
                <div class="image_wrapper">
                  <a href="<?php echo $url_parcheggio ?>" tabindex="-1">
                  <div class="mask"></div><img class="scale-with-grid" style="-webkit-mask-size:;-webkit-mask-position:;" src="<?php echo $get_the_post_thumbnail_url ?>" alt="<?php echo esc_html( get_the_title($wp_parcheggio) ) ?>" title="<?php echo esc_html( get_the_title($wp_parcheggio) ) ?>" <?php echo $srcset ?> width="<?php echo $width ?>" height="<?php echo $height ?>"></a>
                  <div class="image_links">
                    <a href="<?php echo $url_parcheggio ?>" class="link" tabindex="-1"><svg viewbox="0 0 26 26" aria-label="go to link">
                    <defs></defs>
                    <g>
                      <path d="M10.17,8.76l2.12-2.12a5,5,0,0,1,7.07,0h0a5,5,0,0,1,0,7.07l-2.12,2.12" class="path"></path>
                      <path d="M15.83,17.24l-2.12,2.12a5,5,0,0,1-7.07,0h0a5,5,0,0,1,0-7.07l2.12-2.12" class="path"></path>
                      <line x1="10.17" y1="15.83" x2="15.83" y2="10.17" class="path"></line>
                    </g></svg></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="column mcb-column mcb-item-om8iqtij3 one tablet-one mobile-one column_column" style="">
            <div class="mcb-column-inner mcb-column-inner-om8iqtij3 mcb-item-column-inner">
              <div class="column_attr mfn-inline-editor clearfix align_center" style="">
                <i class="icon-info-circled" style="" aria-hidden="true"></i><a href="<?php echo $url_parcheggio ?>">INFO PARCHEGGIO</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="wrap mcb-wrap mcb-wrap-1bjy858wz two-third tablet-two-third mobile-one valign-top clearfix" data-desktop-col="two-third" data-tablet-col="tablet-two-third" data-mobile-col="mobile-one" style="">
        <div class="mcb-wrap-inner mcb-wrap-inner-1bjy858wz">
          <div class="mcb-wrap-background-overlay"></div>
          <div class="column mcb-column mcb-item-vdjgrc0z0 one tablet-one mobile-one column_fancy_heading" style="">
            <div class="mcb-column-inner mcb-column-inner-vdjgrc0z0 mcb-item-fancy_heading-inner">
              <div class="fancy_heading fancy_heading_line">
                <div class="fh-top"></div>
                <h2 class="title"><?php echo esc_html( get_the_title($wp_parcheggio) ) ?></h2>
                <div class="inside">
                  <p><a href="<?php echo $url_parcheggio ?>"><i class="icon-location" style="" aria-hidden="true"></i></a> <?php echo $value['indirizzo']; ?> <?php echo $value['citta']; ?><</p>
                </div>
              </div>
            </div>
          </div>
          <div class="column mcb-column mcb-item-f711y4ipm one tablet-one mobile-one column_column" style="">
            <div class="mcb-column-inner mcb-column-inner-f711y4ipm mcb-item-column-inner">
              <div class="column_attr mfn-inline-editor clearfix" style="">
                <?php echo $excerpt ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php
      foreach ($listini_parcheggio as $key => $l) {
      ?>
      <div class="wrap mcb-wrap mcb-wrap-a1u3244mz one tablet-one mobile-one valign-top clearfix" data-desktop-col="one" data-tablet-col="tablet-one" data-mobile-col="mobile-one" style="">
        <div class="mcb-wrap-inner mcb-wrap-inner-a1u3244mz">
          <div class="mcb-wrap-background-overlay"></div>
          <div class="column mcb-column mcb-item-2nwr7rgxy one-third tablet-one-third mobile-one column_column tiplogia-item" style="">
            <div class="mcb-column-inner mcb-column-inner-2nwr7rgxy mcb-item-column-inner">
              <div class="column_attr mfn-inline-editor clearfix align_center" style="">
                <strong><?php echo $l['controllo_posti']['settore'] ?> <?php echo $l['controllo_posti']['tipo'] ?></strong>
              </div>
            </div>
          </div>
          <div class="column mcb-column mcb-item-xqvv82t9h one-third tablet-one-third mobile-one column_button" style="">
            <div class="mcb-column-inner mcb-column-inner-xqvv82t9h mcb-item-button-inner">
              <div class="button_align align_center">
                <a class="button pagainparcheggio-item has-icon button_left button_size_2" href="<?php echo esc_url( get_permalink($wp_parcheggio) ); ?>?data_inizio_date=<?php echo urlencode($inizio_date) ?>&data_fine_date=<?php echo urlencode($fine_date) ?>&data_inizio_time=<?php echo urlencode($inizio_time) ?>&data_fine_time=<?php echo urlencode($fine_time) ?>&park_type=<?php echo $l['tipo_parcheggio'] ?>&veicolo=<?php echo $l['tipo_veicolo'] ?>&listino_id=<?php echo $l['listino_id'] ?>&tipo_pagamento=1" style="background-color:#1e73be!important;color:#ffffff;"><span class="button_icon"><i class="icon-cart" style="color:#ffffff!important;" aria-hidden="true"></i></span><span class="button_label">Paga in parcheggio <?php echo $l['subtotale'] ?> €</span></a>
              </div>
            </div>
          </div>
          <div class="column mcb-column mcb-item-0eadzj441 one-third tablet-one-third mobile-one column_button" style="">
            <div class="mcb-column-inner mcb-column-inner-0eadzj441 mcb-item-button-inner">
              <div class="button_align align_center">
                <a class="button pagaonline-item has-icon button_left button_size_2" href="<?php echo esc_url( get_permalink($wp_parcheggio) ); ?>?data_inizio_date=<?php echo urlencode($inizio_date) ?>&data_fine_date=<?php echo urlencode($fine_date) ?>&data_inizio_time=<?php echo urlencode($inizio_time) ?>&data_fine_time=<?php echo urlencode($fine_time) ?>&park_type=<?php echo $l['tipo_parcheggio'] ?>&veicolo=<?php echo $l['tipo_veicolo'] ?>&listino_id=<?php echo $l['listino_id'] ?>&tipo_pagamento=2" style="background-color:#81d742!important;color:#ffffff;"><span class="button_icon"><i class="icon-cart" style="color:#ffffff!important;" aria-hidden="true"></i></span><span class="button_label">Paga On-Line <?php echo $l['online'] ?> €</span></a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php
        }
      ?>
    </div>
  </div>
  <div class="section mcb-section mcb-section-foh0suad8" style="padding-top:20px;padding-bottom:45px">
    <div class="section_wrapper mcb-section-inner">
      <div class="wrap mcb-wrap mcb-wrap-sii4r4jht one  valign-top clearfix" style="">
        <div class="mcb-wrap-inner">
          <div class="column mcb-column mcb-item-trty5ob1t one column_divider">
            <div class="hr_zigzag" style="margin:0 auto 5px"><i class="icon-down-open" style="color:#1e73be"></i><i class="icon-down-open" style="color:#1e73be"></i><i class="icon-down-open" style="color:#1e73be"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

    <?php
    }
   }
  }
}

public function net_parking_connector_prenotazione($atts, $content = '', $tag){

    $gestionale_url = 'https://gestionale.parcheggioincloud.it/api.php';
    $inserimento_url = 'https://gestionale.parcheggioincloud.it/InserimentoPrenotazione.php';

    $parcheggio_conf_options = get_option( 'parcheggio_conf_option_name' );
    $pagina_testo = $parcheggio_conf_options['pagina_testo'];
    $select_car = $parcheggio_conf_options['select_car_option_0'];

    if ($_REQUEST['data_inizio_date'] && $_REQUEST['data_inizio_time']) {
    $inizio_time = urldecode($_REQUEST['data_inizio_time']);
    $inizio_date = urldecode($_REQUEST['data_inizio_date']);
    }  else {
    $timestamp = strtotime(date( 'H' ).':00') + 60*60*3;
    $inizio_time = date('H:i', $timestamp);
    $inizio_date = date('d-m-Y');
    }

    if ($_REQUEST['data_fine_date'] && $_REQUEST['data_fine_time']) {
    $fine_time = urldecode($_REQUEST['data_fine_time']);
    $fine_date = urldecode($_REQUEST['data_fine_date']);
    } else {
    $timestamp1 = strtotime(date( 'H' ).':00') + 60*60*3;
    $fine_time = date('H:i', $timestamp1);
    $fine_date = date('d-m-Y', mktime(0,0,0,date('m'),date('d')+1,date('Y')));
    }

    if ($_REQUEST['veicolo']) {
    $veicolo = urldecode($_REQUEST['veicolo']);
    } else {
    $veicolo = '';
    }

    if ($_REQUEST['park_type']) {
    $park_type = $_REQUEST['park_type'];
    } else {
    $park_type = $_REQUEST['park_type'];
    }

    if ($_REQUEST['listino_id']) {
    $listino_id = urldecode($_REQUEST['listino_id']);
    } else {
    $listino_id = '';
    }

    if ($_REQUEST['tipo_pagamento']) {
    $tipo_pagamento = $_REQUEST['tipo_pagamento'];
    } else {
    $tipo_pagamento = 1;
    }

    if ($_REQUEST['parcheggio_id']) {
    $parcheggio_id = urldecode($_REQUEST['parcheggio_id']);
    } else {
    $parcheggio_id = '';
    }

    /*$veicolo = urldecode($_REQUEST['veicolo']);
    $park_type = $_REQUEST['park_type'];*/
    /*$url_tipo = $gestionale_url.'?command=GetType';
    $tipo_parcheggio = json_decode(file_get_contents($url_tipo),true);
    $url_veicolo = $gestionale_url.'?command=GetVeicoli';
    $tipo_veicolo = json_decode(file_get_contents($url_veicolo),true);*/

    $url_servizi = $gestionale_url.'?command=GetServices';
    $servizi = json_decode(file_get_contents($url_servizi),true);

    $url_nazioni = $gestionale_url.'?command=GetNazioni';
    $nazioni = json_decode(file_get_contents($url_nazioni),true);
    $url_prov = $gestionale_url.'?command=GetProvince';
    $province = json_decode(file_get_contents($url_prov),true);

    $atts = shortcode_atts(array(
        'data_inizio_time' => $inizio_time,
        'data_inizio_date' => $inizio_date,
        'data_fine_time' => $fine_time,
        'data_fine_date' => $fine_date,
        'veicolo' => $veicolo,
        'park_type' => $park_type,
        'parcheggio_id' => $parcheggio_id,
        'css' => '',
        )
    ,$atts,$tag);

    $url_coperto = $gestionale_url."?command=BookingSearchMulti&data_inizio_date=".$atts['data_inizio_date']."&data_fine_date=".$atts['data_fine_date']."&data_inizio_time=".$atts['data_inizio_time']."&data_fine_time=".$atts['data_fine_time']."&parcheggio_id=".$atts['parcheggio_id']."";
    $parck_coperto = json_decode(file_get_contents($url_coperto),true);
    //print_r($parck_coperto);

    ?>
<div id="formAggiungi">
<div class="bootstrap prenotazione-<?php echo $atts['css'] ?>">
<div class="containers">
        <div class="row sfondowp">

            <div class="col-md-12 col-md-offset-0">
                <form role="form" id="formDestination" method="post" action="<?php echo $inserimento_url ?>">
                    <!--<legend class="text-center">Register</legend> -->
                     <input type="hidden" name="car" maxlength="60" />
                     <input type="hidden" id="waiting_paypal" name="waiting_paypal" value="0" />
                     <input type="hidden" name="privacyx" value="1"/>
                     <input type="hidden" name="inserimento" value="1"/>
                     <input type="hidden" name="cliente_id" value="0"/>
                     <input type="hidden" name="tipo" value="<?php echo $atts['veicolo'] ?>"/>
                     <input type="hidden" name="park_type" value="<?php echo $atts['park_type'] ?>"/>
                     <input type="hidden" name="parcheggio_id" value="<?php echo $atts['parcheggio_id'] ?>"/>
                     <input type="hidden" name="sconto_prenotazione_tipo" value="1"/> 
                     <div class="row">
                        <div class="form-group col-md-12 headtitolo">
                        <h5 class="titoloh5">Prenota il tuo parcheggio </h5>
                        </div>
                     </div>
                    <fieldset>
                        <div class="step1">
                        <div class="row">
                        <div class="form-group col-md-3">
                            <label for="data-inizio-date">Arrivo in parcheggio</label>
                            <input id="data_inizio_date" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="data_inizio_date" value="<?php echo $inizio_date;?>" placeholder="Data ingresso" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="data_inizio_time">Ora</label>
                            <select id="data_inizio_time" class="form-control testo_form_input orario_form_select blu refill selectpicker" name="data_inizio_time" autocomplete="off">
                            <option value="">Ingresso</option>
                            <?php echo $this->get_times_cerco($inizio_time, '+5 minutes'); ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="service_type">Servizio</label>
                            <div id="tipo_parcheggio">
                            <select id="service_type" class="form-control testo_form_input orario_form_select orario_form_noborder blu refill selectpicker" name="service_type" autocomplete="off">
                            <?php foreach ($parck_coperto as $key => $value) { ?>
                            <?php
                            $listini_parcheggio = $value['servizi_parcheggio'];
                            foreach ($listini_parcheggio as $key => $l) {
                            ?>
                            <option value="<?php echo $l['listino_id']; ?>" <?php if ($listino_id==$l['listino_id']) { echo 'selected="selected"';} ?>><?php echo $l['controllo_posti']['settore'] ?> <?php echo $l['controllo_posti']['tipo'] ?></option>
                            <?php } ?>
                            <?php } ?>
                            </select>
                            </div>
                        </div>
                        </div>
                        <div class="row">
                        <div class="form-group col-md-3">
                            <label for="data-fine-date">Partenza dal parcheggio</label>
                            <input id="data_fine_date" class="form-control testo_form_input blu refill tooltip_onload" data-placement="bottom" type="text" name="data_fine_date" value="<?php echo  $fine_date;?>" placeholder="Data uscita" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="data_fine_time">Ora</label>
                            <select id="data_fine_time" class="form-control testo_form_input orario_form_select orario_form_noborder blu refill selectpicker" name="data_fine_time" autocomplete="off">
                            <option value="">Uscita</option>
                            <?php echo $this->get_times_cerco($fine_time, '+5 minutes'); ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                    <label class="control-label col-sm-12 servizioption">Servizi</label>
                    <div class="col-sm-9" id="servizihtml">
                    <?php
                    foreach ($servizi as $key => $s) {
                    ?>
                        <div class="checkbox" id="items-service-<?php echo $s['id']; ?>">
                            <label>
                                <input type="checkbox" id="servizi" name="servizi[<?php echo $s['id']; ?>]" value="<?php echo $s['id']; ?>"  disabled/> <?php echo $s['nome']; ?>
                            </label>
                        </div>
                    <?php } ?>
                        </div>
                        </div>
                       </div>
                    <?php
                    if ($pagina_testo) {
                    ?>
                    <div class="row">
                    <div class="form-group col-md-4">
                    <p><?php echo $pagina_testo ?></p>
                    </div>
                    </div>
                    <?php } ?>
                    </div>
                    <div class="step1">
                    <div class="row">

            </div>
            <div class="row" style="display:none;">
           </div>
           </div>
           <div class="step2">

                        <div class="row">
                        <div class="form-group col-md-3">
                            <label for="nome">Nome *</label>
                            <input id="nome" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="nome" value="" placeholder="Nome" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="cognome">Cognome *</label>
                            <input id="cognome" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="cognome" value="" placeholder="Cognome" autocomplete="off">
                        </div>
                        <!--</div> -->

                        <!--<div class="row">  -->
                        <div class="form-group col-md-3">
                            <label for="passengers">Numero passeggeri *</label>
                            <input id="passengers" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="passengers" value="" placeholder="Numero passeggeri" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="email">Email *</label>
                            <input id="email" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="email" value="" placeholder="Email" autocomplete="off">
                        </div>
                        </div>

                        <div class="row">
                        <div class="form-group col-md-12">
                            <div class="radio">
                                <label class="radio-inline">
                                    <input type="radio" value="2" name="cliente_prenotazione" id="cliente_prenotazione">
                                    <b>Cliente gi&agrave; registrato </b>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" value="1" name="cliente_prenotazione" id="cliente_prenotazione">
                                    Registrati come cliente
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" value="0" name="cliente_prenotazione" id="cliente_prenotazione" checked="checked">
                                    Prenotazione senza registrazione
                                </label>
                            </div>
                        </div>
                        </div>

                        <div class="row">
                        <div class="form-group col-md-3">
                            <label for="coupon_code">Cod. Convenzione **</label>
                            <input id="coupon_code" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="coupon_code" value="" placeholder="Cod. Convenzione" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="phone">Cellulare *</label>
                            <input id="phone" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="phone" value="" placeholder="Cellulare" autocomplete="off">
                        </div>
                        <!--</div>-->

                        <!--<div class="row"> -->
                        <div class="form-group col-md-3">
                            <label for="model">Modello Veicolo</label>
                            <input id="model" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="model" value="" placeholder="Modello veicolo" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="plate">Targa Veicolo *</label>
                            <input id="plate" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="plate" value="" placeholder="Targa" autocomplete="off">
                        </div>
                        </div>

                        <div class="row">
                        <div class="form-group col-md-3">
                            <label for="flight_code">Sigla Volo Rientro</label>
                            <input id="flight_code" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="flight_code" value="" placeholder="Sigla Volo Rientro" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="return_provenience">Provenienza volo ritorno</label>
                            <input id="return_provenience" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="return_provenience" value="" placeholder="Provenienza volo ritorno" autocomplete="off">
                        </div>
                        <!--</div>  -->

                        <!--<div class="row">-->
                        <div class="form-group col-md-3">
                            <label for="flight_code_dest">Sigla Volo Partenza</label>
                            <input id="flight_code_dest" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="flight_code_dest" value="" placeholder="Sigla Volo partenza" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="partenza">Partenza volo</label>
                            <input id="partenza" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="partenza" value="" placeholder="partenza volo" autocomplete="off">
                        </div>
                        </div>

                        <div class="row">
                        <div class="form-group col-md-6">
                            <label for="customer_notes">Note</label>
                            <textarea placeholder="Note.." rows="3" class="form-control" id="customer_notes" name="customer_notes"></textarea>
                        </div>
                        </div>

                        <div class="row">
                        <div class="form-group col-md-6">
                            <label class="control-label col-sm-6 fattura">Richiesta Fattura</label>
                            <div class="col-sm-9">
                            <label class="radio-inline"><input type="radio" name="fattura" value="0" checked>No</label>
                            <label class="radio-inline"><input type="radio" name="fattura" value="1">Si</label>
                            </div>
                        </div>
                        </div>
                        <div id="fatturadiv">
                        <div class="row">
                        <div class="form-group col-md-6">
                            <label for="tipo_sogetto">Tipo Soggetto</label>
                            <select id="tipo_sogetto" name="tipo_sogetto" class="form-control">
                            <option value="">Seleziona tipo</option>
                            <option value="Privato" <?php if ($_REQUEST['tipo_sogetto']=='Privato') { echo 'selected="selected"';} ?>>Privato / persona fisica</option>
                            <option value="Azienda" <?php if ($_REQUEST['tipo_sogetto']=='Azienda') { echo 'selected="selected"';} ?>>Azienda / Ditta Individuale</option>
                            <option value="PA" <?php if ($_REQUEST['tipo_sogetto']=='PA') { echo 'selected="selected"';} ?>>PA</option>
                            </select>
                        </div>
                        <!--</div> -->
                        <!--<div class="row">-->
                        <div class="form-group col-md-3">
                            <label for="name">Indirizzo *</label>
                            <input id="name" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="indirizzo" value="" placeholder="Indirizzo" autocomplete="off">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="cap">Cap *</label>
                            <input id="cap" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="cap" value="" placeholder="Cap" autocomplete="off">
                        </div>
                        </div>
                        <div class="row">
                        <div class="form-group col-md-3">
                            <label for="nazione">Nazione</label>
                            <select id="nazione" name="nazione" class="form-control">
                            <option value="">Seleziona la nazione</option>
                            <?php
                            foreach ($nazioni as $key => $row_nazioni) { ?>
                            ?>
                            <option value="<?php echo $row_nazioni['iso_2'] ?>" <?php if ('IT'==$row_nazioni['iso_2']) { echo 'selected="selected"';} ?>><?php echo $row_nazioni['nome_stati'] ?></option>
                            <?php
                            }
                            ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="provincia">Provincia</label>
                            <input class="form-control" id="provincia" name="provincia" placeholder="Inserisci la provincia" type="text">
                                <select id="provincia" name="provincia" class="form-control provinciaselect">
                                <option value="">Seleziona la Provincia</option>
                                <?php
                                foreach ($province as $key => $row_italy_provincies) { ?>
                                ?>
                                <option value="<?php echo $row_italy_provincies['sigla'] ?>" <?php if ($_REQUEST['provincia']==$row_italy_provincies['sigla']) { echo 'selected="selected"';} ?>><?php echo $row_italy_provincies['provincia'] ?></option>
                                <?php
                                }
                                ?>
                                </select>
                        </div>
                        <!--</div>-->
                        <!--<div class="row">-->
                        <div class="form-group col-md-6">
                            <label for="citta">Citta *</label>
                            <input id="citta" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="citta" value="" placeholder="Citta" autocomplete="off">
                        </div>
                        </div>
                        <div class="row">
                        <div class="form-group col-md-3" id="aziendapa">
                            <label for="partita_iva">Partita Iva</label>
                            <input class="form-control" id="partita_iva" name="partita_iva" placeholder="Inserisci la Partita Iva" type="text" value="">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="codice_univoco">Codice Univoco</label>
                            <input class="form-control" id="codice_univoco" name="codice_univoco" placeholder="Inserisci il codice univoco" type="text" value="">
                        </div>
                        <!--</div>-->
                        <!--<div class="row">-->
                        <div class="form-group col-md-3">
                            <label for="codice_fiscale">Codice fiscale</label>
                            <input class="form-control" id="codice_fiscale" name="codice_fiscale" placeholder="Inserisci il Codice fiscale" type="text" value="">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="email_pec">Email PEC</label>
                            <input class="form-control" id="email_pec" name="email_pec" placeholder="Inserisci la PEC" type="text" value="">
                        </div>
                        </div>
                        <div class="row">
                        <div class="form-group col-md-6" id="ragionesocialeditta">
                            <label for="ragione_sociale">Ragione Sociale</label>
                            <input class="form-control" id="ragione_sociale" name="ragione_sociale" placeholder="Inserisci la ragione sociale" type="text" value="">
                        </div>
                        </div>
                        </div>
           </div>

                        <div class="row">
                        <div class="form-group col-md-12">
                        <div id="cacolo"></div>
                        </div>
                        </div>

                        <div class="row">
                        <div class="form-group col-md-6">
                        <div id="cacolo-tariffa"></div>
                        </div>
                        </div>

                        <div class="row">
                        <div class="form-group col-md-12">
                        <div id="disponibile"></div>
                        </div>
                        </div>

                        <div class="step2">

                        <div class="row" style="display:none;">
                        <div class="form-group col-md-6">
                        <div class="alert alert-info">
                            <strong>Si prega La gentile clientela di attendere la redirezione sul gateway di paypal in caso di pagamento online</strong>
                        </div>
                        </div>
                        </div>
                        <div class="row" style="display:none;">
                        <div class="form-group col-md-4">
                        <div class="alert alert-info">
                            <strong>LA FATTURA DOVRA' ESSERE RICHIESTA IN PARCHEGGIO ESCLUSIVAMENTE IL GIORNO DI INGRESSO PRIMA DELL'EMISSIONE DELLO SCONTRINO FISCALE</strong>
                        </div>
                        </div>
                        </div>
                        <div class="row">
                        <div class="form-group">
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="1" id="" name="privacy" id="privacy">
                                    * Accetto <a href="/cookie-policy">la informativa sulla privacy ai sensi dell'art. 13 d.lgs. 196/03 </a>.
                                </label>
                            </div>
                        </div>
                        </div>
                        </div>
                        <div class="row">
                        <div class="form-group">
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="1" id="" name="condizioni_parcheggio" id="condizioni_parcheggio">
                                    * Accetto le <a href="/condizioni">condizioni generali del parcheggio</a>.
                                </label>
                            </div>
                        </div>
                        </div>
                        </div>
                        <div class="row" style="display:none;">
                        <div class="form-group">
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="1" id="" name="clausole_parcheggio" id="clausole_parcheggio">
                                    * Accetto per espressa accettazione delle clausole di cui ai punti h), j), l), m) e o) del regolamento contrattuale.
                                </label>
                            </div>
                        </div>
                        </div>
                        </div>

                        <div class="row" style="display:none;">
                        <div class="form-group col-md-6">
                         <i>Confermando la prenotazione si accetta integralmente la presente informativa ai sensi dell'art. 10 della Legge 675/96. I dati personali raccolti inviando questo form sono trattati esclusivamente da demo per rispondere alle tue richieste. Anche in presenza di un tuo consenso, ti informiamo che, ai sensi e per gli effetti dell'Art. 9 comma 1 lett. e) e c) della Legge n. 675 del 1996,
                         tali dati saranno comunque cancellati e/o distrutti a decorrere da 24 mesi dalla data di invio del presente messaggio. Puoi chiedere, in ogni momento, quali sono i tuoi dati personali conservati e come vengono utilizzati. Puoi anche esercitare il diritto di correggerli, aggiornarli, cancellarli ed opporti al loro trattamento (secondo quanto previsto dall'Art. 13 della legge n. 675/1996).
                         Per ogni informazione e richiesta, puoi rivolgerti ai Responsabili interni del trattamento: Responsabile Servizio Legale presso la nostra Direzione Generale. </i>
                        </div>
                        </div>

                        <div class="row">
                        <div class="paymentWrap">
							<div class="btn-group paymentBtnGroup btn-group-justified" data-toggle="buttons">
					            <label class="btn paymentMethod <?php if($tipo_pagamento==1){ print 'active'; }?>">
					            	<div class="method paypal"></div>
					                <input type="radio" name="tipo_pagamento" value="1" <?php if($tipo_pagamento==1){ print ' checked="checked"'; }?>>
					            </label>
                                <label class="btn paymentMethod <?php if($tipo_pagamento==2){ print 'active'; }?>">
					            	<div class="method contanti"></div>
					                <input type="radio" name="tipo_pagamento" value="2" <?php if($tipo_pagamento==2){ print ' checked="checked"'; }?>>
					            </label>
                                <label class="btn paymentMethod">
					            	<div class="method credito"></div>
					                <input type="radio" name="tipo_pagamento" value="3">
					            </label>
					        </div>
						</div>
                        </div>
                    </div>
                    <div class="step2">
                    <div class="row bottone-chisura">
                    <div class="form-group col-sm-3" id="bottone-cerca-wrap">
                    <label for="submit-search" style="color: #fff;">cerca</label>
                        <div class="bottone-cerca">
                                   <div class="col-xs-6 center_align_text botone-sl" id="nascondi">
                                       <button class="btn btn-primary" id="submit-search">Prenota</button>
                                   </div>
                                   <div class="col-xs-12 top_margin_15">
                                   <p align="left"><span class="Stile3"><br><br>* Campi obbligatori</span></p>
                                   </div>
                        </div>
                    </div>
                    </div>
                    </div>

                    <div class="row bottone-chisura1 bottone-continua">
                    <div class="form-group col-sm-3" id="bottone-cerca-wrap1">
                        <div class="bottone-cerca1">
                                   <div class="col-xs-6 center_align_text botone-sl" id="nascondi1">
                                       <button class="btn btn-primary" id="submit-continua">Continua & Prenota</button>
                                   </div>
                        </div>
                    </div>
                    </div>

                    <!--<div class="row bottone-chisura1 bottone-prenota">
                    <div class="form-group col-sm-3" id="bottone-cerca-wrap1">
                        <div class="bottone-cerca1">
                                   <div class="col-xs-6 center_align_text botone-sl" id="nascondi1">
                                       <button class="btn btn-primary" id="submit-prenota">Continua & Prenota</button>
                                   </div>
                        </div>
                    </div>
                    </div>-->
                   </fieldset>
                </form>
            </div>

        </div>
    </div>
</div>
 </div>

 <!-- Conferma prenotazione -->

<div id="risultatoprenotazione">
    <div class="bootstrap conferma-<?php echo $atts['css'] ?>">
        <div class="containers">
        <div id="mostraconferma"></div>
        </div>
    </div>
</div>
    <?php
}

function add_bootstrap_scripts() {

   $parcheggio_conf_options = get_option( 'parcheggio_conf_option_name' );
   $google_key = $parcheggio_conf_options['google_key'];

  //wp_enqueue_style( 'parcheggio-css', plugin_dir_url( __FILE__ ) .'lib/custom.css', array(), '1.1', 'all');
  wp_enqueue_style( 'bootstrap-css', plugin_dir_url( __FILE__ ) .'lib/dependencies/css/bootstrap.css', array(), '1.1', 'all');
  wp_enqueue_style( 'bootstrap-slider-js', plugin_dir_url( __FILE__ ) .'lib/dist/css/bootstrap-slider.css', array(), '1.1', 'all');
  wp_enqueue_style( 'bootstrap-select-js', plugin_dir_url( __FILE__ ) .'lib/select/css/bootstrap-select.min.css', array(), '1.1', 'all');
  wp_enqueue_style( 'bootstrap-autocomplete-js', plugin_dir_url( __FILE__ ) .'lib/autocomplete/easy-autocomplete.css', array(), '1.1', 'all');
  wp_enqueue_style( 'bootstrap-time-css', plugin_dir_url( __FILE__ ) . 'timepicker/css/bootstrap-timepicker.css', array(), '1.1', 'all');
  wp_enqueue_style( 'bootstrap-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1', 'all');
  wp_enqueue_style( 'custom-css', plugin_dir_url( __FILE__ ) . 'custom.css', array(), '1.1', 'all');
  wp_enqueue_style( 'font-css-animate', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome-animation/0.2.1/font-awesome-animation.css', array(), '1.12.1', 'all');

  wp_enqueue_script('jquery');
  wp_enqueue_script( 'bootstrap-js', plugin_dir_url( __FILE__ ) .'lib/dependencies/js/bootstrap.min.js', array ( 'jquery' ), 1.1, true);
  //wp_enqueue_script( 'bootstrap-slider', plugin_dir_url( __FILE__ ) .'lib/dist/bootstrap-slider.min.js', array ( 'jquery' ), 1.1, true);
  wp_enqueue_script( 'bootstrap-select',  plugin_dir_url( __FILE__ ) .'lib/select/js/bootstrap-select.min.js', array ( 'jquery' ), 1.1, true);
  wp_enqueue_script( 'bootstrap-select-lang', plugin_dir_url( __FILE__ ) .'lib/select/js/i18n/defaults-'. get_locale() .'.min.js', array ( 'jquery' ), 1.1, true);
  //wp_enqueue_script( 'bootstrap-select-dest', plugin_dir_url( __FILE__ ) . 'lib/parcheggio.js', array ( 'jquery' ), 1.1, true);
  wp_enqueue_script( 'bootstrap-modernizr', plugin_dir_url( __FILE__ ) .'js/vendor/modernizr-respond.min.js', array ( 'jquery' ), 1.1, true);
  wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', false, '1.12.1');
  //wp_enqueue_script('jquery-ui-time', 'http://trentrichardson.com/examples/timepicker/jquery-ui-timepicker-addon.js', false, '1.12.1');
  //wp_enqueue_script( 'bootstrap-js-time', plugin_dir_url( __FILE__ ) . 'timepicker/js/bootstrap-timepicker.js', false, 1.1);
  wp_enqueue_script( 'bootstrap-autocomplete-dest', plugin_dir_url( __FILE__ ) .'lib/autocomplete/wp-jquery.easy-autocomplete.js', array ( 'jquery' ), 1.1, true);
  wp_enqueue_script( 'bootstrap-js-validate', plugin_dir_url( __FILE__ ) . 'validate/dist/jquery.validate.js', false, 1.1);
  wp_enqueue_script( 'jquery-ui-locale', plugin_dir_url( __FILE__ ) . 'js/datepicker-'.get_locale().'.js', false, 1.1);

}

function parcheggio_script() {

    $gestionale_url = 'https://gestionale.parcheggioincloud.it/';

    $parcheggio_conf_options = get_option( 'parcheggio_conf_option_name' );
    $google_key = $parcheggio_conf_options['google_key'];
    $pagina_ricerca_id = $parcheggio_conf_options['pagina_ricerca_id'];
    $pagina_prenotazione_id = $parcheggio_conf_options['pagina_prenotazione_id'];

    ?>

    <script type="text/javascript">
    jQuery.noConflict();
    jQuery(document).ready(function() {

    });
    jQuery("body").prepend('<div id="loader"></div>');
    jQuery("#risultatoprenotazione").hide();

    jQuery("#data-inizio-date").datepicker({
    	dateFormat: 'dd-mm-yy',
    	todayHighlight: true,
    	setDate: new Date(),
    	autoclose: true,
        minDate: 0,
        onSelect: function(dateStr) {
           var newDate = jQuery(this).datepicker('getDate');
           if (newDate) { // Not null
                   newDate.setDate(newDate.getDate() + 1);
           }
           jQuery('#data-fine-date').datepicker('setDate', newDate).datepicker('option', 'minDate', newDate);
           }
    });

    jQuery("#data-fine-date").datepicker({
    	dateFormat: 'dd-mm-yy',
    	todayHighlight: true,
    	startDate: '+1d',
        minDate: 0,
    	autoclose: true
    });

    jQuery("#data_inizio_date").datepicker({
    	dateFormat: 'dd-mm-yy',
    	todayHighlight: true,
    	setDate: new Date(),
    	autoclose: true,
        minDate: 0,
        onSelect: function(dateStr) {
           var newDate = jQuery(this).datepicker('getDate');
           if (newDate) { // Not null
                   newDate.setDate(newDate.getDate() + 1);
           }
           jQuery('#data_fine_date').datepicker('setDate', newDate).datepicker('option', 'minDate', newDate);
           }
    });

    jQuery("#data_fine_date").datepicker({
    	dateFormat: 'dd-mm-yy',
    	todayHighlight: true,
    	startDate: '+1d',
        minDate: 0,
    	autoclose: true
    });

    </script>


    <script type="text/javascript">

         jQuery(document).ready(function() {
          AjaxTipoParcheggio();
          //Prezzi_Api();
          servizi();
          cacolo_prezzo();
          jQuery('input[name=data_inizio_date]').change(function() {
                <?php
                if ($this->Conf_Active_List() == 1) {
                ?>
                lista_listini();
                <?php } ?>
    			servizi();
                cacolo_prezzo();
                Availability();
                ChiusureApi();
                //Prezzi_Api();
                //controlloore();
    	  });
          jQuery('input[name=data_fine_date]').change(function() {
                <?php
                if ($this->Conf_Active_List() == 1) {
                ?>
                lista_listini();
                <?php } ?>
    			servizi();
                cacolo_prezzo();
                Availability();
                ChiusureApi();
                //Prezzi_Api();
                //controlloore();
    	  });
          jQuery('input[name=tipo]').change(function() {
                <?php
                if ($this->Conf_Active_List() == 1) {
                ?>
                lista_listini();
                <?php } ?>
    			servizi();
                cacolo_prezzo();
                Availability();
                //Prezzi_Api();
    	  });
          jQuery('select[name=park_type]').change(function() {
                <?php
                if ($this->Conf_Active_List() == 1) {
                ?>
                lista_listini();
                <?php } ?>
    			servizi();
                cacolo_prezzo();
                Availability();
                //Prezzi_Api();
    	  });
          jQuery('input[name="servizi[]"]').click(function(){
    			//alert('ok');
    	  })
          jQuery('select[name=tipo_incremento]').change(function() {
    			servizi();
                cacolo_prezzo();
    	  });
          jQuery('select[name=tipo_sconto]').change(function() {
    			servizi();
                cacolo_prezzo();
    	  });
          jQuery('input[name=coupon_code]').change(function() {
    			servizi();
                cacolo_prezzo();
                //Prezzi_Api();
    	  });
          <?php
          if ($this->Conf_Active_Pacchetto() == 1) {
          ?>
          jQuery('select[name=pacchetto_parcheggio]').change(function() {
    			lista_pachetto_date();
    	  });
          <?php } ?>

          jQuery('select[name=data_inizio_time]').change(function() {
                <?php
                if ($this->Conf_Active_List() == 1) {
                ?>
                lista_listini();
                <?php } ?>
    			servizi();
                cacolo_prezzo();
                Availability();
                //Prezzi_Api();
                //controlloore();
    	  });
          jQuery('select[name=data_fine_time]').change(function() {
                <?php
                if ($this->Conf_Active_List() == 1) {
                ?>
                lista_listini();
                <?php } ?>
    			servizi();
                cacolo_prezzo();
                Availability();
                //Prezzi_Api();
                //controlloore();
    	  });

          jQuery('#service_type').change(function() {
                Get_TypeParking();
                //Prezzi_Api();
                //controlloore();
    	  });

    });

    function servizi() {
    var data_inizio_date = jQuery('input[name=data_inizio_date]').val();
    var data_fine_date	 = jQuery('input[name=data_fine_date]').val();
    var tipo_veicolo	 = jQuery('input[name=tipo]').val();
    var tipo_parcheggio	 = jQuery('input[name=park_type]').val();
    var listino = '';

    var get_listino	 = jQuery('#service_type').val();
    if ( get_listino >=1) {
      var listino = '&listino=' + get_listino;
    } else {
      var listino = '';
    }

    if ( data_inizio_date && data_fine_date && tipo_veicolo && tipo_parcheggio) {
    			jQuery.ajax({
    				type: 'GET',
    				url: '<?php echo $gestionale_url ?>servizi.php',
    				data: 'data_inizio_date=' + data_inizio_date + '&data_fine_date=' + data_fine_date + '&tipo_veicolo=' + tipo_veicolo + '&tipo_parcheggio=' + tipo_parcheggio+listino,
    				success: function(msg){
                      if(!jQuery.trim(msg)){
                      jQuery('#servizihtml').html('Nessun servizio per questo periodo selezionato');
                      //alert('Codice convenzionato errato');
                      }else{
                      jQuery('#servizihtml').html(msg);
                      //alert('Codice convenzionato errato');
                      }
                }
        });
        } else {
            //$('#conve').hide();
        }
    }

    function cacolo_prezzo() {
    var data_inizio_date = jQuery('input[name=data_inizio_date]').val();
    var data_fine_date	 = jQuery('input[name=data_fine_date]').val();
    var tipo_veicolo	 = jQuery('input[name=tipo]').val();
    var park_type	 = jQuery('input[name=park_type]').val();
    var servizi = jQuery("input[name='servizi[]']:checked").serializeArray();
        jQuery.each( servizi, function( i, field ) {
          console.log('i', field)
        });
    if ( data_inizio_date && data_fine_date && tipo_veicolo && tipo_parcheggio) {
    			jQuery.ajax({
    				type: 'GET',
    				url: '<?php echo $gestionale_url ?>prezzo_prenotazione.php',
    				data: jQuery('#formDestination').serialize(),
    				success: function(msg){
                      if(!jQuery.trim(msg)){
                      jQuery('#cacolo').html('Nessun servizio per questo periodo selezionato');
                      //alert('Codice convenzionato errato');
                      }else{
                      jQuery('#cacolo').html(msg);
                      //alert('Codice convenzionato errato');
                      var totale = jQuery('#subtotale').val();
                      jQuery('#totale').val(totale);
                      var online = jQuery('#online').val();
                      jQuery('#totale_online').val(online);
                      if(totale >= 1){
                      jQuery(".bottone-cerca .botone-sl").show();
                      } else {
                      jQuery(".bottone-cerca .botone-sl").hide();
                      }
                      }
                }
        });
        } else {
            //$('#conve').hide();
        }
    }

    /*function PrenotaPulsante() {
    jQuery("#formDestination").submit(function(e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.

        var form = jQuery(this);
        var url = form.attr('action');

        jQuery.ajax({
               type: "POST",
               url: url,
               data: form.serialize(), // serializes the form's elements.
               success: function(msg)
               {
               jQuery("#formAggiungi").hide();
               jQuery("#risultatoprenotazione").show();
               jQuery('#mostraconferma').html(msg);
                   //alert(data); // show response from the php script.
               }
             });
            });
    	}*/

    jQuery("input#provincia").hide();
    jQuery('#nazione').on('change', function() {
      //alert( this.value );
      var nazione = this.value;
      if (nazione == 'IT') {
      jQuery("input#provincia").hide();
      jQuery(".provinciaselect").show();
        } else {
      jQuery("input#provincia").show();
      jQuery(".provinciaselect").hide();
        }
    });

    jQuery("#fatturadiv").hide();
    jQuery('input[name=fattura]').change(function() {
      var fattura = jQuery("input[name='fattura']:checked"). val();
      if (fattura == 1) {
      jQuery("#fatturadiv").show();
        } else {
      jQuery("#fatturadiv").hide();
        }
    });

    function Availability()
    {
        var response = "";
        var form_data = {
            data_inizio_date: jQuery('input[name=data_inizio_date]').val(),
            data_fine_date: jQuery('input[name=data_fine_date]').val(),
            data_inizio_time: jQuery('select[name=data_inizio_time]').val(),
            data_fine_time: jQuery('select[name=data_fine_time]').val(),
            tipo: jQuery( 'input[name=tipo]' ).val(),
            park_type: jQuery('input[name=park_type]').val(),
            parcheggio_id: jQuery('input[name=parcheggio_id]').val(),
            is_ajax: 1
        };
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $gestionale_url ?>api.php?command=BookingSearch",
            data: form_data,
            success: function(response)
            {

            console.log(response);

    	    var json_obj = response;//parse JSON

            //alert(json_obj.controllo_posti.disponibili);

            var output="<div class='risposta-disponibilita'>";
            if (json_obj.controllo_posti.disponibili >= 1) {
            output+="<div class='verde'>Posto " + json_obj.controllo_posti.tipo + ' ' + json_obj.controllo_posti.settore + ' disponibile</div>';
            jQuery(".bottone-cerca").show();
            } else {
            output+="<div class='rosso'>Posto " + json_obj.controllo_posti.tipo + ' ' + json_obj.controllo_posti.settore + ' non disponibile</div>';
            jQuery(".bottone-cerca").hide();
            }
            if (json_obj.giorni >= json_obj.max_prenotabile.max_giorni && json_obj.max_prenotabile.max_day_active == 1) {
            output+="<div class='rosso-chiamare'>" + json_obj.max_prenotabile.max_day_text + '</div>';
            jQuery("#bottone-cerca-wrap").hide();
            } else {
            jQuery("#bottone-cerca-wrap").show();
            }
            output+="</div>";

            jQuery('#disponibile').html(output);

                /*$('span').html(output);  */
            },
            dataType: "json"//set to JSON
        })
    }

    function ChiusureApi()
    {
        var response = "";
        <?php
        if ( is_page( $pagina_prenotazione_id ) ) {
        ?>
        var form_data = {
            data_inizio_date: jQuery('input[name=data_inizio_date]').val(),
            data_fine_date: jQuery('input[name=data_fine_date]').val(),
            tipo: jQuery( 'input[name=tipo]:checked' ).val(),
            park_type: jQuery('#park_type').val(),
            is_ajax: 1
        };
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $gestionale_url ?>api.php?command=GetChiusure",
            data: form_data,
            success: function(response)
            {

            console.log(response);

    	    var json_obj = response;//parse JSON

            //alert(json_obj.controllo_posti.disponibili);

            var output="";
            if (json_obj[1].chiuso >= 1) {
            output+="" + json_obj[1].messaggio + '';
            jQuery(".bottone-chisura").hide();
            alert(output);
            } else {
            output+="";
            jQuery(".bottone-chisura").show();
            }
            //output+="";

            //alert(output);

                /*$('span').html(output);  */
            },
            dataType: "json"//set to JSON
        })
        <?php
        } else {
        }
        ?>
    }

    </script>
    <script type="text/javascript">

    jQuery(document).ready(function() {
        jQuery( "#formDestination" ).validate( {
    				rules: {
    					nome: "required",
    					cognome: "required",
                        passengers: "required",
                        email: {
    						required: true,
    						email: true
    					},
                        phone: {
    						required: true,
    						minlength: 9
    					},
                        plate: "required",
    					privacy: "required",
                        /*flight_code: "required",
                        return_provenience: "required",*/
                        tipo: "required",
                        park_type: "required",
                        codice_fiscale: {
    						required: function(element){
                                return jQuery("#tipo_sogetto").val() == 'Privato' && jQuery("input[name='fattura']:checked").val() == '1';
                            },
                            codfiscale: function(element){
                                return jQuery("#tipo_sogetto").val() == 'Privato' && jQuery("input[name='fattura']:checked").val() == '1';
                            },
    				    },
                        partita_iva: {
    						required: function(element){
                                return jQuery("#tipo_sogetto").val() == 'Azienda' && jQuery("input[name='fattura']:checked").val() == '1' || jQuery("#tipo_sogetto").val() == 'PA' && jQuery("input[name='fattura']:checked").val() == '1' || jQuery("#tipo_sogetto").val() == 'Azienda' && jQuery("input[name='fattura']:checked").val() == '1';
                            },
                            pIva: function(element){
                                return jQuery("#tipo_sogetto").val() == 'Azienda' && jQuery("input[name='fattura']:checked").val() == '1' || jQuery("#tipo_sogetto").val() == 'PA' && jQuery("input[name='fattura']:checked").val() == '1' || jQuery("#tipo_sogetto").val() == 'Azienda' && jQuery("input[name='fattura']:checked").val() == '1';
                            },
    				    },
                        tipo_sogetto: {
    						required: function(element){
                                return jQuery("input[name='fattura']:checked").val() == '1';
                            }
    				    },
                        ragione_sociale: {
    						required: function(element){
                                return jQuery("#tipo_sogetto").val() == 'Azienda' && jQuery("input[name='fattura']:checked").val() == '1' || jQuery("#tipo_sogetto").val() == 'PA' && jQuery("input[name='fattura']:checked").val() == '1' || jQuery("#tipo_sogetto").val() == 'Azienda' && jQuery("input[name='fattura']:checked").val() == '1';
                            }
    				    },
                        indirizzo: {
    						required: function(element){
                                return jQuery("input[name='fattura']:checked").val() == '1';
                            },
    						minlength: 5
    				    },
                        cap: {
    						required: function(element){
                                return jQuery("input[name='fattura']:checked").val() == '1';
                            },
    						minlength: 3
    				    },
                        nazione: {
    						required: function(element){
                                return jQuery("input[name='fattura']:checked").val() == '1';
                            }
    				    },
                        provincia: {
    						required: function(element){
                                return jQuery("input[name='fattura']:checked").val() == '1';
                            }
    				    },
                        citta: {
    						required: function(element){
                                return jQuery("input[name='fattura']:checked").val() == '1';
                            },
    						minlength: 3
    				},
                    condizioni_parcheggio: "required",
                    clausole_parcheggio: "required",
    				},
    				messages: {
    					nome: "Inserisci il tuo nome",
    					cognome: "Inserisci il tuo cognome",
                        passengers: "Inserisci i passeggeri",
    					email: "Si prega di inserire un indirizzo email valido",
                        phone: {
    						required: "Inserisci il tuo telefono",
    						minlength: "Deve contenere almeno 9 caratteri"
    					},
                        plate: "Inserisci la targa Veicolo",
    					privacy: "Accetta i termini e condizioni",
                        flight_code: "Inserisci il numero volo e terminal",
                        return_provenience: "Inserisci la provenienza",
                        tipo: "Seleziona il veicolo",
                        park_type: "Seleziona il tipo di parcheggio",
                        codice_fiscale: {
                              required: "Inserire il codice fiscale",
                              codfiscale: 'Codice fiscale non valido',
                              },
                        partita_iva: {
                              required: "Inserire la partita iva",
                              pIva: 'Partita iva non valida',
                              },
                        tipo_sogetto: "Seleziona il soggetto",
                        ragione_sociale: "Inserisci la ragione sociale",
                        indirizzo: {
    						required: "Inserisci il tuo indirizzo",
    						minlength: "Deve contenere almeno 5 caratteri"
    				    },
                        cap: {
    						required: "Inserisci il tuo cap",
    						minlength: "Deve contenere almeno 3 caratteri"
    				    },
                        nazione: "Seleziona la nazione",
                        provincia: "Inserisci la provincia",
                        citta: {
    						required: "Inserisci la tua citta",
    						minlength: "Deve contenere almeno 3 caratteri"
    				    },
                        condizioni_parcheggio: "E' obligatorio accettare le condizioni del parcheggio",
                        clausole_parcheggio: "E' obligatorio accettare le clausole del parcheggio",
    				},
    				errorElement: "em",
    				errorPlacement: function ( error, element ) {
    					// Add the `help-block` class to the error element
    					error.addClass( "help-block" );

    					// Add `has-feedback` class to the parent div.form-group
    					// in order to add icons to inputs
    					element.parents( ".form-group" ).addClass( "has-feedback" );

    					if ( element.prop( "type" ) === "checkbox" ) {
    						error.insertAfter( element.parent( "label" ) );
    					} else {
    						error.insertAfter( element );
    					}

    					// Add the span element, if doesn't exists, and apply the icon classes to it.
    					if ( !element.next( "span" )[ 0 ] ) {
    						jQuery( "<span class='glyphicon glyphicon-remove form-control-feedback'></span>" ).insertAfter( element );
    					}
    				},
    				success: function ( label, element ) {
    					// Add the span element, if doesn't exists, and apply the icon classes to it.
    					if ( !jQuery( element ).next( "span" )[ 0 ] ) {
    						jQuery( "<span class='glyphicon glyphicon-ok form-control-feedback'></span>" ).insertAfter( jQuery( element ) );
    					}
    				},
    				highlight: function ( element, errorClass, validClass ) {
    					jQuery( element ).parents( ".form-group" ).addClass( "has-error" ).removeClass( "has-success" );
    					jQuery( element ).next( "span" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
    				},
    				unhighlight: function ( element, errorClass, validClass ) {
    					jQuery( element ).parents( ".form-group" ).addClass( "has-success" ).removeClass( "has-error" );
    					jQuery( element ).next( "span" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
    				},
                    submitHandler: function (form,event) {
                        //var spinner = jQuery('#loader');
                        event.preventDefault();
                        var url = jQuery(form).attr('action');
                        //var ControlloUtente = AjaxControlloPrenotazione();
                        /*if ( jQuery('#online').val() !="" ) {
                        jQuery('#online').val(jQuery('input[name=subtotale]').val());
                        }*/
                        //if ( ControlloUtente ) {
                        jQuery('#loader').show();
                        jQuery.ajax({
                        type: "POST",
                        url: url,
                        data: jQuery(form).serialize(),
                        success: function (msg) {
                         jQuery("#formAggiungi").hide();
                         jQuery("#risultatoprenotazione").show();
                         jQuery('#mostraconferma').html(msg);
                         jQuery('html, body').animate({scrollTop: '0px'}, 1000);
                     }
                 }).done(function(resp) {
                    jQuery('#loader').hide();
                });
                 return false; // required to block normal submit since you used ajax
                //}
                }
    			} );
    });
    </script>
    <script>
    jQuery(document).ready(function() {
      jQuery('input[name=tipo]').change(function() {
                AjaxTipoParcheggio();
    	  });
    });
    function AjaxTipoParcheggio() {

    var tipo_veicolo	 = jQuery("input[name='tipo']:checked").val();
    var tipo_parcheggio	 = jQuery('#park_type').val();
    var opt	 = 'prenotazione';

    if ( tipo_veicolo) {
    			jQuery.ajax({
    				type: 'GET',
    				url: '<?php echo $gestionale_url ?>ajax_tipologia_front.php',
    				data: 'tipo_veicolo=' + tipo_veicolo + '&park_type=' + tipo_parcheggio  + '&opt=' + opt,
    				success: function(msg){
                      if(!jQuery.trim(msg)){
                      //jQuery('#park_type').html('Nessun servizio per questo periodo selezionato');
                      //alert('Codice convenzionato errato');
                      }else{
                      jQuery('#tipo_parcheggio').html(msg);
                      jQuery('#park_type').selectpicker('refresh');
                      servizi();
                      cacolo_prezzo();
                      Availability();
                      //alert('Codice convenzionato errato');
                      }
                }
        });
        } else {
            //$('#conve').hide();
        }
    }
    </script>

    <?php
    if (empty($_REQUEST['veicolo'])) {
    ?>
    <script>
    jQuery(function() {
        var $radios = jQuery('input:radio[name=tipologia]');
        if($radios.is(':checked') === false) {
            $radios.filter('[value=1]').prop('checked', true);
        }
        var $radiosv = jQuery('input:radio[name=veicolo]');
        if($radiosv.is(':checked') === false) {
            $radiosv.filter('[value=1]').prop('checked', true);
        }
    });



    </script>
    <?php } ?>
    <script>

    function controlloore() {

        var today = new Date();

        var data_inizio_date = jQuery('input[name=data_inizio_date]').val();
        var data_inizio_time = jQuery('select[name=data_inizio_time]').val();
        var datearrivo = data_inizio_date;
        var arr_date = datearrivo.split('-');
        var oraarrivo = data_inizio_time;
        var arr_ora = oraarrivo.split(':');

        var date1 = new Date(today.getFullYear(), today.getMonth(), today.getDate(), today.getHours(), today.getMinutes());
        var date2 = new Date(arr_date[2], arr_date[1]-1, arr_date[0], arr_ora[0], arr_ora[1]);
        var msec = date2 - date1;
        var mins = Math.floor(msec / 60000);
        var hrs = Math.floor(mins / 60);
        var days = Math.floor(hrs / 24);
        var yrs = Math.floor(days / 365);
        mins = mins % 60;
        hrs = hrs % 24;
        days = days % 365;

        if ( days == 0 && hrs <= 12 ) {
          jQuery("#nascondi").hide();
          alert('Non e possibile prenotare prima di 12 ore');
        } else {
             jQuery("#nascondi").show();
            //$('#conve').hide();
            //alert('Giorni ' + days + ' Ore ' + hrs);
        }
    }

    function Prezzi_Api()
    {
        var response = "";
        var form_data = jQuery('#formDestination').serialize();
        var pagamento_tipo_anticpo	 = jQuery("input[name='pagamento_tipo_anticpo']:checked").val();
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $gestionale_url ?>api.php?command=BookingSearch",
            data: form_data,
            success: function(response)
            {

            console.log(response);

    	    var json_obj = response;//parse JSON

            //alert(json_obj.subtotale);

            var output="<div class='risposta-calcolo alert alert-info'>";
            if (json_obj.anticipo.totale >= 1 && pagamento_tipo_anticpo == 1) {
            jQuery('input[name=anticipo]').val(json_obj.anticipo.valore_commisione);
            var anticipo = jQuery('input[name=anticipo]').val();
            var totale_parcheggio = json_obj.totale - jQuery('input[name=anticipo]').val();
            output+="<div class='tariffa'><p class='text-center'><?php _e('Tariffa totale parcheggio', 'parcheggio-partner'); ?>: &euro; " + json_obj.totale + "  - <?php _e('Anticipo prenotazione', 'parcheggio-partner'); ?>: &euro; " + anticipo + "</p></div>";
            output+="<div class='tariffa'><p class='text-center'><?php _e('Saldo da pagare in parcheggio ', 'parcheggio-partner'); ?>: &euro; " + totale_parcheggio + "</p></div>";
            } else if (json_obj.totale >= 1 && pagamento_tipo_anticpo == 0) {
            jQuery('input[name=anticipo]').val(0);
            var anticipo = jQuery('input[name=anticipo]').val();
            output+="<div class='tariffa'><p class='text-center'><?php _e('Tariffa totale parcheggio', 'parcheggio-partner'); ?>: &euro; " + json_obj.totale + "</p></div>";
            } else {
            output+="<div class='tariffa'><p class='text-center'><?php _e('Tariffa', 'parcheggio-partner'); ?>: -- &euro;</p></div>";
            }
            output+="</div>";

            jQuery('#cacolo-tariffa').html(output);

                /*$('span').html(output);  */
            },
            dataType: "json"//set to JSON
        })
    }

    function Get_TypeParking()
    {
        var response = "";
        var form_data = {
            listino: jQuery('select[name=service_type]').val(),
            is_ajax: 1
        };
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $gestionale_url ?>api.php?command=SingoloListino",
            data: form_data,
            success: function(response)
            {

            console.log(response);

    	    var json_obj = response;//parse JSON

            //alert(json_obj.controllo_posti.disponibili);
            //alert(json_obj.tipo_veicolo);

            if (json_obj.parcheggio_id >= 1) {
            jQuery('input[name=tipo]').val(json_obj.tipo_veicolo);
            jQuery('input[name=park_type]').val(json_obj.tipo_parcheggio);
            servizi();
            cacolo_prezzo();
            Availability();
            }
            },
            dataType: "json"//set to JSON
        })
    }

    </script>
    <script type="text/javascript">
    function SetUserData()
    {
        var response = "";
        var form_data = {
            utente_id: jQuery('input[name=email]').val(),
            type: 'email',
            livello: 3,
            is_ajax: 1
        };
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $gestionale_url ?>api.php?command=GetUserAdv",
            data: form_data,
            success: function(response)
            {

            console.log(response);

    	    var json_obj = response;//parse JSON

            if (json_obj.user_id) {
            jQuery('input[name=nome]').val(json_obj.nome);
            jQuery('input[name=cognome]').val(json_obj.cognome);
            jQuery('input[name=phone]').val(json_obj.phone);
            jQuery('input[name=email]').val(json_obj.email);
            jQuery('input[name=indirizzo]').val(json_obj.indirizzo);
            jQuery('input[name=cap]').val(json_obj.cap);
            jQuery('select[name=nazione]').val(json_obj.nazione);
            jQuery('select[name=provincia]').val(json_obj.provincia);
            jQuery('select[name=tipo_sogetto]').val(json_obj.tipo_sogetto);
            jQuery('input[name=partita_iva]').val(json_obj.partita_iva);
            jQuery('input[name=ragione_sociale]').val(json_obj.ragione_sociale);
            jQuery('input[name=codice_fiscale]').val(json_obj.codice_fiscale);
            jQuery('input[name=email_pec]').val(json_obj.email_pec);
            jQuery('input[name=codice_univoco]').val(json_obj.codice_univoco);
            jQuery('input[name=cliente_id]').val(json_obj.user_id);
            } else {
            alert('Nessun utente registrato con questo indirizzo email');
            }
            /*$('span').html(output);  */
            },
            dataType: "json"//set to JSON
        })
    }

    jQuery(document).ready(function() {
    jQuery('input[name=cliente_prenotazione]').on('change', function() {
      //alert( this.value );
      var email = jQuery('input[name=email]').val()
      var cliente_prenotazione = jQuery("input[name=cliente_prenotazione]:checked").val();
      if (cliente_prenotazione == 2 && email) {
      SetUserData();
      } else {
            jQuery('input[name=nome]').val('');
            jQuery('input[name=cognome]').val('');
            jQuery('input[name=phone]').val('');
            jQuery('input[name=email]').val('');
            jQuery('input[name=indirizzo]').val('');
            jQuery('input[name=cap]').val('');
            jQuery('select[name=nazione]').val('');
            jQuery('select[name=provincia]').val('');
            jQuery('select[name=tipo_sogetto]').val('');
            jQuery('input[name=partita_iva]').val('');
            jQuery('input[name=ragione_sociale]').val('');
            jQuery('input[name=codice_fiscale]').val('');
            jQuery('input[name=email_pec]').val('');
            jQuery('input[name=codice_univoco]').val('');
            jQuery('input[name=cliente_id]').val('0');
      }
    });

    });

    jQuery(document).ready(function() {
    jQuery('input[name=email]').on('change', function() {
      //alert( this.value );
      var email = jQuery('input[name=email]').val()
      var cliente_prenotazione = jQuery("input[name=cliente_prenotazione]:checked").val();
      if (cliente_prenotazione == 2 && email) {
      SetUserData();
      } else {
            jQuery('input[name=nome]').val('');
            jQuery('input[name=cognome]').val('');
            jQuery('input[name=phone]').val('');
            //jQuery('input[name=email]').val('');
            jQuery('input[name=indirizzo]').val('');
            jQuery('input[name=cap]').val('');
            jQuery('select[name=nazione]').val('');
            jQuery('select[name=provincia]').val('');
            jQuery('select[name=citta]').val('');
            jQuery('select[name=tipo_sogetto]').val('');
            jQuery('input[name=partita_iva]').val('');
            jQuery('input[name=ragione_sociale]').val('');
            jQuery('input[name=codice_fiscale]').val('');
            jQuery('input[name=email_pec]').val('');
            jQuery('input[name=codice_univoco]').val('');
            jQuery('input[name=cliente_id]').val('0');
      }
    });

    });
    </script>
    <script type="text/javascript">

    jQuery.validator.addMethod("codfiscale", function(value) {
            var regex = /[A-Za-z]{6}[0-9lmnpqrstuvLMNPQRSTUV]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9lmnpqrstuvLMNPQRSTUV]{2}[A-Za-z]{1}[0-9lmnpqrstuvLMNPQRSTUV]{3}[A-Za-z]{1}/;
            return value. match(regex);
        }, "Codice fiscale non valido");

    jQuery.validator.addMethod("pIva",pIva);

    function pIva(pi, element) {
        if( pi == '' ) return true;
    	if( pi.length != 11 ) return false;
    	validi = "0123456789";
    	for( i = 0; i < 11; i++ ){if( validi.indexOf( pi.charAt(i) ) == -1 ) return false;}
    	s = 0;
    	for( i = 0; i <= 9; i += 2 )s += pi.charCodeAt(i) - '0'.charCodeAt(0);
    	for( i = 1; i <= 9; i += 2 ){
    		c = 2*( pi.charCodeAt(i) - '0'.charCodeAt(0) );
    		if( c > 9 )  c = c - 9;
    		s += c;
    	}
    	if( ( 10 - s%10 )%10 != pi.charCodeAt(10) - '0'.charCodeAt(0)) return false;
    	return true;
    }

    jQuery(document).ready(function() {
      jQuery("select[name='tipo_sogetto']").val('Privato');
      jQuery("#anagraficanc").show();
      jQuery("#ragionesocialeditta").hide();
      jQuery("#aziendapa").show();
      jQuery("label[for='partita_iva']").text("Partita Iva");
      jQuery('select[name=tipo_sogetto]').change(function() {
      var tipo_sogetto = jQuery("select[name='tipo_sogetto']").val();
      if (tipo_sogetto == 'Azienda' || tipo_sogetto == 'PA') {
      //jQuery("#anagraficanc").hide();
      jQuery("#ragionesocialeditta").show();
      jQuery("label[for='codice_fiscale']").text("Codice fiscale");
      jQuery("label[for='partita_iva']").text("Partita Iva *");
      jQuery("#aziendapa").show()
      } else {
      //jQuery("#anagraficanc").show();
      jQuery("#ragionesocialeditta").hide();
      jQuery("#aziendapa").hide()
      jQuery("label[for='partita_iva']").text("Partita Iva");
      jQuery("label[for='codice_fiscale']").text("Codice fiscale *");
        }
      });
    });

    </script>
    <script>
    jQuery(document).ready(function() {
      jQuery('input:radio[name=tipologia]').change(function() {
            var tipo_veicolo     = jQuery(this).val(); 
            var tipo_parcheggio  = jQuery('#park_type').val();
            var opt  = 'prenotazione';
            if ( tipo_veicolo) {
                // console.log(tipo_veicolo);
                        jQuery.ajax({
                            type: 'GET',
                            url: '<?php echo $gestionale_url ?>ajax_tipologia_front_search.php',
                            data: 'tipo_veicolo=' + tipo_veicolo + '&park_type=' + tipo_parcheggio  + '&opt=' + opt,
                            success: function(msg){
                              if(!jQuery.trim(msg)){
                              //jQuery('#park_type').html('Nessun servizio per questo periodo selezionato');
                              //alert('Codice convenzionato errato');
                              }else{
                              jQuery('#tipo_parcheggio').html(msg);
                              jQuery('#park_type').selectpicker('refresh');
                              //alert('Codice convenzionato errato');
                              }
                        }
                });
                } else {
                    //$('#conve').hide();
                }
    	  });
    });
    
</script>

    <?php
    if (is_page($pagina_ricerca_id)) {

        if (empty($_REQUEST['tipologia'])) {
        ?>
        <script>
        // jQuery(function() {
        //     var tipologiaradio = jQuery('input:radio[name=tipologia]');
        //     if(tipologiaradio.is(':checked') === false) {
        //         tipologiaradio.filter('[value=1]').prop('checked', true);
        //     }
        //     else{}
        // });
         </script>

    <?php } 
    $tipologia_veicolo = $_REQUEST['tipologia'];
    $park_type = $_REQUEST['park_type'];
    ?>
    <script type="text/javascript">
    jQuery("select[name='tipologia']").val(<?php echo $tipologia_veicolo ?>);
    jQuery('#park_type').val(<?php echo $park_type ?>);
    <?php } ?>
    </script>
    <?php
    if ($google_key) {
    ?>
    <script>
    </script>
    <?php } ?>
    <script>
    function VerificaCoupon()
    {
        var response = "";
        var form_data = {
            coupon_code: jQuery('input[name=coupon_code]').val(),
            is_ajax: 1
        };
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $gestionale_url ?>VerficaCoupon.php",
            data: form_data,
            success: function(response)
            {

            console.log(response);

            var json_obj = response;//parse JSON

            //alert(json_obj.controllo_posti.disponibili);


            if (json_obj.codice_valido != 1) {
            alert("Codice sconto non valido e/o scaduto");
            } else {
            }

            //jQuery('#disponibile').html(output);

                /*$('span').html(output);  */
            },
            dataType: "json"//set to JSON
        })
    }

    jQuery(document).ready(function() {
        jQuery('input[name=coupon_code]').on('change', function() {
        VerificaCoupon();
        });
    });

    jQuery(document).ready(function() {
        jQuery(".step2").hide();
        //jQuery(".step3").hide();
        /*jQuery(".bottone-prenota").hide();  */

        jQuery("#submit-continua").click(function(event){
            event.preventDefault();
            jQuery(".step1").hide();
            jQuery(".bottone-continua").hide();
            jQuery(".step2").show();
            jQuery(".bottone-prenota").show();
            //jQuery("#step2b").show();
            jQuery('html, body').animate({scrollTop: '0px'}, 1000);
          });

          /*jQuery("#submit-prenota").click(function(event){
            event.preventDefault();
            jQuery(".step2").hide();
            jQuery(".bottone-prenota").hide();
            jQuery(".step3").show();
            //jQuery("#step2b").show();
            jQuery('html, body').animate({scrollTop: '0px'}, 1000);
          });*/

          });
    </script>
    <script type="text/javascript">
        jQuery(document).ready(function($){
           $('input[name="tipologia"]').each(function(){
                if($(this).attr("checked")){
                    $(this).next().addClass('image-radio-checked');
                }else{
                    $(this).next().removeClass('image-radio-checked');
                }
            });
           $('input:radio[name="tipologia"]').change(function(){
                $('.image-radio').css('color','');
                $(this).next().css('color','#1a63a9');
            });
        });

   </script>

<?php
}

function get_times_cerco( $default = '19:00', $interval = '+15 minutes' ) {

    $output = '';

    $current = strtotime( '00:00' );
    $end = strtotime( '23:59' );

    while( $current <= $end ) {
        $time = date( 'H:i', $current );
        $sel = ( $time == $default ) ? ' selected' : '';

        $output .= "<option value=\"{$time}\"{$sel}>" . date( 'H:i', $current ) .'</option>';
        $current = strtotime( $interval, $current );
    }

    return $output;
}

function count_day_park( $date1, $date2 ) {

    $date1_in = date_create(date("Y-m-d", strtotime($date1)));
    $date2_out = date_create(date("Y-m-d", strtotime($date2)));

    //difference between two dates
    $diff = date_diff($date1_in,$date2_out);

    $day = $diff->format("%a");

    return $day;
}

public function get_type_parck($id) {
$url = "http://parkinlinate.netparking.it/api.php?API_KEY=SS0WKoaCxCIsRjjwiCrkTimPHECICN4ievlk9cDlNLflVv66gqZEz11uBMHKfHwZWYYqOdQQkoN9gsuX5eQKSG5Tbq5aUPF1ceNB&encode=json&command=SearchTypes";
$parck = json_decode(file_get_contents($url),true);
?>
<?php foreach ($parck as $key => $value) { ?>
<div class="radio">
  <label><input type="radio" name="tipo" value="<?php echo $value['id'] ?>" <?php if($id==$value['id']){ print ' checked="checked"'; }?>><?php echo $value['nome'] ?></label>
</div>
<?php
   }
 }

public function Conf_Active_Pacchetto() {

    return 0;
}

public function Conf_Active_List() {

    return 0;
}

public function ConvertiDataOra($data,$ora) {
    $output = date("Y-m-d H:i", strtotime($data.' ' .$ora));

	return $output;
}

public function ConteggioDifferenza($data1,$ora1,$data2,$ora2) {

    $sdate = new DateTime($this->ConvertiDataOra($data1,$ora1));
    $edate = new DateTime($this->ConvertiDataOra($data2,$ora2));
    $interval = $sdate->diff($edate);

	return array(
			"y"=>$interval->y,
            "m"=>$interval->m,
            "d"=>$interval->d,
            "h"=>$interval->h,
            "i"=>$interval->i,
            "s"=>$interval->s,
            "f"=>$interval->f,
            "weekday"=> $interval->weekday,
            "weekday_behavior"=> $interval->weekday_behavior,
            "first_last_day_of"=> $interval->first_last_day_of,
            "invert"=> $interval->invert,
            "days"=> $interval->days,
            "special_type"=> $interval->special_type,
            "special_amount"=> $interval->special_amount,
            "have_weekday_relative"=> $interval->have_weekday_relative,
            "have_special_relative"=> $interval->have_special_relative,
            "sdate"=> $this->ConvertiDataOra($data1,$ora1),
            "edate"=> $this->ConvertiDataOra($data2,$ora2),
  );
}

public function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


public function parcheggio_search_header($atts, $content = '', $tag){

    //build default arguments
    $atts = shortcode_atts(array(
        'id' => '',
        'css' => 'partner',
        'column' => '4',
        'columndate' => '4',
        'columntime' => '4',
        'columntipo' => '8',
        'offset' => '2',
        'button' => '8',
        'top' => '40',
        'buttoncss' => '#fff',
        'horizontal' => '0',
        'columcitta' => '10',
         )
    ,$atts,$tag);

    //uses the main output function of the location class
    $parcheggio_conf_options = get_option( 'parcheggio_conf_option_name' );
    $select_car = $parcheggio_conf_options['select_car_option_0'];
    $api_key = $parcheggio_conf_options['partner_key'];
    $partner_id = $parcheggio_conf_options['partner_id'];
    $pagina_ricerca_id = $parcheggio_conf_options['pagina_ricerca_id'];
    $gestionale_url = 'https://gestionale.parcheggioincloud.it/api.php';
    $url_veicolo = $gestionale_url.'?command=GetVeicoli';
    $tipo_veicolo = json_decode(file_get_contents($url_veicolo),true);
    $url_tipo = $gestionale_url.'?command=GetType';
    $tipo_parcheggio = json_decode(file_get_contents($url_tipo),true);
    $url_citta = $gestionale_url.'?command=GetCitta';
    $citta_parcheggio = json_decode(file_get_contents($url_citta),true);
    $html = '';
    if ($_REQUEST['data_inizio_date'] && $_REQUEST['data_inizio_time']) {
    $inizio_time = urldecode($_REQUEST['data_inizio_time']);
    $inizio_date = urldecode($_REQUEST['data_inizio_date']);
    }  else {
    $timestamp = strtotime(date( 'H' ).':00') + 60*60*3;
    $inizio_time = date('H:i', $timestamp);
    $inizio_date = date('d-m-Y');
    }

    if ($_REQUEST['data_fine_date'] && $_REQUEST['data_fine_time']) {
    $fine_time = urldecode($_REQUEST['data_fine_time']);
    $fine_date = urldecode($_REQUEST['data_fine_date']);
    } else {
    $timestamp1 = strtotime(date( 'H' ).':00') + 60*60*3;
    $fine_time = date('H:i', $timestamp1);
    $fine_date = date('d-m-Y', mktime(0,0,0,date('m'),date('d')+1,date('Y')));
    }
    ?>
<div class="bootstrap ricerca-<?php echo $atts['css'] ?>">
<div class="containers">
        <div class="row top<?php echo $atts['top'] ?>">

            <div class="col-md-12 col-md-offset-<?php echo $atts['offset'] ?>">
                <form role="form" id="formDestinationBooking" method="get" action="<?php echo esc_url( get_permalink($pagina_ricerca_id) ); ?>">
                    <input type="hidden" name="api_key" value="<?php echo $api_key ?>" id="api_key" />
                    <input type="hidden" name="partner_id" value="<?php echo $partner_id ?>" id="partner_id" />
                    <input type="hidden" name="ricerca_post" value="1" id="ricerca_post" />
                    <!--<legend class="text-center">Register</legend> -->

                    <fieldset>
                        <?php
                        if ($atts['horizontal'] == 1) {
                        ?>
                       <div class="row">
                        <?php } ?>
                        <div class="form-group col-md-<?php echo $atts['columndate'] ?>">
                            <label for="data-inizio-date">Arrivo in parcheggio</label>
                            <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></div>
                            <input id="data-inizio-date" class="form-control testo_form_input blu refill tooltip_onload" data-placement="top" type="text" name="data_inizio_date" value="<?php echo $inizio_date;?>" placeholder="Data ingresso" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group col-md-<?php echo $atts['columntime'] ?>">
                            <label for="data-inizio-time">Ora</label>
                            <select id="data-inizio-time" class="form-control testo_form_input orario_form_select blu refill selectpicker" name="data_inizio_time" autocomplete="off" data-size="5">
                            <option value="">Ingresso</option>
                            <?php echo $this->get_times_cerco($inizio_time, '+15 minutes'); ?>
                            </select>
                        </div>
                        <?php
                        if ($atts['horizontal'] == 1) {
                        ?>
                       </div>
                        <?php } ?>

                        <?php
                        if ($atts['horizontal'] == 1) {
                        ?>
                       <div class="row">
                        <?php } ?>
                        <div class="form-group col-md-<?php echo $atts['columndate'] ?>">
                            <label for="data-fine-date">Partenza dal parcheggio</label>
                            <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></div>
                            <input id="data-fine-date" class="form-control testo_form_input blu refill tooltip_onload" data-placement="bottom" type="text" name="data_fine_date" value="<?php echo  $fine_date;?>" placeholder="Data uscita" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group col-md-<?php echo $atts['columntime'] ?>">
                            <label for="confirm_password">Ora</label>
                            <select id="data-fine-time" class="form-control testo_form_input orario_form_select orario_form_noborder blu refill selectpicker" name="data_fine_time" autocomplete="off" data-size="5">
                            <option value="">Uscita</option>
                            <?php echo $this->get_times_cerco($fine_time,'+15 minutes'); ?>
                            </select>
                        </div>
                        <?php
                        if ($atts['horizontal'] == 1) {
                        ?>
                       </div>
                       <?php } ?>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    <div class="row">
                    <?php } ?>
                    <div class="form-group col-md-<?php echo $atts['columntipo'] ?>">
                            <label for="tipologia">Veicolo </label>
                            <select id="tipologia" class="form-control testo_form_input tipologia_select orario_form_noborder blu refill selectpicker" name="tipologia" autocomplete="off" data-size="5">
                            <?php foreach ($tipo_veicolo as $key => $row_veicolo) { ?>
                            <option value="<?php echo $row_veicolo['id']; ?>" <?php if($_REQUEST['veicolo']==$row_veicolo['id']){ print ' selected="selected"'; }?>><?php echo $row_veicolo['nome']; ?> </option>
                            <?php } ?>
                            </select>
                    </div>


                    <div class="form-group col-md-<?php echo $atts['columntipo'] ?>">
                            <label for="park_type">Tipo</label>
                            <div id="tipo_parcheggio">
                            <select id="park_type" class="form-control testo_form_input orario_form_select orario_form_noborder blu refill selectpicker" name="park_type" autocomplete="off">
                            <?php foreach ($tipo_parcheggio as $key => $row_tipi) { ?>
                            <option value="<?php echo $row_tipi['id']; ?>" <?php if ($_REQUEST['park_type']==$row_tipi['id']) { echo 'selected="selected"';} ?>><?php echo $row_tipi['nome']; ?></option>
                            <?php } ?>
                            </select>
                            </div>
                    </div>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    </div>
                    <?php } ?>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    <div class="row">
                    <?php } ?>
                    <div class="form-group col-md-<?php echo $atts['columcitta'] ?>">
                            <label for="citta">Città</label>
                            <select id="citta" class="form-control testo_form_input tipologia_select orario_form_noborder blu refill selectpicker" name="citta" autocomplete="off" data-size="5" data-live-search="true">
                            <option value=""></option>
                            <?php foreach ($citta_parcheggio as $key => $row_citta) { ?>
                            <option value="<?php echo $row_citta['citta']; ?>" <?php if($_REQUEST['citta']==$row_citta['citta']){ print ' selected="selected"'; }?>><?php echo ucfirst($row_citta['citta']); ?> </option>
                            <?php } ?>
                            </select>
                    </div>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    </div>
                    <?php } ?>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    <div class="row">
                    <?php } ?>
                    <div class="form-group col-md-<?php echo $atts['button'] ?>">
                    <label for="submit-search" style="color: <?php echo $atts['buttoncss'] ?>;"> &nbsp; </label>
                        <div class="bottone-cerca">
                                   <div class="col-xs-12 center_align_text botone-sl">
                                       <button class="btn btn-warning btn-block" id="submit-search"><?php _e('Cerca Parcheggio', 'cercoparcheggio'); ?></button>
                                   </div>
                                   <div class="col-xs-12 top_margin_15 hidden-lg hidden-md"></div>
                        </div>
                    </div>
                    <?php
                    if ($atts['horizontal'] == 1) {
                    ?>
                    </div>
                    <?php } ?>
                   </fieldset>
                </form>
            </div>

        </div>
    </div>
</div>

    <?php


    }

function net_get_image_id($image_url) {
    global $wpdb;
    $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
        return $attachment[0];
}

}

function footer_script () {
?>
    <script src="https://kit.fontawesome.com/59e1dde833.js" crossorigin="anonymous"></script>
<?php
}
add_filter( 'admin_footer', 'footer_script' );
add_filter( 'wp_footer', 'footer_script' );

include(plugin_dir_path( __FILE__ )."admin.php");
include(plugin_dir_path( __FILE__ )."ricerca-parcheggio.php");

$Partner_Parcheggio = new Partner_Parcheggio;


?>