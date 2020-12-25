//Scripts for Blog App
$(function() {
	$(".info-sdb__list_cal").on("click", ".info-sdb__item-l", function(e) {
		e.preventDefault();

		var $this = $(this),
			currentItem = $this.closest(".info-sdb__item"),
			currentMenu = currentItem.find(".info-sdb__list"),
			siblingItems = currentItem.siblings(),
			siblingMenus = siblingItems.find(".info-sdb__list");

		currentMenu.slideToggle(function() {
			if (currentItem.hasClass("is-active")) {
				currentItem.removeClass("is-active");
			} else {
				currentItem.addClass("is-active");
			}
		});

		siblingItems.removeClass("is-active");
		siblingMenus.slideUp();
	});
});
