jQuery( function ( $ ) {
  init_mpay_meta();
  $(".mpay_customize_mpay_donations_field input:radio").on("change", function() {
    init_mpay_meta();
  });

  function init_mpay_meta(){
    if ("enabled" === $(".mpay_customize_mpay_donations_field input:radio:checked").val()){
      $(".mpay_merchant_id_field").show();
      $(".mpay_api_key_field").show();
      $(".mpay_description_field").show();
      $(".mpay_collect_billing_field").show();
    } else {
      $(".mpay_merchant_id_field").hide();
      $(".mpay_api_key_field").hide();
      $(".mpay_description_field").hide();
      $(".mpay_collect_billing_field").hide();
    }
  }
});