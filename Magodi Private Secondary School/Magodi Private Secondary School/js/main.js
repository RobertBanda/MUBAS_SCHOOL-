$(document).ready(function() {
	// Initialize gallery slider with enhanced options
	var gallerySlider = $('.slider-con .bxslider').bxSlider({
		mode: 'horizontal',
		speed: 600,
		auto: false,
		pager: true,
		controls: false,
		slideMargin: 0,
		minSlides: 1,
		maxSlides: 1,
		moveSlides: 1,
		responsive: true,
		touchEnabled: true,
		preloadImages: 'all',
		onSliderLoad: function() {
			// Ensure images are visible after slider loads
			$('.slider-con .slide li img').css('opacity', '1');
		}
	});
	
	// Initialize other sliders (homepage slider)
	$('.slider .bxslider').bxSlider();
	
	// Latest news: equalize article heights for a balanced, dynamic layout
	function equalizeNewsHeights() {
		var $news = $('.news article');
		if ($news.length === 0) return;
		$news.css('min-height', '');
		if ($(window).width() > 768) {
			var maxH = 0;
			$news.each(function() {
				var h = $(this).outerHeight();
				if (h > maxH) maxH = h;
			});
			$news.css('min-height', maxH + 'px');
		}
	}
	equalizeNewsHeights();
	$(window).on('resize', function() {
		equalizeNewsHeights();
	});
	
	// Smooth scroll to anchor links with header offset
	$('a[href^="#event-"]').on('click', function(e) {
		var target = $(this.getAttribute('href'));
		if (target.length) {
			e.preventDefault();
			var headerHeight = $('#header').outerHeight() || 100;
			$('html, body').animate({
				scrollTop: target.offset().top - headerHeight - 20
			}, 600);
			
			// Remove highlight after 3 seconds
			setTimeout(function() {
				target.css({
					'background-color': '',
					'padding': '',
					'margin-left': '',
					'margin-right': '',
					'box-shadow': ''
				});
			}, 3000);
		}
	});
	
	// Handle anchor links on page load (if URL has hash)
	if (window.location.hash) {
		var target = $(window.location.hash);
		if (target.length) {
			setTimeout(function() {
				var headerHeight = $('#header').outerHeight() || 100;
				$('html, body').animate({
					scrollTop: target.offset().top - headerHeight - 20
				}, 600);
			}, 100);
		}
	}
	
	$(".menu-trigger").click(function() {
		$("#menu").fadeToggle(300);
		$(this).toggleClass("active")
	});
	
	$('.info-request, .get-contact').fancybox();
	
	// Initialize fancybox for gallery images (lightbox)
	$('.slider-con .slide li a').fancybox({
		helpers: {
			title: {
				type: 'inside'
			},
			overlay: {
				opacity: 0.9,
				css: {
					'background-color': '#000'
				}
			}
		},
		padding: 0,
		margin: 20,
		openEffect: 'fade',
		closeEffect: 'fade',
		nextEffect: 'fade',
		prevEffect: 'fade',
		openSpeed: 300,
		closeSpeed: 300
	});
	
	// Initialize fancybox for sidebar gallery images
	$('#sidebar .widget.list li a').fancybox({
		helpers: {
			title: {
				type: 'inside'
			},
			overlay: {
				opacity: 0.9,
				css: {
					'background-color': '#000'
				}
			}
		},
		padding: 0,
		margin: 20,
		openEffect: 'fade',
		closeEffect: 'fade',
		nextEffect: 'fade',
		prevEffect: 'fade',
		openSpeed: 300,
		closeSpeed: 300
	});
	
	// Ensure sidebar gallery images load properly with staggered animation
	$('#sidebar .list li img').on('load', function() {
		var $img = $(this);
		var $li = $img.closest('li');
		var index = $li.index();
		
		setTimeout(function() {
			$img.css({
				'opacity': '1',
				'transform': 'scale(1)'
			});
			$li.addClass('loaded');
		}, index * 50); // Stagger animation
	}).each(function() {
		if (this.complete) {
			$(this).trigger('load');
		}
	});
	
	// Add click ripple effect to gallery images
	$('#sidebar .list li a').on('click', function(e) {
		var $link = $(this);
		var $ripple = $('<span class="ripple"></span>');
		var rect = this.getBoundingClientRect();
		var size = Math.max(rect.width, rect.height);
		var x = e.clientX - rect.left - size / 2;
		var y = e.clientY - rect.top - size / 2;
		
		$ripple.css({
			width: size + 'px',
			height: size + 'px',
			left: x + 'px',
			top: y + 'px'
		});
		
		$link.append($ripple);
		
		setTimeout(function() {
			$ripple.remove();
		}, 600);
	});
	
	// Add keyboard navigation support for gallery
	$('#sidebar .list li a').on('keydown', function(e) {
		if (e.key === 'Enter' || e.key === ' ') {
			e.preventDefault();
			$(this).click();
		}
	});
	
	// Ensure gallery images load properly
	$('.slider-con .slide li img').on('load', function() {
		$(this).css('opacity', '1');
	}).each(function() {
		if (this.complete) {
			$(this).trigger('load');
		}
	});
	
	// Add loading state management
	function initGalleryImages() {
		$('.slider-con .slide li img').each(function() {
			var $img = $(this);
			if (!$img.attr('src')) {
				$img.attr('src', $img.data('src') || '');
			}
		});
	}
	initGalleryImages();
	
	$("select").crfs(); 
	
	
	$(".table td").mouseenter(function(){    
        $(this).find(".holder").stop(true, true).fadeIn(600);
        $(this).find(">div").addClass('hover');
        return false;
     });
      $('.table td').mouseleave(function(){  
        $(this).find(".holder").stop(true, true).fadeOut(400);
        $(this).find(">div").removeClass('hover');
        return false;
     });
	$(".table td .holder").click(function() {
        $(this).stop(true, true).fadeOut(400);
        $(this).parent().parent().removeClass('hover');
        return false;
	});
	
	var isBrowserOs = {
	    Windows: function() {
	        return navigator.userAgent.match(/Win/i);
	    },
	    MacOS: function() {
	        return navigator.userAgent.match(/Mac/i);
	    },
	    UNIX: function() {
	        return navigator.userAgent.match(/X11/i);
	    },
	    Linux: function() {
	        return navigator.userAgent.match(/Linux/i);
	    },
	    iOs: function() {
	        return navigator.userAgent.match(/(iPad|iPhone|iPod)/i);
	    },
	    Android: function() {
	        return navigator.userAgent.match(/android/i);
	    },
	    BlackBerry: function() {
	        return navigator.userAgent.match(/BlackBerry/i);
	    },
	    Chrome: function() {
	        return window.chrome;
	    },
	    Firefox: function() {
	        return navigator.userAgent.match(/Firefox/i);
	    },
	    IE: function() {
	        return navigator.userAgent.match(/MSIE/i);
	    },
	    Opera: function() {
	        return (!!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0);
	    },
	    SeaMonkey: function() {
	        return navigator.userAgent.match(/SeaMonkey/i);
	    },
	    Camino: function() {
	        return navigator.userAgent.match(/Camino/i);
	    },
	    Safari: function() {
	        return (Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0);
	    }
	};
	 
	var html_class = '';
	//OS
	if(isBrowserOs.Windows())
		html_class = 'win';
	if(isBrowserOs.UNIX())
		html_class = 'unix';
	if(isBrowserOs.MacOS())
		html_class = 'mac';
	if(isBrowserOs.Linux())
		html_class = 'linux';
	if(isBrowserOs.iOs())
		html_class = 'ios mac';
	if(isBrowserOs.Android())
		html_class = 'android';
	if(isBrowserOs.BlackBerry())
		html_class = 'blackberry';
	 
	//Browser
	if(isBrowserOs.Chrome())
		html_class = html_class + ' chrome';
	if(isBrowserOs.Firefox())
		html_class = html_class + ' firefox';
	if(isBrowserOs.IE())
		html_class = html_class + ' ie';
	if(isBrowserOs.Opera())
		html_class = html_class + ' opera';
	if(isBrowserOs.SeaMonkey())
		html_class = html_class + ' seamonkey';
	if(isBrowserOs.Camino())
		html_class = html_class + ' camino';
	if(isBrowserOs.Safari())
		html_class = html_class + ' safari';
	 
	$("html").addClass(html_class);
	 
});

