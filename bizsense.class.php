<?php

class Bizsense {

  public  $debug                           = false;
  private $pp_biz_id                       = 0;
  private $pp_user_guid                    = '';
  private $pp_client_name                  = '';
  private $pp_email                        = - 1;
  private $pp_phone                        = - 1;
  private $pp_cell                         = - 1;
  private $pp_address                      = '';
  private $pp_total_with_vat               = 0;
  private $pp_doc_name                     = 'Default Name';
  private $pp_credit_terms_reg             = 1;
  private $pp_credit_terms_payments        = 0;
  private $pp_credit_terms_credit_payments = 0;
  private $pp_items                        = '';
  private $pp_url_success                  = '';
  private $pp_url_fail                     = '';
  private $document_type_id                = 3;
  private $pp_send_doc_by_mail             = 1;
  private $pp_tz_ahot                      = '';
  private $outform_vars                    = [
      'pp_biz_id',
      'pp_user_guid',
      'pp_client_name',
      'pp_email',
      'pp_phone',
      'pp_cell',
      'pp_address',
      'pp_total_with_vat',
      'pp_doc_name',
      'pp_credit_terms_reg',
      'pp_credit_terms_payments',
      'pp_credit_terms_credit_payments',
      'pp_items',
      'pp_url_success',
      'pp_url_fail',
      'document_type_id',
  ];
  private $items                           = [];
  private $itemsSeperator                  = '¿';
  private $itemPropSeperator               = 'º';
  private $action_url                      = 'https://biznese.co.il/Default.aspx';
  private $tax                             = 0.17;

  public
  function __construct() {

  }

  public
  function set_client_name( $name ) {
    $this -> pp_client_name = $name;
  }

  public
  function get_client_name() {
    return $this -> pp_client_name;
  }

  public
  function set_client_email( $email ) {
    if( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
      trigger_error ( "Invalid email format" ,E_USER_NOTICE );
    }
    $this -> pp_email = $email;
  }

  public
  function get_client_email() {
    return $this -> pp_email;
  }

  public
  function set_fail_url( $url ) {
    if( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
      trigger_error ( "Invalid url format" ,E_USER_NOTICE );
    }
    $this -> pp_url_fail = $url;
  }
  public
  function get_fail_url() {
    return $this -> pp_url_fail;
  }

  public
  function set_success_url( $url ) {
    if( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
      trigger_error ( "Invalid url format" ,E_USER_NOTICE );
    }
    $this -> pp_url_success = $url;
  }
  public
  function get_success_url() {
    return $this -> pp_url_success;
  }

  public
  function add_item( $id = - 1, $name, $price, $quantity = 1, $vat_prec = null, $vat = null, $total = null, $price_with_vat = null ) {
    if( empty( $id ) ) {
      $id = - 1;
    }
    if( empty( $vat_prec ) ) {
      $vat_prec = $this -> tax * 100;
    }
    if( empty( $vat ) ) {
      $vat = $vat_prec;
    }
    if( empty( $total ) ) {
      $price = $price / ( $this -> tax + 1 );
      $total = ( $price * $quantity ) + ( ( $price * $quantity ) * ( $vat_prec / 100 ) );
    }
    if( empty( $price_with_vat ) ) {
      $price_with_vat = $total / $quantity;
    }

    $this -> items[] = [
        $id,
        $name,
        $price,
        $vat_prec,
        $vat,
        $total,
        $quantity,
        $price_with_vat
    ];

    $this -> convert_items_format();

    $this -> pp_total_with_vat += $total;
  }

  private
  function convert_items_format() {
    $format = [];
    foreach ( $this -> items as $item ) {
      $format[] = implode( $this -> itemPropSeperator, $item );
    }

    $format           = implode( $this -> itemsSeperator, $format );
    $this -> pp_items = $format;
  }

  public
  function go_to_payment( $success_url = null, $fail_url = null ) {
    if( ! empty( $success_url ) ) {
      $this -> pp_url_success = $success_url;
    }
    if( ! empty( $fail_url ) ) {
      $this -> pp_url_fail = $fail_url;
    }

    $inputType = $this -> debug ? 'text' : 'hidden';


    // Create form settings
    $output = '<form target="_blank" action="' . $this -> action_url . '" method="post" name="bizsenseForm" id="bizsenseForm">';

    // Add all inputs to form
    $vars = get_object_vars( $this );
    foreach ( $this -> outform_vars as $key ) {
      if( isset( $vars[ $key ] ) ) {
        $output .= '<input title="' . $key . '" type="' . $inputType . '" name="' . $key . '" value="' . $vars[ $key ] . '" />';
      }
    }

    // Add auto submit with js
    if( $this -> debug ) {
      $output .= '<input type="submit" />';
    } else {
      $output .= '
      <script type="text/javascript">
        window.onload = function(){
          document.forms["bizsenseForm"].submit();
        }
      </script>
    ';
    }

    $output .= '</form>';

    echo $output;
  }
}
