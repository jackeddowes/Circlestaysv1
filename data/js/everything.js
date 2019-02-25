/**
 * @package    WordPress
 * @subpackage Everything
 */

'use strict';

//Imports
(function(base_url) {

	document.write('<script src="' + base_url + 'data/js/_imagesloaded.pkgd.js"></script>');
	document.write('<script src="' + base_url + 'data/js/_isotope.pkgd.js"></script>');
	document.write('<script src="' + base_url + 'data/js/_jquery.fancybox.js"></script>');
	document.write('<script src="' + base_url + 'data/js/_jquery.tipsy.js"></script>');
	document.write('<script src="' + base_url + 'data/js/_slippry.js"></script>');

})(((document.currentScript || {src: ''}).src.match(/^.+\/wp-content\/themes\/.+?\//i) || [''])[0]);

(function($) {

	/**
	 * Array index of
	 */
	if (!Array.prototype.indexOf) {
		Array.prototype.indexOf = function(elt /*, from*/) {
			var len = this.length >>> 0;
			var from = Number(arguments[1]) || 0;
			from = (from < 0) ? Math.ceil(from) : Math.floor(from);
			if (from < 0) {
				from += len;
			}
			for (; from < len; from++) {
				if (from in this && this[from] === elt) {
					return from;
				}
			}
			return -1;
		};
	}

	/**
	 * Array filter
	 */
	if (!Array.prototype.filter) {
		Array.prototype.filter = function(fun /*, thisp */) {
			if (this === void 0 || this === null) {
				throw new TypeError();
			}
			var t = Object(this);
			var len = t.length >>> 0;
			if (typeof fun !== 'function') {
				throw new TypeError();
			}
			var res = [];
			var thisp = arguments[1];
			for (var i = 0; i < len; i++) {
				if (i in t) {
					var val = t[i];
					if (fun.call(thisp, val, i, t)) {
						res.push(val);
					}
				}
			}
			return res;
		};
	}

	/**
	 * Array unique
	 */
	$.arrayUnique = function(haystack) {
		return haystack.filter(function(value, index, haystack) {
			return haystack.indexOf(value, index + 1) < 0;
		});
	};

	/**
	 * Get data
	 */
	$.fn.getData = function(key, defaultValue) {
		return typeof this.data(key) !== 'undefined' ? this.data(key) : defaultValue;
	};

	/**
	 * Discard white space
	 */
	$.fn.discardWhiteSpace = function() {
		return this.each(function() {
			$(this).contents().filter(function() {
				return this.nodeType === 3;
			}).remove();
		});
	};

	/**
	 * Movable container
	 */
	$.fn.movableContainer = function(forceTouchDevice) {

		// Touch device
		var touchDevice = ('ontouchstart' in document.documentElement) || (typeof window.navigator.msPointerEnabled != 'undefined');
		if (typeof forceTouchDevice != 'undefined') {
			touchDevice = touchDevice || forceTouchDevice;
		}

		// Movable container
		return this.removeClass('movable-container').each(function() {

			// Original margins
			var margins = {
				marginTop:    $(this).css('margin-top'),
				marginBottom: $(this).css('margin-bottom')
			};

			// Wrapping
			var content = $(this).addClass('movable-container-content').wrap('<div class="movable-container" />');
			var mc      = content.parent().css(margins);

			// Max left position
			var maxLeft = function() {
				return mc.width() - content.width() - (touchDevice ? nav.outerWidth(true) : 0);
			};

			// Touchable device
			if (touchDevice) {

				var nav = $('<div />', {'class': 'movable-container-nav'})
					.append('<a class="button small prev"><i class="icon-fast-backward"></i></a>')
					.append('<a class="button small next"><i class="icon-fast-forward"></i></a>')
					.appendTo(mc);

				var buttons = $('.button', nav).click(function() {

					// Disabled
					if ($(this).is('.disabled')) {
						return;
					}

					// Position
					var s = ($(this).index() == 0 ? 1 : -1) * Math.round((mc.width()-nav.outerWidth(true))*0.9);
					var x = Math.max(Math.min(content.position().left + s, 0), maxLeft());

					// Buttons
					buttons.eq(0).toggleClass('disabled', x == 0);
					buttons.eq(1).toggleClass('disabled', x == maxLeft());

					// Content animation
					content.stop(true).animate({left: x}, 400);

				});
				buttons.eq(0).addClass('disabled');

			}

			// Non-touchable device
			else {
				$(mc)
					.mousemove(function(event) {
						var f = Math.min(Math.max((event.pageX-mc.offset().left-20) / (mc.width()-40), 0), 1);
						var x = Math.round((mc.width() - content.width()) * f);
						content.stop(true).css('left', x);
					})
					.mouseleave(function() {
						content.stop(true).animate({left: '+=0'}, 1600).animate({left: 0}, 400);
					});
			}

			// Resize event
			var on_resize = function() {
				content.css('left', Math.max(content.position().left, maxLeft()));
				if (touchDevice) {
					if (content.width() > mc.width()) {
						nav.show();
						buttons.eq(0).toggleClass('disabled', content.position().left == 0);
						buttons.eq(1).toggleClass('disabled', content.position().left == maxLeft());
					} else {
						nav.hide();
						content.css('left', 0);
					}
				}
			};
			$(window).on('resizeend', on_resize); on_resize();

			content.imagesLoaded(on_resize);

		});

	};

	/**
	 * Scroller
	 */
	$.fn.scroller = function(counter) {

		if (typeof counter == 'undefined') {
			counter = true;
		}

		this.filter('ul').removeClass('scroller').filter(':has(li)').each(function() {

			// Original margins
			var margins = {
				marginTop:    $(this).css('margin-top'),
				marginBottom: $(this).css('margin-bottom')
			};

			// Wrapping
			var content  = $(this).addClass('scroller-content').wrap('<div class="scroller" />');
			var items    = $('> li', content);
			var scroller = content.parent().css(margins);

			// Content & items
			content.css('width', (items.length*100)+'%');
			items.eq(0).addClass('active');

			// Navigation
			var nav = $('<div />', {'class': 'scroller-nav'})
				.append('<a class="button small prev"><i class="icon-left-open"></i></a>')
				.append('<a class="button small next"><i class="icon-right-open"></i></a>')
				.appendTo(scroller);
			if (counter) {
				nav.append('<small>1/'+items.length+'</small>');
			}

			var buttons = $('.button', nav).click(function() {

				// Disabled
				if ($(this).is('.disabled')) {
					return;
				}

				// Active & next item
				var active = items.filter('.active');
				var next   = items.eq(Math.min(Math.max(active.index() + ($(this).index() == 0 ? -1 : 1), 0), items.length-1));

				active.removeClass('active');
				next.addClass('active');

				// Buttons
				buttons.eq(0).toggleClass('disabled', next.index() == 0);
				buttons.eq(1).toggleClass('disabled', next.index() == items.length-1);

				// Counter
				if (counter) {
					$('small', nav).text((next.index()+1)+'/'+items.length);
				}

				// Content scroll
				content.css({
					left:   -next.position().left,
					height: next.outerHeight()
				});

			});
			buttons.filter(function(i) { return i == 0 || i == items.length; }).addClass('disabled');

			// Resize event
			var on_resize = function() {
				var active = items.filter('.active');
				items.css('width', scroller.width());
				content.css({
					left:   -active.position().left,
					height: active.outerHeight()
				});
			};
			$(window).on('resizeend', on_resize); on_resize();

		});

		return this;

	};

	// jQuery
	$(document).ready(function($) {

		// No-js
		$('html').removeClass('no-js').addClass('js');

		// Configuration
		var conf = $.extend({}, {
			columnsMobileColsThreshold: 3,
			zoomHoverIcons:             {
				image:     'icon-search',
				mail:      'icon-mail',
				title:     'icon-right',
				'default': 'icon-plus-circled'
			},
			togglesIcons: {
				closed: 'icon-plus-circled',
				opened: 'icon-minus-circled'
			},
			fancyboxOptions:            {},
			slippryOptions:             {
				captions: 'overlay',
				preload:  'all',
				useCSS:   true
			},
			isotopeOptions:             {},
			captions:                   {
				bricksAllButton: 'all'
			},
			callbacks: {
				onAfterReady: function($) {},
				onBeforeEnd:  function($) {}
			}
		}, typeof everythingConfig != 'undefined' ? everythingConfig : {});

		// Callback
		conf.callbacks.onAfterReady($);

		// On resize end
		(function() {
			var last_width = null;
			var timer      = null;
			var resizeend = function() {
				var width = $(window).width();
				if (width === last_width) {
					$(window).trigger('resizeend');
					timer = null;
				} else {
					last_width = $(window).width();
					timer = setTimeout(resizeend, 150);
				}
			};
			$(window).resize(function() {
				if (timer == null) {
					resizeend();
				}
			});
		})();

		// Horizontal align
		$(window).bind('load', function() {
			$('.horizontal-align')
				.css('width', function() { return $(this).outerWidth()+1; })
				.css('float', 'none');
		});

		// Vertical align
		(function() {
			var on_resize = function() {
				$('.vertical-align').each(function() {
					$(this).css('top', ($(this).parent().height() - $(this).outerHeight(true))*0.5);
				});
			};
			$(window).on('resizeend', on_resize); $(window).bind('load', on_resize); on_resize();
		})(); // not used

		// Movable container
		$('.movable-container').each(function() {
			$(this).movableContainer($(this).is('[data-movable-container-force-touch-device="true"]'));
		});

		// Scroller
		$('.scroller').scroller();

		// Zoom hover
		$('.zoom-hover').each(function() {

			// Layers
			var overlay = $('<div />', {'class': 'zoom-hover-overlay'}).appendTo(this);
			var title;
			var title_left;

			// Icon
			var icon;
			if ($(this).is('a[href].fb') && $(this).attr('href').match(/\.(jpe?g|png|gif|bmp)(\?.+)?$/i)) {
				icon = conf.zoomHoverIcons.image;
			} else if ($(this).is('a[href^="mailto:"]')) {
				icon = conf.zoomHoverIcons.mail;
			} else {
				icon = $(this).is('[title]') ? conf.zoomHoverIcons.title : conf.zoomHoverIcons['default'];
			}
			icon = $(this).getData('zoom-hover-icon', icon);

			// Title
			if ($(this).is('[title]')) {
				title = $('<div />', {'class': 'button small'}).html('<span>'+$(this).attr('title')+'</span>');
				if (icon) {
					title.append($('<i />', {'class': icon}));
				}
			} else {
				title = icon ? $('<i />', {'class': icon}) : $('<div />');
			}
			title.addClass('zoom-hover-title').appendTo(overlay);

			// Hover
			$(this)
				.hover(function() {
					title
						.toggleClass('tiny', title.is('i') && !$('html').is('.lt-ie9') && ($(this).innerWidth() < 100 || $(this).innerHeight() < 100))
						.css('margin-top', Math.round(0.5*($(this).innerHeight()-title.outerHeight())));
					title_left = Math.round(-0.5*title.outerWidth());
					if ($('html').is('.lt-ie9')) {
						title.css('margin-left', title_left);
					} else {
						title.stop(true).css('margin-left', title_left-10).animate({'margin-left': title_left}, 100);
					}
				}, function() {
					if (!$('html').is('.lt-ie9')) {
						title.stop(true).animate({'margin-left': title_left+10}, 100);
					}
				});

		});

		// Grayscale hover
		$('.grayscale-hover:has(> img)').each(function() {
			$(this).addClass('image-hover');
			var img = $('> img', this);
			img.clone().appendTo($(this));
			img.addClass('grayscale');
		});

		// Embed
		$('.embed').each(function() {
			var video = $('> iframe, > object, > embed', this).filter('[width][height]').first();
			if (video.length > 0) {
				if (video.is('[width$="%"]')) {
					$(this).css('height', video.attr('height'));
				} else {
					var ratio = (parseInt(video.attr('height'), 10) / parseInt(video.attr('width'), 10))*100;
					$(this).css({'padding-bottom': ratio+'%', height: 0});
				}
			}
		});

		// Table
		$('table:not(.fixed)').wrap($('<div />', {'class': 'table'}));

		// Input
		$('.ie input[type="text"], .ie input[type="email"], .ie textarea').filter('[placeholder]').each(function() {
			var ph = $(this).attr('placeholder');
			$(this)
				.focus(function() {
					if ($(this).hasClass('placeholder')) {
						$(this).removeClass('placeholder').val('');
					}
				})
				.blur(function() {
					if ($(this).val() === '') {
						$(this).addClass('placeholder').val(ph);
					}
				})
				.blur();
		});

		// Button
		$('.button, button, input[type="button"]').filter('[data-button-href]').click(function() {
			switch ($(this).getData('button-target', '_self')) {
				case '_blank':  window.open($(this).data('button-href')); break;
				case '_top':    window.top.location    = $(this).data('button-href'); break;
				case '_parent': window.parent.location = $(this).data('button-href'); break;
				default:        window.location        = $(this).data('button-href');
			}
		});

		// Message
		$('.message[data-message-closable="true"]').each(function() {

			var _this = this;
			var preserve_state = $(this).is('.message[data-message-preserve-state="true"]') && $(this).attr('id') && typeof(Storage) !== 'undefined';

			if (preserve_state && localStorage.getItem('everything-'+$(this).attr('id')) === 'true') {
				$(this).remove();
				return;
			}

			$('<i />', {'class': 'icon-cancel close'}).click(function() {

				if ($(_this).is(':animated')) {
					return;
				}

				var prev   = $(_this).prev();
				var next   = $(_this).next();
				var margin = Number.POSITIVE_INFINITY;
				var height = $(_this).outerHeight();

				if (prev.length > 0) {
					margin  = Math.min(margin, parseInt(prev.css('margin-bottom')));
					height += Math.max(parseInt($(_this).css('margin-top'))-parseInt(prev.css('margin-bottom')), 0);
				}
				if (next.length > 0) {
					margin  = Math.min(margin, parseInt(next.css('margin-top')));
					height += Math.max(parseInt($(_this).css('margin-bottom'))-parseInt(next.css('margin-top')), 0);
				}
				margin = margin == Number.POSITIVE_INFINIT ? 0 : margin / 2;

				$(_this)
					.fadeTo(200, 0, function() {
						$(this).addClass('closed').css('height', height);
					})
					.animate({
						marginTop:    -margin,
						marginBottom: -margin,
						height:       0
					}, {
						duration: 400,
						complete: function() {
							if (preserve_state) {
								localStorage.setItem('everything-'+$(this).attr('id'), 'true');
							}
							$(this).remove();
						}
					});

			}).appendTo($(this));

		});

		// Tooltip
		$('.tipsy-tooltip').each(function() {
			$(this).tipsy({
				gravity: $(this).getData('tipsy-tooltip-gravity', 's'),
				fade:    $(this).getData('tipsy-tooltip-fade', false)
			});
		});

		// Columns
		$('.columns').each(function() {

			var cols = $('> ul > li', this);

			// Alternative mode
			if (!$(this).hasClass('alt-mobile') && cols.length >= conf.columnsMobileColsThreshold) {
				var dens = [];
				cols.each(function() {
					var m = $(this).attr('class').match(/\bcol-1-([0-9]+)\b/);
					if (m !== null) {
						dens.push(parseInt(m[1]));
					}
				});
				if (dens.length == cols.length) {
					if ($.arrayUnique(dens).length == 1) {
						$(this).addClass('alt-mobile');
					} else {
						do {
							var changed = false;
							var i = 0;
							while (i+1 < dens.length) {
								if (dens[i] % 2 == 0 && dens[i] == dens[i+1]) {
									dens.splice(i, 2, dens[i] / 2);
									changed = true;
								} else {
									i++;
								}
							}
						} while (changed);
						if ($.arrayUnique(dens).length == 1) {
							$(this).addClass('alt-mobile');
						}
					}
				}
			}

			// Rows clear
			var lcm = 232792560; // LCM(1-20)
			var sum = {desktop: 0, mobile: [0, 0]};
			cols.each(function() {
				if (sum.desktop >= lcm) {
					$(this).addClass('clear-row');
					sum.desktop = 0;
				}
				if (sum.mobile[0] >= lcm) {
					$(this).addClass('mobile-1-clear-row');
					sum.mobile[0] = 0;
				}
				if (sum.mobile[1] >= lcm) {
					$(this).addClass('mobile-2-clear-row');
					sum.mobile[1] = 0;
				}
				var m = $(this).attr('class').match(/\bcol-([0-9]+)-([0-9]+)\b/); // todo: spr. czy ma class w ogole
				if (m !== null) {
					sum.desktop   += m[1]*(lcm/m[2]);
					sum.mobile[0] += m[1]*(lcm/Math.ceil(m[2]/2));
					sum.mobile[1] += m[1]*(lcm/Math.ceil(m[2]/4));
				}
			});

		});

		// Tabs
		$('.tabs').each(function() {

			var nav = $('<ul />', {'class': 'nav'}).prependTo(this);
			var tabs = $('> div[title]', this);

			// Tabs
			tabs
				.each(function() {
					$('<li />', {'class': $(this).hasClass('active') ? 'active' : ''})
						.text($(this).attr('title'))
						.click(function() {
							$(this).addClass('active').siblings().removeClass('active');
							var active_tab = tabs.removeClass('active').eq($(this).index()).addClass('active');
							$('img', active_tab).trigger('show');
							$('.bricks-isotope', active_tab).isotope('layout');
						})
						.appendTo(nav);
				})
				.attr('title', '');

			// Navigation
			nav.movableContainer();

			$('> :first-child, > .active', nav).click();

			// Deep linking
			var onhashchange = function() {
				var hash = unescape(self.document.location.hash).substring(1);
				if (!hash || hash == '*') {
					return;
				}
				var tab = tabs.filter('#'+hash);
				if (tab.length == 0) {
					return;
				}
				$('> :eq('+(tab.index()-1)+')', nav).click();
				$(window).scrollTop(tab.offset().top);
			}
			if ('onhashchange' in window) {
				window.onhashchange = onhashchange;
			}
			onhashchange();

		});

		// Navigation tabs
		$('.nav-tabs').each(function() {
			$('ul', this)
				.discardWhiteSpace()
				.movableContainer();
		});

		// Super tabs
		$('.super-tabs').each(function() {

			var nav     = $('<ul />', {'class': 'nav'}).appendTo(this);
			var tabs    = $('> div[title]', this);
			var ordered = $(this).is('[data-super-tabs-ordered="true"]');

			// Wrapping
			$(this).wrapInner($('<div />'));
			var wrapper = $('> div', this);
			var on_resize = function() {
				wrapper.css('height', tabs.filter('.active').height());
			};
			$(window).on('resizeend', on_resize);

			// Tabs
			tabs
				.each(function(i) {
					$('<li />', {'class': $(this).hasClass('active') ? 'active' : ''})
						.append($('<div />', {'class': 'table-vertical-align'})
							.append($('<div />')
								.append($('<h2 />')
									.text($(this).attr('title'))
									.prepend(ordered ? $('<span />').text(i+1) : null)
								)
								.append($(this).is('[data-super-tabs-description]') && $(this).data('super-tabs-description') ? $('<small />').text($(this).data('super-tabs-description')) : null)
							)
						)
						.click(function() {
							$(this).addClass('active').siblings().removeClass('active');
							tabs.removeClass('active').eq($(this).index()).addClass('active').find('img').trigger('show');
							on_resize();
						})
						.appendTo(nav);
				})
				.attr('title', '');

			// Navigation
			$('li', nav).css('height', (100 / tabs.length).toFixed(2)+'%');
			$('> :first-child, > .active', nav).click();

			$(this).imagesLoaded(on_resize);

		});

		// Toggles
		$('.toggles').each(function() {

			var _this = this;

			$('> li[title][title!=""]', this).each(function() {

				// Content
				$(this).wrapInner('<div /></div>');

				// Title
				$('<h3 />')
					.text($(this).attr('title'))
					.prepend('<i class="'+conf.togglesIcons.closed+'"></i>')
					.prepend('<i class="'+conf.togglesIcons.opened+' display-none"></i>')
					.click(function() {
						if ($(_this).is('[data-toggles-singular="true"]') && !$(this).next('div').is(':visible')) {
							$(this).parent().siblings().each(function() {
								$('> h3 > .'+conf.togglesIcons.closed, this).toggleClass('display-none', false);
								$('> h3 > .'+conf.togglesIcons.opened, this).toggleClass('display-none', true);
								$('> div', this).stop(true).slideUp(200);
							});
						}
						$('i', this).toggleClass('display-none');
						$(this).next('div').stop(true).slideToggle(200);
					})
					.prependTo($(this));

				$(this).removeAttr('title');

				// Active
				if ($(this).hasClass('active')) {
					$('> h3 > i', this).toggleClass('display-none');
					$('> div', this).show();
				}

			});

		});

		// Fancybox
		$('a[href].fb')
			.each(function() {
				var youtube = $(this).attr('href').match(/^https?:\/\/(www\.youtube\.com\/watch\?v=|youtu\.be\/)([-_a-z0-9]+)/i);
				var vimeo   = $(this).attr('href').match(/^https?:\/\/vimeo.com\/([-_a-z0-9]+)/i);
				if (youtube != null) {
					$(this).data({'fancybox-type': 'iframe', 'fancybox-href': '//www.youtube.com/embed/'+youtube[2]+'?wmode=opaque'});
				}
				else if (vimeo != null) {
					$(this).data({'fancybox-type': 'iframe', 'fancybox-href': '//player.vimeo.com/video/'+vimeo[1]});
				}
			})
			.fancybox($.extend({}, conf.fancyboxOptions, {
				margin:      [30, 70, 30, 70],
				padding:     2,
				aspectRatio: true
			}));

		// Social buttons
		$('.social-buttons ul').discardWhiteSpace();

		// Contact form
		$('.contact-form').submit(function() {
			if ($('input[type="submit"]', this).prop('disabled')) {
				return false;
			}
			var _this = this;
			$('input[type="submit"]', this).prop('disabled', true);
			$.ajax({
				url:      $(this).attr('action'),
				type:     'POST',
				data:     $(this).serialize(),
				dataType: 'json',
				complete: function() {
					$('input[type="submit"]', _this).prop('disabled', false);
				},
				success: function(data) {
					if (data === null) {
						$('.msg', _this).text('Unknown error.');
					} else {
						$('.msg', _this).text(data.message);
						if (data.result) {
							$('input[type="text"], textarea', _this).val('');
						}
					}
				}
			});
			return false;
		});

		// Slider
		$('.slider').each(function() {

			$(this).slippry($.extend({}, conf.slippryOptions, {
				transition: $(this).getData('slider-transition', 'fade'),
				speed:      $(this).getData('slider-speed', 800),
				auto:       $(this).getData('slider-auto', false),
				pause:      $(this).getData('slider-pause', 3000),
				controls:   $(this).getData('slider-controls', true),
				pager:      $(this).getData('slider-pager', true),
				onSlideBefore: function(slide, old_index, new_index) {

					var old_slide = slide.closest('.sy-list').find('li').eq(old_index);

					// Pause YouTube and Vimeo players
					var youtube = $('iframe[src*="//www.youtube.com"]', old_slide);
					var vimeo = $('iframe[src*="//player.vimeo.com"]', old_slide);
					if (youtube.length > 0) {
						youtube[0].contentWindow.postMessage(JSON.stringify({event: 'command', func: 'pauseVideo'}), '*');
					}
					if (vimeo.length > 0) {
						vimeo[0].contentWindow.postMessage(JSON.stringify({method: 'pause'}), vimeo.attr('src').split('?')[0]);
					}
					$('audio, video', old_slide).each(function() {
						this.player.media.pause();
					});

					// Hidding control-nav on embed slides
					slide.closest('.sy-slides-crop').next('.sy-controls').css('visibility', slide.is(':has(.embed)') ? 'hidden' : 'visible');

				}
			}));

		});

		// Bricks
		$('.bricks').each(function() {

			var _this   = this;
			var boxes   = $('> div', this).addClass('bricks-box');
			var isotope = $('<div />', {'class': 'bricks-isotope'}).append(boxes).appendTo($(this));

			if ($(this).getData('bricks-columns', 2) >= conf.columnsMobileColsThreshold) {
				$(this).addClass('alt-mobile');
			}

			isotope.imagesLoaded(function() {

				// Isotope
				isotope.isotope($.extend({}, conf.isotopeOptions, {
					itemSelector: '.bricks-box',
					hiddenStyle:  {
						opacity: 0,
						transform: 'scale(0.75)'
					}
				}));

				// Filter
				if ($(_this).is('[data-bricks-filter="true"]') && boxes.filter('[data-bricks-terms]').length > 0) {

					var filter = $('<div />', {'class': 'bricks-filter'}).prependTo($(_this));

					// All button
					$('<a />', {'class': 'button small active', href: '#*'})
						.text(conf.captions.bricksAllButton)
						.click(function() {
							isotope.isotope({
								filter: '*'
							});
						})
						.appendTo(filter);

					// Terms buttons
					var terms = [];
					boxes.filter('[data-bricks-terms]').each(function() {
						$.merge(terms, $.grep($(this).data('bricks-terms'), function(term) {
							return terms.indexOf(term) == -1;
						}));
					});
					terms.sort();
					$.each(terms, function(i, term) {
						$('<a />', {'class': 'button small', href: '#'+term})
							.text(term)
							.click(function() {
								isotope.isotope({
									filter: function() {
										return $(this).getData('bricks-terms', []).indexOf(term) != -1;
									}
								});
								return false;
							})
							.appendTo(filter);
					});

					// Buttons
					$('.button', filter).click(function() {
						$(this).addClass('active').siblings().removeClass('active');
					});

					// Deep linking
					var onhashchange = function() {
						var hash = unescape(self.document.location.hash).substring(1);
						if (!hash) {
							hash = '*';
						}
						$('.button[href="#'+hash+'"]', filter).click();
					}
					if ('onhashchange' in window) {
						window.onhashchange = onhashchange;
					}
					onhashchange();

				}

				// Layout refresh
				isotope.isotope('on', 'layoutComplete', function() {
					isotope.parent().closest('.bricks-isotope').isotope('layout');
				});
				$(document).on('webfontactive', function() {
					isotope.isotope('layout');
				});

			});

		});

		// Outer container
		(function() {

			var on_resize = function() {
				var fixed_top = $('#wrapper').offset().top-parseInt($('html').css('margin-top'));
				$('.outer-container.fixed, .outer-container.floated').each(function() {
					var next   = $(this).next('.outer-container');
					var height = 0;
					if (!$(this).hasClass('floated') || !next.is('#banner')) {
						var logo = $('#logo.shrunken img', this);
						if (logo.length > 0) {
							var h = logo.height();
							logo.css('height', logo.attr('height'));
							height = $(this).outerHeight();
							if (h > 0) {
								logo.css('height', h);
							}
						} else {
							height = $(this).outerHeight();
						}
					}
					fixed_top += height;
					next.css('margin-top', fixed_top);
				});
			};

			$(window).on('resizeend', on_resize); on_resize();

		})();

		// Logo
		$('.outer-container.fixed #logo.shrunken img').filter(function() {
			return $(this).attr('height') > 30;
		}).each(function() {
			var _this = this;
			var on_scroll = function() {
				$(_this).css('height', Math.max($(_this).attr('height')-$(window).scrollTop(), 30));
				var max = Math.round($(_this).width()*($(_this).attr('height')/$(_this).attr('width')));
				if ($(_this).height() > max) {
					$(_this).css('height', max);
				}
			};
			$(this).css('width', 'auto');
			$(this).imagesLoaded(on_scroll);
			$(window).on('resizeend', on_scroll);
			$(window).scroll(on_scroll); on_scroll();
		});

		// Search
		$('.edge-bar .search-box button').click(function() {

			var search = $(this).closest('.search-box');
			if ((search.hasClass('opened') && $.trim($(this).prev('input').val()) == '') || !search.hasClass('opened')) {
				search.toggleClass('opened');
				return false;
			}

		});

		// Navigation menus
		$('.nav-menu, .top-nav-menu, .mobile-nav-menu').each(function() {

			$('ul, li', this).discardWhiteSpace();
			$('li:has(> ul)', this).addClass('sub');
			$('ul:first > li', this).addClass('level-0');

			$('a[href="#"]', this).click(function() {
				return false;
			});

		});

		// Main navigation menu
		$('.nav-menu.main, .mobile-nav-menu.main').each(function() {

			$('li:not(.level-0.mega)')
				.filter(function() { return ['-', '–'].indexOf($('> a', this).text()) > -1; })
				.addClass('no-label')
				.find('> a')
					.contents()
					.filter(function() { return this.nodeType == 3; })
					.remove();

			$('li.level-0.mega > ul > li:has(> a)', this)
				.filter(function() { return ['-', '–'].indexOf($('> a', this).text()) > -1; })
				.addClass('no-label');

		});

		$('.nav-menu.main').each(function() {

			$('li.sub', this).mouseenter(function() {
				var parent = $(this).closest('ul');
				var li = $(this);
				var ul = $('> ul', this).removeClass('left').removeAttr('style');
				if (ul.offset().left + ul.outerWidth() > $(window).width() - 20 || (parent.is('.left') && li.offset().left - ul.outerWidth() >= 20)) {
					ul.addClass('left');
					if (ul.is('.level-0 > ul') && ul.offset().left < 10) {
						ul.css('right', parseInt(ul.css('right'))+ul.offset().left-10);
					}
				}
			});

		});

		// Top navigation menu
		$('.top-nav-menu li:has(> a > i)')
			.filter(function() { return ['-', '–'].indexOf($('> a', this).text()) > -1; })
			.addClass('no-label')
			.find('> a')
				.contents()
				.filter(function() { return this.nodeType == 3; })
				.remove();

		// Secondary navigation menu
		$('.nav-menu.secondary').each(function() {

			$('ul:first', this).movableContainer();
			var lower = $(this).hasClass('lower');
			var items = $('ul.movable-container-content > li.sub', this);

			if (items.length == 0) {
				return;
			}

			var on_scroll = function() {
				var top = -$(window).scrollTop()+items.offset().top;
				items.each(function() {
					var offset = lower ? -$('> ul', this).outerHeight() : $(this).height();
					$('> ul', this).css('top', top+offset);
				});
			};
			$(window).scroll(on_scroll); on_scroll();

			$('.movable-container', this)
				.mousemove(function() {
					items.each(function() {
						$('> ul', this).css('left', $(this).offset().left);
					});
				})
				.mouseenter(on_scroll);

		});

		// Mobile section toggle
		$('#mobile-section-toggle').click(function() {
			$('#mobile-section').slideToggle(200);
			return false;
		});

		// Mobile navigation menu
		$('.mobile-nav-menu :not(.sub) > a:not(:has(i)):not(:has(img))').prepend('<i class="icon-dot"></i>');

		// Main mobile navigation menu
		$('.mobile-nav-menu.main').each(function() {

			$('.no-label', this).each(function() {
				$('> a', this).remove();
				$('> ul', this).unwrap().contents().unwrap();
			});

			$('.sub', this)
				.prepend('<a class="sub-toggle"><i class="icon-plus-circled"></i><i class="icon-minus-circled display-none"></i></a>')
				.find('> .sub-toggle')
					.click(function() {
						$(this).nextAll('ul').slideToggle(200);
						$('i', this).toggleClass('display-none');
						return false;
					});

			$('li .current', this).parents('ul').slice(0, -1).each(function() {
				$(this).toggle().prevAll('a.sub-toggle').find('> i').toggleClass('display-none');
			});

		});

		// Content
		(function() {
			var content_top = 0;
			var on_resize = function() {
				var height = 0;
				$('#content').closest('.outer-container').nextAll('.outer-container').each(function() {
					height += $(this).outerHeight();
				});
				content_top = Math.round($('#content').offset().top);
				$('#content > .container').css('min-height', Math.max($(window).height() - content_top - height - 1, 0));
			};
			setInterval(function() {
				if (content_top != $('#content').offset().top) {
					on_resize();
				}
			}, 500);
			$(window).on('resizeend', on_resize); //on_resize();
		})();

		// Footer
		if ($('#footer .widget').length == 0) {
			$('#footer').hide();
		}

		// Callback
		conf.callbacks.onBeforeEnd($);

	});

})(jQuery);