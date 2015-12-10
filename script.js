$(document).ready(function() {

    // run test on initial page load
    checkSize();

    // run test on resize of the window
    $(window).resize(checkSize);
    var activeClasses = $('.s-lg-tabs-side li.active').length;
    var contentHeight = $('.s-lg-tab-content').height();
    var navHeight = $('.s-lg-tabs-side').height();
    
    if(contentHeight > navHeight) {
    $('#s-lg-tabs-container .nav-stacked').affix({
    offset: {
      top: 205
    , bottom: function () {
        return (this.bottom = $('.footer').outerHeight(true))
      }
    }
  })
  }
  
  if(activeClasses > 1) {
    $('.s-lg-tabs-side ul li').removeClass('active');
    $('.s-lg-tabs-side ul li:first').addClass('active');
  }
  
  $('#s-lib-bc-customer a').text('Harvard Library');
  $('#s-lib-bc-site a').text('Research Guides');
  
  $( "#s-lg-guide-desc-container" ).on( "click", ".s-lg-tabs-side-toggle", function() {
    $('.s-lg-tabs-side').slideToggle();
    });
  
  $('.s-lib-profile-container').closest('.s-lib-box-container').parent().remove();
  $('body').windowspy({ target: '.s-lg-tabs-side' });
  $('#s-lib-bc').prependTo('#s-lib-footer-public');
  var prettyUrl = $('#s-lg-guide-header-url .s-lg-text-greyout').text();
  $('<p><a href="' + prettyUrl + '">' + prettyUrl + '</a></p>').prependTo('#s-lg-guide-description').addClass('s-lg-text-greyout');
  var updated_on = $('meta[name="DC.Date.Modified"]').attr("content");
  var creator = $('meta[name="DC.Creator"]').attr("content");
  profileBox(creator);
  var additionalCreator = $('meta[name="DC.Additional.Creator"]').attr("content");
  profileBox(additionalCreator)
  
  function profileBox(creator) {
    $.getJSON( config.apiUrl, function( data ) {
    $.each( data, function( key, val ) {
        if((val.first_name + ' ' + val.last_name) === creator) {
            if(val.profile.image) {
                var image_path = config.imageBase + val.profile.account_id + '/profiles/' + val.profile.id + '/' + val.profile.image.file;
            }
            else {
                var image_path = config.imageBackground;
            }
            var email = val.email;
            var profile = val.profile.url;
            $('#s-lg-guide-desc-container').append('<div class="s-lg-guide-footer-byline"><a href="' + profile + '"><img src="' + image_path + '"></a><p><span class="s-lg-guide-byline-updated">Last updated ' + updated_on + '</span><a href="' + profile + '">' + creator + '</a> | <a href="mailto:' + email + '"><i class="fa fa-envelope" title="Email"></i> Email</a></p></div>');
        }
    }); 
  });
  }
  
  // Replace search box
  //$('#s-lg-guide-search-form').attr('action', 'https://cse.google.com/cse/publicurl');
  //$( "input[name='guide_id']").attr('name', 'cx').attr('value', '005262195415746452776:ekldow64xay');
  //$('#s-lg-guide-search-terms').attr('placeholder', 'Search Research Guides');
  
});

//Function to the css rule
function checkSize(){
    if ($(".s-lg-tabs-side").css("display") == "none" ){
        $('.s-lg-tabs-side > ul > li').clone().prependTo('.guide-menu-container');
        $('.guide-menu-container li').removeClass('active');
    }
}

+function ($) {
  'use strict';

  // SCROLLSPY CLASS DEFINITION
  // ==========================

  function WindowSpy(element, options) {
    var href
    var process  = $.proxy(this.process, this)

    this.$element       = $(element).is('body') ? $(window) : $(element)
    this.$body          = $('body')
    this.$scrollElement = this.$element.on('scroll.bs.windowspy', process)
    this.options        = $.extend({}, WindowSpy.DEFAULTS, options)
    this.selector       = (this.options.target
      || ((href = $(element).attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      || '') + ' .nav li > a'
    this.offsets        = $([])
    this.targets        = $([])
    this.activeTarget   = null

    this.refresh()
    this.process()
  }

  WindowSpy.DEFAULTS = {
    offset: 10
  }

  WindowSpy.prototype.refresh = function () {
    var offsetMethod = this.$element[0] == window ? 'offset' : 'position'

    this.offsets = $([])
    this.targets = $([])

    var self     = this

    this.$body
      .find(this.selector)
      .map(function () {
        var $el   = $(this)
        var href  = $el.data('target') || $el.attr('href')
        var hrefsplit = href.split('#')
        href = '#' + hrefsplit[1] 
        var $href = /^#./.test(href) && $(href)

        return ($href
          && $href.length
          && $href.is(':visible')
          && [[ $href[offsetMethod]().top + (!$.isWindow(self.$scrollElement.get(0)) && self.$scrollElement.scrollTop()), href ]]) || null
      })
      .sort(function (a, b) { return a[0] - b[0] })
      .each(function () {
        self.offsets.push(this[0])
        self.targets.push(this[1])
      })
  }

  WindowSpy.prototype.process = function () {
    var scrollTop    = this.$scrollElement.scrollTop() + this.options.offset
    var scrollHeight = this.$scrollElement[0].scrollHeight || Math.max(this.$body[0].scrollHeight, document.documentElement.scrollHeight)
    var maxScroll    = scrollHeight - this.$scrollElement.height()
    var offsets      = this.offsets
    var targets      = this.targets
    var activeTarget = this.activeTarget
    var i

    if (scrollTop >= maxScroll) {
      return activeTarget != (i = targets.last()[0]) && this.activate(i)
    }

    if (activeTarget && scrollTop <= offsets[0]) {
      return activeTarget != (i = targets[0]) && this.activate(i)
    }

    for (i = offsets.length; i--;) {
      activeTarget != targets[i]
        && scrollTop >= offsets[i]
        && (!offsets[i + 1] || scrollTop <= offsets[i + 1])
        && this.activate( targets[i] )
    }
  }

  WindowSpy.prototype.activate = function (target) {
    this.activeTarget = target

    $(this.selector)
      .parentsUntil(this.options.target, '.active')
      .removeClass('active')

    var selector = this.selector +
        '[data-target="' + target + '"],' +
        this.selector + '[href$="' + target + '"]'

    var active = $(selector)
      .parents('li')
      .addClass('active')

    if (active.parent('.dropdown-menu').length) {
      active = active
        .closest('li.dropdown')
        .addClass('active')
    }

    active.trigger('activate.bs.windowspy')
  }


  // SCROLLSPY PLUGIN DEFINITION
  // ===========================

  var old = $.fn.windowspy

  $.fn.windowspy = function (option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.windowspy')
      var options = typeof option == 'object' && option

      if (!data) $this.data('bs.windowspy', (data = new WindowSpy(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.windowspy.Constructor = WindowSpy


  // SCROLLSPY NO CONFLICT
  // =====================

  $.fn.windowspy.noConflict = function () {
    $.fn.windowspy = old
    return this
  }


  // SCROLLSPY DATA-API
  // ==================

  $(window).on('load.bs.windowspy.data-api', function () {
    $('[data-spy="scroll"]').each(function () {
      var $spy = $(this)
      $spy.windowspy($spy.data())
    })
  })

}(jQuery);
