jQuery(document).ready(function ($) {
	$("#widget-whiz-import-button").on("click", function () {
		$.ajax({
			url: WidgetWhiz.ajax_url,
			method: "POST",
			data: {
				action: "import_sidebars",
				nonce: WidgetWhiz.nonce,
			},
			success: function (response) {
				if (response.success) {
					location.reload();
				} else {
					alert("Failed to import sidebars.");
				}
			},
		});
	});

	$(".widget-whiz-delete-button").on("click", function () {
		var $row = $(this).closest("tr");
		var key = $row.data("key");

		$.ajax({
			url: WidgetWhiz.ajax_url,
			method: "POST",
			data: {
				action: "delete_sidebar",
				nonce: WidgetWhiz.nonce,
				key: key,
			},
			success: function (response) {
				if (response.success) {
					$row.remove();
					location.reload();
				} else {
					alert("Failed to delete sidebar.");
				}
			},
		});
	});
});
