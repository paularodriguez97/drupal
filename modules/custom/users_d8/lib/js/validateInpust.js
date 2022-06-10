(function ($, Drupal, drupalSettings) {
  "use strict";
  "use strict";
  const elements = {
    element_1: '#users-d8-register'
  }

  $.validator.addMethod("formAlphanumeric", function (value, element) {
    var pattern = /^[a-zA-Z, ]*$/;
    return this.optional(element) || pattern.test(value);
  }, "El campo debe tener un valor alfanumérico (azAZ09)");


  $().ready(function() {
    $(elements.element_1).validate({
      rules: {
        name: { required: true, minlength: 5, formAlphanumeric: true},
      },
      messages: {
        name: "El campo es obligatorio y solo se permite letras.",
      }
    });
  });
}(jQuery, Drupal, drupalSettings));
