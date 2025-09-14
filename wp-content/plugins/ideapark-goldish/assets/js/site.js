(function ($, root, undefined) {
	"use strict";
	
	var ideapark_nav_text = [
		'<i class="ip-right h-carousel__prev"></i>',
		'<i class="ip-right h-carousel__next"></i>'
	];
	
	var ideapark_nav_text_alt = [
		'<i class="ip-arrow-long h-carousel__prev"></i>',
		'<i class="ip-arrow-long h-carousel__next"></i>'
	];
	
	var ideapark_nav_text_def = [
		'<i class="ip-right-default h-carousel__prev"></i>',
		'<i class="ip-right-default h-carousel__next"></i>'
	];
	
	var ideapark_on_transition_end = 'transitionend webkitTransitionEnd oTransitionEnd';
	var ideapark_nav_text_big = [
		'<i class="ip-right_big h-carousel__prev"></i>',
		'<i class="ip-right_big h-carousel__next"></i>'
	];
	var ideapark_is_rtl = $("body").hasClass('h-rtl');
	
	$(function () {
		ideapark_defer_action_add(function () {
			$(document)
				.on('click', ".js-ip-video", function (e) {
					e.preventDefault();
					var $this = $(this);
					$this.attr('data-vbtype', 'video');
					$this.attr('data-autoplay', 'true');
					ideapark_init_venobox($this);
				});
			
			ideapark_init_slider_carousel();
			ideapark_init_news_widget_carousel();
			ideapark_init_reviews_widget_carousel();
			ideapark_init_image_list_1_carousel();
			ideapark_init_image_list_2_carousel();
			ideapark_init_image_list_3_combined();
			ideapark_init_image_list_3_carousel();
			ideapark_init_countdown();
			ideapark_init_accordion();
			ideapark_init_tabs();
			ideapark_init_banners();
			ideapark_init_video_widget_carousel();
			
			ideapark_resize_action_500_add(function () {
				ideapark_init_running_line();
				ideapark_init_tabs(true);
				ideapark_init_image_list_1_carousel();
				ideapark_init_hotspot_points($('.c-ip-hotspot-carousel__image-wrap'));
			});
		});
		ideapark_init_hotspot_widget_carousel();
		ideapark_init_running_line();
	});
	
	root.ideapark_init_slider_carousel = function () {
		var need_load_animation = false;
		var draw_timer = [];
		var radius = 6;
		
		var arcs = [];
		var angle = 0;
		var i = 0;
		var d = 'M0,0 ';
		arcs[0] = d;
		do {
			angle += 5;
			angle %= 365;
			var radians = ((-angle - 90) / 180) * Math.PI;
			var x = Math.round((radius + 1 + Math.cos(radians) * radius) * 100) / 100;
			var y = Math.round((radius + 1 + Math.sin(radians) * radius) * 100) / 100;
			if (i === 0) {
				d += " M " + x + " " + y;
			} else {
				d += " L " + x + " " + y;
			}
			arcs[angle] = d;
			i++;
		} while (angle != 360);
		
		var drawCircle = function (circle_id, timeout) {
			var angle = 365;
			var circle = document.getElementById(circle_id);
			
			if (circle) {
				for (var index in draw_timer) {
					if (draw_timer[index]) {
						window.clearInterval(draw_timer[index]);
						draw_timer[index] = null;
						document.getElementById(index).setAttribute("d", arcs[0]);
					}
				}
				
				draw_timer[circle_id] = window.setInterval(
					function () {
						angle -= 5;
						if (angle >= 0) {
							angle %= 365;
							circle.setAttribute("d", arcs[angle]);
							if (angle === 0) {
								window.clearInterval(draw_timer[circle_id]);
							}
						}
					}, Math.round(timeout / 73));
			}
		};
		
		$('.js-slider-carousel:not(.owl-carousel)')
			.each(function () {
				var $this = $(this);
				var widget_id = $this.data('widget-id');
				var autoplay = $this.data('autoplay') === 'yes';
				var animation = $this.data('animation');
				var animation_timeout = $this.data('animation-timeout');
				var dots = !$this.hasClass('h-carousel--dots-hide');
				var params = {
					items        : 1,
					center       : false,
					autoWidth    : false,
					margin       : 0,
					rtl          : ideapark_is_rtl,
					nav          : !$(this).hasClass('h-carousel--nav-hide'),
					dots         : dots,
					dotsData     : true,
					loop         : true,
					navText      : ideapark_nav_text,
					responsive   : {
						0   : {
							nav: 0
						},
						1189: {
							nav: !$(this).hasClass('h-carousel--nav-hide'),
						}
					},
					onInitialized: function (event) {
						if (autoplay && dots) {
							var circle_id = "arc-" + widget_id + '-0';
							drawCircle(circle_id, animation_timeout);
						}
						if ($(window).width() <= 767) {
							$('.c-ip-slider__image--mobile[loading="lazy"]').removeAttr('loading');
						} else {
							$('.c-ip-slider__image--desktop[loading="lazy"]').removeAttr('loading');
						}
					}
				};
				
				
				if (autoplay) {
					params.autoplay = true;
					params.autoplayTimeout = animation_timeout;
				}
				
				if (animation != '') {
					params.animateOut = animation + '-out';
					params.animateIn = animation + '-in';
				}
				$this
					.addClass('owl-carousel')
					.on('changed.owl.carousel', function (event) {
						if (autoplay) {
							$this.trigger('stop.owl.autoplay');
							$this.trigger('play.owl.autoplay');
						}
						if (autoplay && dots) {
							var page = event.page, index;
							if (event.property.name == 'position') {
								// index = page.index + 1;
								// if (index == page.count) {
								// 	index = 0;
								// }
								drawCircle("arc-" + widget_id + '-' + page.index, animation_timeout);
							}
						}
					})
					.owlCarousel(params);
				
				if (ideapark_get_time() - ideapark_start_time > 4) {
					$this.trigger('next.owl.carousel');
				}
				
				need_load_animation = true;
			});
	};
	
	root.ideapark_init_gift_widget_carousel = function () {
		$('.js-gift-carousel:not(.owl-carousel)').each(function () {
			var $this = $(this);
			if ($('.c-ip-gift__item', $this).length > 1) {
				var autoplay = $this.data('autoplay') === 'yes';
				var animation_timeout = $this.data('animation-timeout');
				var params = {
					center       : false,
					autoWidth    : true,
					items        : 1,
					loop         : $this.hasClass('h-carousel--loop'),
					margin       : 0,
					rtl          : ideapark_is_rtl,
					nav          : !$this.hasClass('h-carousel--nav-hide'),
					dots         : !$this.hasClass('h-carousel--dots-hide'),
					navText      : ideapark_nav_text,
					onInitialized: ideapark_owl_hide_arrows
				};
				if (autoplay) {
					params.autoplay = true;
					params.autoplayTimeout = animation_timeout;
				}
				$this
					.addClass('owl-carousel')
					.on('resized.owl.carousel', ideapark_owl_hide_arrows)
					.owlCarousel(params);
			}
		});
	};
	
	root.ideapark_init_news_widget_carousel = function () {
		$('.js-news-carousel:not(.owl-carousel)').each(function () {
			var $list = $(this);
			if ($('.c-post-list', $list).length > 1) {
				var layout = $list.data('layout');
				var count = $list.data('count');
				var params = {
					center       : false,
					autoWidth    : false,
					loop         : false,
					items        : Math.min(3, count),
					margin       : 10,
					rtl          : ideapark_is_rtl,
					nav          : !$list.hasClass('h-carousel--nav-hide'),
					dots         : !$list.hasClass('h-carousel--dots-hide'),
					navText      : ideapark_nav_text_def,
					onInitialized: ideapark_owl_hide_arrows,
				};
				switch (layout) {
					case 'layout-1':
						params.responsive = {
							0   : {
								items: 1,
							},
							768 : {
								items : Math.min(2, count),
								margin: 20,
							},
							1190: {
								items: Math.min(3, count),
							},
						};
						break;
					case 'layout-2':
						params.items = 2;
						params.responsive = {
							0   : {
								items: 1,
							},
							1190: {
								items: Math.min(2, count),
							},
						};
						break;
					case 'layout-3':
						params.margin = 30;
						params.responsive = {
							0   : {
								items: 1,
							},
							768 : {
								items : Math.min(2, count),
								margin: 20,
							},
							1190: {
								items: Math.min(3, count),
							},
						};
						break;
				}
				$list
					.addClass('owl-carousel')
					.on('resized.owl.carousel', function () {
						ideapark_owl_hide_arrows($list);
						$list.trigger('arrows.owl.carousel');
					})
					.on('refreshed.owl.carousel', function () {
						$list.trigger('arrows.owl.carousel');
					})
					.owlCarousel(params)
					.on('arrows.owl.carousel', function () {
						var $image_container = $list.find('.c-post-list__thumb').first();
						if ($image_container.length && (window.innerWidth < (layout === 'layout-2' ? 768 : 1190) || layout === 'layout-1')) {
							var image_height = $image_container.outerHeight();
							$list.find(".owl-prev,.owl-next").css({
								'top': Math.round(image_height) + 'px'
							});
						} else {
							$list.find(".owl-prev,.owl-next").css({
								'top': ''
							});
						}
					})
					.on('changed.owl.carousel', function () {
						$list.find('.owl-nav,.owl-dots').removeClass('disabled');
					})
					.trigger('arrows.owl.carousel');
			}
		});
	};
	
	root.ideapark_init_video_widget_carousel = function () {
		
		$('.js-video-carousel:not(.owl-carousel)').each(function () {
			if ($('.c-ip-video-carousel__item', $(this)).length > 1) {
				$(this)
					.addClass('owl-carousel')
					.on('resized.owl.carousel', ideapark_owl_hide_arrows)
					.on('initialized.owl.carousel', function () {
						$(document.body).trigger('video-carousel-initialized');
					})
					.owlCarousel({
						center       : false,
						autoWidth    : true,
						items        : 1,
						loop         : false,
						margin       : 0,
						rtl          : ideapark_is_rtl,
						nav          : !$(this).hasClass('h-carousel--nav-hide'),
						dots         : !$(this).hasClass('h-carousel--dots-hide'),
						navText      : ideapark_nav_text,
						onInitialized: ideapark_owl_hide_arrows,
					});
			}
		});
	};
	
	root.ideapark_init_image_list_1_carousel = function () {
		$('.js-image-list-1').each(function () {
			var $this = $(this);
			var is_combined = $this.hasClass('c-ip-image-list-1__list--combined');
			
			var container_width = $this.closest('.c-ip-image-list-1__wrap').outerWidth();
			
			var items = 0;
			var items_width = 0;
			$this.find('.c-ip-image-list-1__item').each(function () {
				items_width += $(this).outerWidth();
				items++;
			});
			if ((items_width >= container_width && items > 1) && (!is_combined || window.innerWidth < 768)) {
				if (!$this.hasClass('owl-carousel')) {
					$this
						.addClass('owl-carousel')
						.on('resized.owl.carousel', ideapark_owl_hide_arrows)
						.owlCarousel({
							center       : true,
							margin       : 0,
							loop         : false,
							autoWidth    : true,
							items        : 1,
							rtl          : ideapark_is_rtl,
							nav          : !$this.hasClass('h-carousel--nav-hide'),
							dots         : !$this.hasClass('h-carousel--dots-hide'),
							navText      : ideapark_nav_text,
							onInitialized: ideapark_owl_hide_arrows,
							responsive   : {
								0  : {
									center: true,
								},
								375: {
									center: false,
								},
							}
						});
				}
			} else if (items > 1) {
				if ($this.hasClass('owl-carousel')) {
					$this
						.removeClass('owl-carousel')
						.trigger("destroy.owl.carousel");
				}
			}
		});
	};
	
	root.ideapark_init_image_list_2_carousel = function () {
		$('.js-image-list-2').each(function () {
			var $this = $(this);
			
			ideapark_on_all_images_loaded($this.find('.c-ip-image-list-2__image'), $this,
				function() {
				
					var f = function() {
						var container_width = $this.closest('.c-ip-image-list-2__wrap').outerWidth();
						var is_carousel = $this.hasClass('owl-carousel');
						var is_combined = $this.hasClass('c-ip-image-list-2__list--combined');
						var $first_item = $('.c-ip-image-list-2__item', $this).first();
						var margin = 40;
						if ($first_item.length) {
							var $owl_item = $first_item.closest('.owl-item');
							if ($owl_item.length) {
								$first_item = $owl_item;
							}
							var element = $first_item[0];
							var style = element.currentStyle || window.getComputedStyle(element);
							if (ideapark_is_rtl) {
								margin = parseInt(style.marginLeft.replace('px', '')) * (is_carousel ? 1 : 2);
							} else {
								margin = parseInt(style.marginRight.replace('px', '')) * (is_carousel ? 1 : 2);
							}
						} else {
							return;
						}
						
						var items = 0;
						var items_width = -margin;
						$this.find('.c-ip-image-list-2__item').each(function () {
							items_width += $(this).outerWidth() + margin;
							items++;
						});
						if ((items_width >= container_width && items > 1) && (!is_combined || window.innerWidth <= 500)) {
							if (!$this.hasClass('owl-carousel')) {
								var autoplay = $this.data('autoplay') === 'yes';
								var animation_timeout = $this.data('animation-timeout');
								var params = {
									center       : false,
									margin       : margin,
									loop         : false,
									autoWidth    : true,
									items        : 1,
									rtl          : ideapark_is_rtl,
									nav          : !$this.hasClass('h-carousel--nav-hide'),
									dots         : !$this.hasClass('h-carousel--dots-hide'),
									navText      : ideapark_nav_text,
									onInitialized: ideapark_owl_hide_arrows,
									responsive   : {
										0  : {
											autoWidth: false,
										},
										500: {
											autoWidth: true,
										},
									}
								};
								
								if (autoplay) {
									params.autoplay = true;
									params.loop = true;
									params.autoplayTimeout = animation_timeout;
								}
								$this
									.addClass('owl-carousel')
									.on('resized.owl.carousel', ideapark_owl_hide_arrows)
									.owlCarousel(params);
							}
						} else if (items > 1) {
							if ($this.hasClass('owl-carousel')) {
								$this
									.removeClass('owl-carousel')
									.trigger("destroy.owl.carousel");
							}
						}
					};
					
					f();
					
					if (!$this.hasClass('init')) {
						ideapark_resize_action_500_add(f);
						$this.addClass('init');
					}
				});
		});
	};
	
	root.ideapark_init_image_list_3_combined = function () {
		$('.js-image-list-3-combined:not(.init-combined)').each(function () {
			var $list = $(this);
			if ($list.find('.c-ip-image-list-3__item').length <= 1) {
				return;
			}
			var combined = $list.data('combined');
			
			var resized = function () {
				var is_mobile = window.innerWidth < 768;
				if (is_mobile && !$list.hasClass('owl-carousel')) {
					$list.addClass(combined);
					ideapark_init_image_list_3_carousel();
				} else if (!is_mobile && $list.hasClass('owl-carousel')) {
					$list
						.removeClass(combined)
						.removeClass('owl-carousel')
						.trigger("destroy.owl.carousel");
				}
			};
			
			resized();
			ideapark_resize_action_500_add(resized);
			
			$list.addClass('init-combined');
		});
	};
	
	root.ideapark_init_image_list_3_carousel = function () {
		$('.js-image-list-3:not(.owl-carousel)').each(function () {
			var $list = $(this);
			var count = $list.data('count');
			if (count > 1) {
				var autoplay = $list.data('autoplay') === 'yes';
				var animation_timeout = $list.data('animation-timeout');
				var params = {
					center    : false,
					loop      : false,
					autoWidth : false,
					margin    : 0,
					rtl       : ideapark_is_rtl,
					nav       : !$list.hasClass('h-carousel--nav-hide'),
					dots      : !$list.hasClass('h-carousel--dots-hide'),
					navText   : ideapark_nav_text,
					responsive: {
						0   : {
							items: 1,
						},
						768 : {
							items: Math.min(3, count),
						},
						1190: {
							items: Math.min(6, count),
						},
					}
				};
				
				if (autoplay) {
					params.autoplay = true;
					params.loop = true;
					params.autoplayTimeout = animation_timeout;
				}
				
				$list
					.addClass('owl-carousel')
					.owlCarousel(params);
			}
			
		});
	};
	
	root.ideapark_init_reviews_widget_carousel = function () {
		$('.js-reviews-carousel:not(.owl-carousel)').each(function () {
			var $list = $(this);
			if ($list.find('.c-ip-reviews__item').length > 1) {
				var layout = $list.data('layout');
				var autoplay = $list.data('autoplay') === 'yes';
				var animation_timeout = $list.data('animation-timeout');
				var params = {
					center       : true,
					autoWidth    : true,
					items        : 1,
					margin       : layout === 'layout-2' ? 80 : (layout === 'layout-3' ? 50 : 0),
					loop         : true,
					rtl          : ideapark_is_rtl,
					nav          : !$list.hasClass('h-carousel--nav-hide'),
					dots         : !$list.hasClass('h-carousel--dots-hide'),
					onInitialized: ideapark_owl_hide_arrows,
					navText      : ideapark_nav_text_alt,
					responsive   : {
						0  : {
							autoWidth: false
						},
						768: {
							autoWidth: true
						}
					},
				};
				if (autoplay) {
					params.autoplay = true;
					params.autoplayTimeout = animation_timeout;
					params.autoplayHoverPause = true;
				}
				$list
					.addClass('owl-carousel')
					.on('resized.owl.carousel', ideapark_owl_hide_arrows)
					.owlCarousel(params);
			}
		});
	};
	
	root.ideapark_init_countdown = function () {
		$('.js-countdown').each(function () {
			var $this = $(this),
				finalDate = $(this).data('date'),
				_n = $(this).data('month'),
				_w = $(this).data('week'),
				_d = $(this).data('day'),
				_h = $(this).data('hour'),
				_m = $(this).data('minute'),
				_s = $(this).data('second');
			if (finalDate) {
				$this.countdown(finalDate, function (event) {
					var is_month = !($(window).width() < 375 || _n === 'no' || _n === 'false' || _n === '0');
					var is_week = !($(window).width() < 375 || _w === 'no' || _w === 'false' || _w === '0');
					$this.html(event.strftime('' +
						(!is_month ? '' : ('<span class="c-ip-countdown__item"><span class="c-ip-countdown__digits">%-m</span><i class="ip-romb c-ip-countdown__separator"></i><span class="c-ip-countdown__title">' + ideapark_countdown_months + '</span></span>')) +
						(!is_week ? '' : ('<span class="c-ip-countdown__item"><span class="c-ip-countdown__digits">%' + (is_month ? '-W' : '-w') + '</span><i class="ip-romb c-ip-countdown__separator"></i><span class="c-ip-countdown__title">' + ideapark_countdown_weeks + '</span></span>')) +
						(_d === 'no' || _d === 'false' || _d === '0' ? '' : ('<span class="c-ip-countdown__item"><span class="c-ip-countdown__digits">%' + (is_week ? '-d' : (is_month ? '-n' : '-D')) + '</span><i class="ip-romb c-ip-countdown__separator"></i><span class="c-ip-countdown__title">' + ideapark_countdown_days + '</span></span>')) +
						(_h === 'no' || _h === 'false' || _h === '0' ? '' : ('<span class="c-ip-countdown__item"><span class="c-ip-countdown__digits">%H</span><i class="ip-romb c-ip-countdown__separator"></i><span class="c-ip-countdown__title">' + ideapark_countdown_hours + '</span></span>')) +
						(_m === 'no' || _m === 'false' || _m === '0' ? '' : ('<span class="c-ip-countdown__item"><span class="c-ip-countdown__digits">%M</span><i class="ip-romb c-ip-countdown__separator"></i><span class="c-ip-countdown__title">' + ideapark_countdown_minutes + '</span></span>')) +
						(_s === 'no' || _s === 'false' || _s === '0' ? '' : ('<span class="c-ip-countdown__item"><span class="c-ip-countdown__digits">%S</span><i class="ip-romb c-ip-countdown__separator"></i><span class="c-ip-countdown__title">' + ideapark_countdown_seconds + '</span></span>'))
					));
				});
			}
		});
	};
	
	root.ideapark_init_accordion = function () {
		$('.js-accordion-title:not(.init)').on('click', function () {
			var $this = $(this);
			var $accordion = $this.closest('.c-ip-accordion');
			var $item = $this.closest('.c-ip-accordion__item');
			var $content = $item.find('.c-ip-accordion__content');
			var $old_item = $accordion.find('.c-ip-accordion__item--active');
			var is_active = $item.hasClass('c-ip-accordion__item--active');
			if ($old_item.length) {
				$old_item.removeClass('c-ip-accordion__item--active');
				$old_item.find('.c-ip-accordion__content').slideUp();
			}
			if (!is_active) {
				$content.slideDown();
				$item.addClass('c-ip-accordion__item--active');
			}
		}).addClass('init');
	};
	
	root.ideapark_init_tabs = function (is_resize) {
		$('.js-ip-tabs-list').each(function () {
			var $this = $(this);
			var $tabs = $this.closest('.js-ip-tabs');
			var container_width = $this.closest('.js-ip-tabs-wrap').outerWidth();
			var $first_tab = $('.js-ip-tabs-menu-item', $tabs).first();
			var margin = 63;
			if ($first_tab.length) {
				var $owl_item = $first_tab.closest('.owl-item');
				if ($owl_item.length) {
					$first_tab = $owl_item;
				}
				var element = $first_tab[0];
				var style = element.currentStyle || window.getComputedStyle(element);
				if (ideapark_is_rtl) {
					margin = parseInt(style.marginLeft.replace('px', ''));
				} else {
					margin = parseInt(style.marginRight.replace('px', ''));
				}
				
			} else {
				return;
			}
			
			var items_width = -margin;
			$this.find('.js-ip-tabs-menu-item').each(function () {
				items_width += $(this).outerWidth() + margin;
			});
			
			if (items_width >= container_width) {
				if (!$this.hasClass('owl-carousel')) {
					$this
						.addClass('owl-carousel')
						.owlCarousel({
							center    : false,
							loop      : false,
							margin    : margin,
							autoWidth : true,
							items     : 1,
							rtl       : ideapark_is_rtl,
							nav       : !$this.hasClass('h-carousel--nav-hide'),
							dots      : !$this.hasClass('h-carousel--dots-hide'),
							navText   : ideapark_nav_text,
							responsive: {
								0  : {
									nav: 0
								},
								769: {
									nav: !$this.hasClass('h-carousel--nav-hide'),
								}
							},
							
						});
				}
			} else {
				if ($this.hasClass('owl-carousel')) {
					$this
						.removeClass('owl-carousel')
						.trigger("destroy.owl.carousel");
				}
			}
			
			if (typeof is_resize === 'undefined' || !is_resize) {
				if (!$this.hasClass('init')) {
					$('.js-ip-tabs-link', $tabs).on('click', function (e) {
						e.preventDefault();
						var $this = $(this);
						var index = $this.data('index');
						var $content = $tabs.find($this.attr('href'));
						var $tab = $this.closest('.js-ip-tabs-menu-item');
						var $current_content = $tabs.find('.visible');
						$tabs.find('.js-ip-tabs-menu-item.active').removeClass('active');
						$tab.addClass('active');
						if ($content.length && $current_content.length) {
							var f = function () {
								f = null;
								$current_content.removeClass('visible');
								$content.addClass('visible');
								setTimeout(function () {
									$content.find('.owl-carousel').each(function () {
										$(this).trigger('refresh.owl.carousel');
										ideapark_owl_hide_arrows($(this));
									});
									$content.addClass('active');
								}, 100);
							};
							var f2 = function () {
								if (f) {
									f();
								}
							};
							ideapark_on_transition_end_callback($current_content, f2);
							setTimeout(f2, 400);
							$current_content.removeClass('active');
						}
						$this.closest('.js-ip-tabs-list').trigger("to.owl.carousel", [index, 300]);
					});
					$this.addClass('init');
				}
			}
		});
	};
	
	root.ideapark_init_banners = function () {
		$('.js-ip-banners:not(.init)').each(function () {
			var $list = $(this);
			var $items = $('.c-ip-banners__item', $list);
			var animation = $list.data('animation');
			var animation_timeout = $list.data('animation-timeout');
			var i = 1;
			var $banners = [];
			var timer = null;
			var is_images_loaded = false;
			var is_started = false;
			$list.addClass('init');
			
			if ($items.length) {
				
				var observer = new IntersectionObserver(function (entries) {
					if (entries[0].isIntersecting === true) {
						if (!is_images_loaded) {
							$list.find('.c-ip-banners__image[loading]').removeAttr('loading');
							is_images_loaded = true;
						}
						is_started = true;
					} else {
						is_started = false;
					}
				}, {threshold: [0]});
				
				observer.observe($list[0]);
				
				$items.each(function () {
					var $banner = $(this);
					var order = i++;
					var timestamp = Math.round(new Date() / 1000);
					$banner.css({order: order});
					$banner.data('timestamp', timestamp - (i === 2 ? -1 : 0));
					$banners.push($banner);
				});
				timer = setInterval(function () {
					if (!is_started) {
						return;
					}
					var layout = $list.data('layout');
					var margin_list = 0;
					var element_list = $list[0];
					var style_list = element_list.currentStyle || window.getComputedStyle(element_list);
					if (ideapark_is_rtl) {
						margin_list = parseInt(style_list.marginLeft.replace('px', ''));
					} else {
						margin_list = parseInt(style_list.marginRight.replace('px', ''));
					}
					
					var $first_item = $items.first();
					var element = $first_item[0];
					var style = element.currentStyle || window.getComputedStyle(element);
					var banner_width = parseInt(style.width.replace('px', ''));
					var container_width = $list.outerWidth();
					var banners_on_screen = Math.round(container_width / banner_width);
					var $banners_visible = [];
					var $banners_order = [];
					$banners.sort(function ($a, $b) {
						return $a.css('order') - $b.css('order');
					});
					for (var i = 0; i < $banners.length; i++) {
						if (i < banners_on_screen) {
							$banners_visible.push($banners[i]);
						} else {
							$banners_order.push($banners[i]);
						}
					}
					if ($banners_order.length) {
						$banners_visible.sort(function ($a, $b) {
							return $a.data('timestamp') - $b.data('timestamp');
						});
						$banners_order.sort(function ($a, $b) {
							return $a.data('timestamp') - $b.data('timestamp');
						});
						
						var timestamp = Math.round(new Date() / 1000);
						var $old_banner = $banners_visible[0];
						if (typeof $old_banner === 'undefined') {
							clearInterval(timer);
							return;
						}
						var old_order = $old_banner.css('order');
						var $new_banner = $banners_order[0];
						var new_order = $new_banner.css('order');
						
						$list.css({
							'height': $old_banner.outerHeight() + 'px'
						});
						$new_banner.addClass('c-ip-banners__item--animation');
						
						ideapark_on_animation_end_callback($new_banner, function () {
							$new_banner.css({order: old_order}).data('timestamp', timestamp);
							$old_banner.css({order: new_order}).data('timestamp', timestamp);
							$old_banner.removeClass(animation + '-out');
							$new_banner.removeClass(animation + '-in').removeClass('c-ip-banners__item--animation').css({
								'left'  : '',
								'height': ''
							});
							$list.css({
								'height': ''
							});
						});
						
						$new_banner.addClass(animation + '-in').css({
							'left'  : ($old_banner.offset().left - $list.offset().left + margin_list) + 'px',
							'height': $old_banner.outerHeight() + 'px'
						});
						if (layout === 'layout-3') {
							$old_banner.addClass(animation + '-out');
						}
					}
				}, animation_timeout);
			}
		});
	};
	
	root.ideapark_init_hotspot_widget_carousel = function () {
		$('.js-hotspot-carousel:not(.owl-carousel)').each(function () {
			var $this = $(this);
			var $container = $this.closest('.c-ip-hotspot-carousel');
			var is_dynamic_bg = $this.data('dynamic') === 'yes';
			var autoplay = $this.data('autoplay') === 'yes';
			var animation_timeout = $this.data('animation-timeout');
			var params = {
				center            : false,
				loop              : false,
				autoHeight        : true,
				autoplayHoverPause: true,
				items             : 1,
				margin            : 0,
				rtl               : ideapark_is_rtl,
				nav               : !$this.hasClass('h-carousel--hide'),
				dots              : !$this.hasClass('h-carousel--dots-hide'),
				navText           : ideapark_nav_text
			};
			if (autoplay) {
				params.autoplay = true;
				params.loop = true;
				params.autoplayTimeout = animation_timeout;
			}
			
			$this
				.on('translated.owl.carousel', function (e) {
					if (is_dynamic_bg) {
						var $item = $('.owl-item.active .c-ip-hotspot-carousel__item', $this);
						var bg_image = $item.data('bg-image');
						var bg_color = $item.data('bg-color');
						$container.css({
							'background-color': bg_color,
							'background-image': 'url(' + bg_image + ')'
						});
					}
				});
			
			$this.find('.c-ip-hotspot-carousel__image', $container).each(function () {
				var $this = $(this);
				var isLoaded = this.complete && this.naturalHeight !== 0;
				if ($this.attr('loading') === 'lazy' && !isLoaded) {
					$this.on('load', function (e) {
						ideapark_init_hotspot_points($(this).closest('.c-ip-hotspot-carousel__image-wrap'));
					});
				} else {
					ideapark_init_hotspot_points($(this).closest('.c-ip-hotspot-carousel__image-wrap'));
				}
			});
			var $first_image = $('.c-ip-hotspot-carousel__image:first', $container);
			
			if ($first_image[0].complete && $first_image[0].naturalHeight !== 0) {
				$('.c-ip-hotspot-carousel__image[loading]', $container).removeAttr('loading');
				$this
					.addClass('owl-carousel')
					.addClass('init')
					.owlCarousel(params);
			} else {
				$first_image.on('load', function () {
					$('.c-ip-hotspot-carousel__image[loading]', $container).removeAttr('loading');
					$this
						.addClass('owl-carousel')
						.addClass('init')
						.owlCarousel(params);
				});
			}
			$('.js-carousel-point', $container).on('click', function (e) {
				if ($(window).width() <= 768) {
					var $popup_container = $('.js-hotspot-container', $container);
					var $popup = $('.js-hotspot-popup', $container);
					var $product = $('.c-ip-hotspot-carousel__point-popup', $(this));
					var $product_new = $product.clone();
					$product_new[0].className = '';
					$product_new.addClass('c-ip-hotspot-carousel__popup-mobile');
					$popup_container.html($product_new);
					$popup.trigger('ip-open');
				} else if ($(window).width() >= 1190) {
					document.location = $(this).find('a').first().attr('href');
				}
			});
			
		});
	};
	
	root.ideapark_init_hotspot_points = function ($containers) {
		$containers.each(function () {
			var $container = $(this);
			var width = $container.width();
			var height = $container.height();
			if (width <= 1 || height <= 1) {
				return;
			}
			$container.find('.c-ip-hotspot-carousel__point-popup--left,.c-ip-hotspot-carousel__point-popup--right,.c-ip-hotspot-carousel__point-popup--top').removeClass('c-ip-hotspot-carousel__point-popup--left c-ip-hotspot-carousel__point-popup--right c-ip-hotspot-carousel__point-popup--top');
			$container.find('.js-carousel-point').each(function () {
				var $this = $(this);
				var left_css = Math.round($this.data('left') * width / 100);
				var top_css = Math.round($this.data('top') * height / 100);
				$this.css({
					'left': left_css + 'px',
					'top' : top_css + 'px',
				});
				$this.addClass('init');
				var $popup = $this.find('.c-ip-hotspot-carousel__point-popup');
				var left = $popup.offset().left - $container.offset().left;
				var top = $popup.offset().top - $container.offset().top;
				var right = width - (left + $popup.outerWidth()) - 30;
				left -= 30;
				
				if (left < 0 && right >= 0) {
					$popup.addClass('c-ip-hotspot-carousel__point-popup--left');
				}
				if (right < 0 && left >= 0) {
					$popup.addClass('c-ip-hotspot-carousel__point-popup--right');
				}
				if (top < 0) {
					$popup.addClass('c-ip-hotspot-carousel__point-popup--top');
				}
			});
		});
	};
	
	root.ideapark_init_running_line = function () {
		$('.js-ip-running-line').each(function () {
			var $line = $(this);
			var $item = $line.find('.c-ip-running-line__content').first();
			var line_width = $item.outerWidth();
			var content_width = $line.innerWidth();
			var cnt = Math.ceil(content_width / line_width) + 2;
			var $new_line = $line.clone();
			$new_line.find('.c-ip-running-line__content:not(:first-child)').remove();
			for (var i = 0; i < cnt; i++) {
				$item.clone().appendTo($new_line);
			}
			$new_line.addClass('c-ip-running-line--active');
			$line.replaceWith($new_line);
			$line.addClass('c-ip-running-line--active');
		});
	};
	
	
})(jQuery, window);