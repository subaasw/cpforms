"use strict";

(function ($) {
  $(".cpforms-form").on("submit", function (e) {
    e.preventDefault();

    const formData = {};

    for (const { name, value } of $(this).serializeArray()) {
      formData[name] = value;
    }

    $.ajax({
      url: cpformsApi.rest_url + "/submit",
      type: "POST",
      data: JSON.stringify(formData),
      contentType: "application/json; charset=utf-8",
      headers: {
        Accept: "application/json",
        "X-CP-Nonce": cpformsApi.nonce,
      },
      dataType: "json",
      success: function () {
        alert("Added Successfully!");
      },
      error: function (xhr) {
        alert(xhr.responseJSON?.message);
      },
    });
  });
})(jQuery);

// For listing and shorting
jQuery(document).ready(function ($) {
  function serverCall(formData) {
    $.ajax({
      url: cpformsApi.rest_url + "/data",
      type: "GET",
      data: formData,
      headers: {
        "X-CP-Nonce": cpformsApi.nonce,
      },
      success: function ({ data, per_page, page, size }) {
        if (Array.isArray(data) && data.length > 0) {
          let table_body = "";
          let index = page === 1 ? 0 : (page - 1) * per_page;

          for (const { id, full_name, email, message } of data) {
            index++;
            table_body += `
              <tr class="col-${id}">
                <td>${index}</td>
                <td>${full_name}</td>
                <td>${email}</td>
                <td>${message}</td>
              </tr>
            `;
          }

          $(".cpforms-list-body").html(table_body);

          $(".cpforms-nav-btns button").each(function () {
            if ($(this).hasClass("prev-btn")) {
              const hasPrevPage = page > 1;
              $(this).prop("disabled", !hasPrevPage);

              if (hasPrevPage) {
                $(this).attr("data-attr", page - 1);
              }
            }

            if ($(this).hasClass("next-btn")) {
              const hasNextpage = size / (page * per_page) > 1;
              $(this).prop("disabled", !hasNextpage);
              console.log("next btn", hasNextpage);

              if (hasNextpage) {
                $(this).attr("data-attr", Number(page) + 1);
              }
            }
          });
        } else {
          $(".cpforms-table-list").html(
            '<h3 align="center">Sorry No Data Found!</h3>'
          );
        }
      },
      error: function (xhr) {
        console.warn("Error: ", xhr.responseJSON);
      },
    });
  }

  $(".cpforms-list-filter").on("submit", function (e) {
    e.preventDefault();

    const formData = {};

    for (const { name, value } of $(this).serializeArray()) {
      if (value) {
        formData[name] = value;
      }
    }
    serverCall(formData);
  });

  $(".cpforms-nav-btns button").on("click", function () {
    let request_page = $(this).attr("data-attr");

    let formData = {};

    for (const { name, value } of $(".cpforms-list-filter").serializeArray()) {
      if (value) {
        formData[name] = value;
      }
    }

    serverCall({ ...formData, page: request_page });
  });
});
