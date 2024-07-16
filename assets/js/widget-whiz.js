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
		var name = $row.find('input[name$="[name]"]').val();

		var confirmation = prompt(
			"Type the name of the sidebar (" + name + ") to confirm deletion:"
		);
		if (confirmation === name) {
			$.ajax({
				url: WidgetWhiz.ajax_url,
				method: "POST",
				data: {
					action: "delete_sidebar",
					nonce: WidgetWhiz.nonce,
					key: key,
					name: name,
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
		} else {
			alert("Sidebar name does not match. Deletion cancelled.");
		}
	});

	$(".widget-whiz-reactivate-button").on("click", function () {
		var name = $(this).data("name");

		$.ajax({
			url: WidgetWhiz.ajax_url,
			method: "POST",
			data: {
				action: "reactivate_sidebar",
				nonce: WidgetWhiz.nonce,
				name: name,
			},
			success: function (response) {
				if (response.success) {
					$("#widget-whiz-sidebars-list").html(
						response.data.sidebars_html
					);
					location.reload();
				} else {
					alert("Failed to reactivate sidebar.");
				}
			},
		});
	});
});
