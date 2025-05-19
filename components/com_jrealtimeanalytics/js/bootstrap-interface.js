/**
 * Custom code for common frontend interface
 */
if(typeof Joomla !== 'object') {
	Joomla = {};
}
Joomla.tableOrdering = function(order, dir, task, form) {
	if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');
	}

	form.filter_order.value = order;
	form.filter_order_Dir.value = dir;
	Joomla.submitform(task, form);
}

jQuery(function($){
	$('div.accordion-toggle').on('click', function (jqEvent) {
		jqEvent.stopPropagation();
		return false;
	});
})

/**
 * Generic submit form
 */
jQuery.submitform = Joomla.submitform = function(task, form) {
	if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');
	}

	if (typeof(task) !== 'undefined' && task !== "") {
		form.task.value = task;
	}

	// Submit the form.
	if (typeof form.onsubmit == 'function') {
		form.onsubmit();
	}
	if (typeof form.fireEvent == "function") {
		form.fireEvent('submit');
	}
	form.submit();
};

/**
 * Default function. Usually would be overriden by the component
 */
jQuery.submitbutton = function(pressbutton) {
	jQuery.submitform(pressbutton);
}

jQuery(function($){
	// Reset badge classes to its own BS3
	$('form.jes.jesform *.bg-primary').removeClass('bg-primary').addClass('badge-primary');
	
	if($('.pagination #limit, .list-footer #limit').length) {
		$('.toppagination').remove();
	}
	
	// Bind waiter events
	$('#ga-dash button, *.waiter').on('click', function(jqEvent){
		// Get div popover container width to center waiter
		$('body').prepend('<img/>').children('img').attr('src', jrealtimeBaseURI + 'administrator/components/com_jrealtimeanalytics/images/loading.gif').css({
			'position' : 'absolute',
			'left' : '50%',
			'top' : '50%',
			'margin-left' : '-64px',
			'width' : '128px',
			'z-index' : '99999'
		});
	});
	
	// CSS Grid patch for table having exceeding width
	var gridContainer = $('div.container-component');
	if(gridContainer.length && parseFloat($(window).width()) > 767.98) {
		var finalGridWidth = gridContainer.width();
		$('form.jesform').hide();
		var initialGridWidth = gridContainer.width();
		if(finalGridWidth > initialGridWidth) {
			gridContainer.css('max-width', initialGridWidth + 'px');
		}
		$('form.jesform').show();
	}
	
	// Perform columns ordering
	$('a[data-ordering-form]').on('click', function(jqEvent){
		let orderingOrder = $(jqEvent.target).attr('data-ordering-order');
		let orderingDirection = $(jqEvent.target).attr('data-ordering-direction');
		let orderingTask = $(jqEvent.target).attr('data-ordering-task');
		Joomla.tableOrdering(orderingOrder, orderingDirection, orderingTask);
		return false;
	});
});
