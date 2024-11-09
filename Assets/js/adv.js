$(document).ready(function() {            
	createAdIframe();
});

function createAdIframe() {		  
	const container = $('<div>').css({
	position: 'fixed',
	bottom: 0,
	left: 0,
	width: '100%',
	backgroundColor: '#f0f0f0',
	borderTop: '1px solid #ccc',
	transition: 'height 0.3s ease-in-out'
	});

	const iframe = $('<iframe>').attr('src', 'https://gamefam.org')
	.css({
	  width: '100%',
	  height: '100%',
	  border: 'none'
	});

	const toggleButton = $('<button>').addClass('btn btn-primary')
	.css({
	  position: 'absolute',
	  top: '-40px',
	  left: '10px',
	  padding: '5px 10px'
	})
	.html('<i class="fas fa-chevron-down"></i>');

	container.append(iframe).append(toggleButton);

	$('body').append(container);

	let isExpanded = true;
	const expandedHeight = '300px';
	const collapsedHeight = '30px';

	container.css('height', expandedHeight);

	toggleButton.on('click', function() {
	if (isExpanded) {
	  container.animate({height: collapsedHeight}, 300);
	  $(this).html('<i class="fas fa-chevron-up"></i>');
	} else {
	  container.animate({height: expandedHeight}, 300);
	  $(this).html('<i class="fas fa-chevron-down"></i>');
	}
	isExpanded = !isExpanded;
	});
}

