(function ($) {
  "use strict";

  $(document).ready(function () {
    var $product_screen = $(".edit-php.post-type-product");
    var $title_action = $product_screen.find(".page-title-action:last");
    var $blank_action = $product_screen.find(
      ".woocommerce-BlankState-cta :last"
    );
    var $blankslate = $product_screen.find(".woocommerce-BlankState");

    var buttonName = "Import Flora Products";
    var updatebutton = "Check Flora updates";

    if (0 === $blankslate.length) {
      $title_action.after(
        '<input type="button" id="full-import" class="page-title-action" value="' +
          buttonName +
          '"/><div class="spinner" style="float:initial; visibility: visible; display: none;"></div>'
      );
      $title_action.after(
        '<input type="button" id="update-flora" class="page-title-action" value="' +
          updatebutton +
          '"/>'
      );
      $("#full-import").after(
        '<div id="show-flora-progress" class="flora-progress" style="float:initial; visibility: visible; display: none;"></div>'
      );
    } else {
      $title_action.hide();
      $(".woocommerce-BlankState-cta:last").after(
        '<input type="button" id="full-import" class="woocommerce-BlankState-cta button flora-blank-slate" value="Import products from Flora@home"/><div class="spinner" style="float:initial; visibility: visible; display: none;"></div>'
      );
    }

    $("#full-import").click(function () {
      $(this).attr("disabled", true);
      $(this).addClass("disabled");
      var data = {
        action: "flora_ajaximport",
      };
      $(".spinner").show();
      $.post(ajaxurl, data, function (response) {
        $(".spinner").hide();
        if (location.href.indexOf("?") === -1) {
          window.location = location.href += "?flora-import=success";
        } else {
          window.location = location.href += "&flora-import=success";
        }
        window.location.load();
      });
    });

    $("#update-flora").click(function () {
      $(this).attr("disabled", true);
      $(this).addClass("disabled");
      $(".spinner").show();
      var data = {
        action: "flora_ajaxupdate",
      };
      $(".spinner").show();
      $.post(ajaxurl, data, function (response) {
        loading: true, $(".spinner").hide();
        location.reload();
      });
    });
  });
})(jQuery);
