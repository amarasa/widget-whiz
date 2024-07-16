jQuery(document).ready(function ($) {
	$(document).on("click", ".widget-whiz-delete-button", function () {
		var $row = $(this).closest("tr");
		var key = $row.data("key");

		if (confirm("Are you sure you want to delete this sidebar?")) {
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
					} else {
						alert("Failed to delete sidebar.");
					}
				},
			});
		}
	});
});
