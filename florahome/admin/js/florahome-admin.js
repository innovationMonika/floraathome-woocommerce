(function ($) {
  "use strict";

  function pricebox(sel) {
    if (sel.value == "disable") {
      $("input[name=fah_settings[fah_text_publish_price_value]]").prop(
        "disabled",
        true
      );
      alert(sel.value);
    }
  }
  $(document).ready(function () {
    var $product_screen = $(".edit-php.post-type-product");
    var $title_action = $product_screen.find(".page-title-action:last");
    var $blankslate = $product_screen.find(".woocommerce-BlankState");

    var buttonName = "Import Flora Products";

    if (0 === $blankslate.length) {
      $title_action.after(
        '<a href="' +
          woocommerce_admin.urls.export_products +
          '" class="page-title-action">' +
          buttonName +
          "</a>"
      );
    } else {
      $title_action.hide();
    }
  });
})(jQuery);
