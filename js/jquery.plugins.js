/*
 * jQuery history plugin
 *
 * sample page: http://www.mikage.to/jquery/jquery_history.html
 *
 * Copyright (c) 2006-2009 Taku Sano (Mikage Sawatari)
 * Licensed under the MIT License:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Modified by Lincoln Cooper to add Safari support and only call the callback once during initialization
 * for msie when no initial hash supplied.
 */


jQuery.extend({
	historyCurrentHash: undefined,
	historyCallback: undefined,
	historyIframeSrc: undefined,
	load_num:0,//载入次数当为零的时候保证只载入一次
	historyNeedIframe: jQuery.browser.msie && (jQuery.browser.version < 8 || document.documentMode < 8),

	historyInit: function(callback, src){
		jQuery.historyCallback = callback;
		if (src) jQuery.historyIframeSrc = src;
		//var current_hash = location.hash.replace(/\?.*$/, '');
		//var current_hash = location.hash;
		var current_hash = src;
		jQuery.historyCurrentHash = current_hash;
		if (jQuery.historyNeedIframe) {
			// To stop the callback firing twice during initilization if no hash present
			if (jQuery.historyCurrentHash == '') {
				jQuery.historyCurrentHash = '#';
			}

			// add hidden iframe for IE
			jQuery("body").prepend('<iframe id="jQuery_history" style="display: none;" src="javascript:false;"></iframe>');
			var ihistory = jQuery("#jQuery_history")[0];
			var iframe = ihistory.contentWindow.document;
			iframe.open();
			iframe.close();
			iframe.location.hash = current_hash;
		}
		else if (jQuery.browser.safari) {
			// etablish back/forward stacks
			jQuery.historyBackStack = [];
			jQuery.historyBackStack.length = history.length;
			jQuery.historyForwardStack = [];
			jQuery.lastHistoryLength = history.length;

			jQuery.isFirst = true;
		}
		if(current_hash){
			jQuery.historyCallback(current_hash.replace(/^#/, ''),true);
		}
		setInterval(jQuery.historyCheck, 100);
	},

	historyAddHistory: function(hash) {
		// This makes the looping function do something
		jQuery.historyBackStack.push(hash);

		jQuery.historyForwardStack.length = 0; // clear forwardStack (true click occured)
		this.isFirst = true;
	},

	historyCheck: function(){
		if (jQuery.historyNeedIframe) {
			// On IE, check for location.hash of iframe
			var ihistory = jQuery("#jQuery_history")[0];
			var iframe = ihistory.contentDocument || ihistory.contentWindow.document;
			//var current_hash = iframe.location.hash.replace(/\?.*$/, '');
			var current_hash = iframe.location.hash;
			if(current_hash != jQuery.historyCurrentHash) {
				location.hash = current_hash;
				jQuery.historyCurrentHash = current_hash;
				if( jQuery.load_num>0 )
				jQuery.historyCallback(current_hash.replace(/^#/, ''),true);

			}
		} else if (jQuery.browser.safari) {
			if(jQuery.lastHistoryLength == history.length && jQuery.historyBackStack.length > jQuery.lastHistoryLength) {
				jQuery.historyBackStack.shift();
			}
			if (!jQuery.dontCheck) {
				var historyDelta = history.length - jQuery.historyBackStack.length;
				jQuery.lastHistoryLength = history.length;

				if (historyDelta) { // back or forward button has been pushed
					jQuery.isFirst = false;
					if (historyDelta < 0) { // back button has been pushed
						// move items to forward stack
						for (var i = 0; i < Math.abs(historyDelta); i++) jQuery.historyForwardStack.unshift(jQuery.historyBackStack.pop());
					} else { // forward button has been pushed
						// move items to back stack
						for (var i = 0; i < historyDelta; i++) jQuery.historyBackStack.push(jQuery.historyForwardStack.shift());
					}
					var cachedHash = jQuery.historyBackStack[jQuery.historyBackStack.length - 1];
					if (cachedHash != undefined) {
						//jQuery.historyCurrentHash = location.hash.replace(/\?.*$/, '');
						jQuery.historyCurrentHash = location.hash;
						if( jQuery.load_num>0 )
						jQuery.historyCallback(cachedHash,true);
					}
				} else if (jQuery.historyBackStack[jQuery.historyBackStack.length - 1] == undefined && !jQuery.isFirst) {
					// back button has been pushed to beginning and URL already pointed to hash (e.g. a bookmark)
					// document.URL doesn't change in Safari
					if (location.hash) {
						var current_hash = location.hash;
						if( jQuery.load_num>0 )
						jQuery.historyCallback(location.hash.replace(/^#/, ''),true);
					} else {
						var current_hash = '';
						if( jQuery.load_num>0 )
						jQuery.historyCallback('',true);
					}
					jQuery.isFirst = true;
				}
			}
		} else {
			// otherwise, check for location.hash
			//var current_hash = location.hash.replace(/\?.*$/, '');
			//alert(location.hash);
			var current_hash = location.hash;
			//location.hash = jQuery.historyCurrentHash;
			//alert(current_hash+"+"+jQuery.historyCurrentHash);
			if(current_hash != jQuery.historyCurrentHash) {
				jQuery.historyCurrentHash = current_hash;
				if( jQuery.load_num>0 ){
					jQuery.historyCallback(current_hash.replace(/^#/, ''),true);
				}
			}
		}
		jQuery.load_num++;
	},
	historyLoad: function(hash){
		var newhash;
		//hash = decodeURIComponent(hash.replace(/\?.*$/, ''));
		hash = decodeURIComponent(hash);
		if (jQuery.browser.safari) {
			newhash = hash;
		}
		else {
			newhash = '#' + hash;
			location.hash = newhash;
		}
		jQuery.historyCurrentHash = newhash;

		if (jQuery.historyNeedIframe) {
			var ihistory = jQuery("#jQuery_history")[0];
			var iframe = ihistory.contentWindow.document;
			iframe.open();
			iframe.close();
			iframe.location.hash = newhash;
			jQuery.lastHistoryLength = history.length;
			jQuery.historyCallback(hash,false);
		}
		else if (jQuery.browser.safari) {
			jQuery.dontCheck = true;
			// Manually keep track of the history values for Safari
			this.historyAddHistory(hash);

			// Wait a while before allowing checking so that Safari has time to update the "history" object
			// correctly (otherwise the check loop would detect a false change in hash).
			var fn = function() {jQuery.dontCheck = false;};
			window.setTimeout(fn, 100);
			jQuery.historyCallback(hash,false);
			// N.B. "location.hash=" must be the last line of code for Safari as execution stops afterwards.
			//      By explicitly using the "location.hash" command (instead of using a variable set to "location.hash") the
			//      URL in the browser and the "history" object are both updated correctly.
			location.hash = newhash;
		}
		else {
		  jQuery.historyCallback(hash,false);
		}
	}
});


/*
 * jQuery Form Plugin
 * version: 2.32 (17-SEP-2009)
 * @requires jQuery v1.2.2 or later
 *
 * Examples and documentation at: http://malsup.com/jquery/form/
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
;(function($) {

/*
    Usage Note:
    -----------
    Do not use both ajaxSubmit and ajaxForm on the same form.  These
    functions are intended to be exclusive.  Use ajaxSubmit if you want
    to bind your own submit handler to the form.  For example,

    $(document).ready(function() {
        $('#myForm').bind('submit', function() {
            $(this).ajaxSubmit({
                target: '#output'
            });
            return false; // <-- important!
        });
    });

    Use ajaxForm when you want the plugin to manage all the event binding
    for you.  For example,

    $(document).ready(function() {
        $('#myForm').ajaxForm({
            target: '#output'
        });
    });

    When using ajaxForm, the ajaxSubmit function will be invoked for you
    at the appropriate time.
*/

/**
 * ajaxSubmit() provides a mechanism for immediately submitting
 * an HTML form using AJAX.
 */
$.fn.ajaxSubmit = function(options) {
    // fast fail if nothing selected (http://dev.jquery.com/ticket/2752)
    if (!this.length) {
        log('ajaxSubmit: skipping submit process - no element selected');
        return this;
    }

    if (typeof options == 'function')
        options = { success: options };

    var url = $.trim(this.attr('action'));
    if (url) {
	    // clean url (don't include hash vaue)
	    url = (url.match(/^([^#]+)/)||[])[1];
   	}
   	url = url || window.location.href || '';

    options = $.extend({
        url:  url,
        type: this.attr('method') || 'GET'
    }, options || {});

    // hook for manipulating the form data before it is extracted;
    // convenient for use with rich editors like tinyMCE or FCKEditor
    var veto = {};
    this.trigger('form-pre-serialize', [this, options, veto]);
    if (veto.veto) {
        log('ajaxSubmit: submit vetoed via form-pre-serialize trigger');
        return this;
    }

    // provide opportunity to alter form data before it is serialized
    if (options.beforeSerialize && options.beforeSerialize(this, options) === false) {
        log('ajaxSubmit: submit aborted via beforeSerialize callback');
        return this;
    }

    var a = this.formToArray(options.semantic);
    if (options.data) {
        options.extraData = options.data;
        for (var n in options.data) {
          if(options.data[n] instanceof Array) {
            for (var k in options.data[n])
              a.push( { name: n, value: options.data[n][k] } );
          }
          else
             a.push( { name: n, value: options.data[n] } );
        }
    }

    // give pre-submit callback an opportunity to abort the submit
    if (options.beforeSubmit && options.beforeSubmit(a, this, options) === false) {
        log('ajaxSubmit: submit aborted via beforeSubmit callback');
        return this;
    }

    // fire vetoable 'validate' event
    this.trigger('form-submit-validate', [a, this, options, veto]);
    if (veto.veto) {
        log('ajaxSubmit: submit vetoed via form-submit-validate trigger');
        return this;
    }

    var q = $.param(a);

    if (options.type.toUpperCase() == 'GET') {
        options.url += (options.url.indexOf('?') >= 0 ? '&' : '?') + q;
        options.data = null;  // data is null for 'get'
    }
    else
        options.data = q; // data is the query string for 'post'

    var $form = this, callbacks = [];
    if (options.resetForm) callbacks.push(function() { $form.resetForm(); });
    if (options.clearForm) callbacks.push(function() { $form.clearForm(); });

    // perform a load on the target only if dataType is not provided
    if (!options.dataType && options.target) {
        var oldSuccess = options.success || function(){};
        callbacks.push(function(data) {
            $(options.target).html(data).each(oldSuccess, arguments);
        });
    }
    else if (options.success)
        callbacks.push(options.success);

    options.success = function(data, status) {
        for (var i=0, max=callbacks.length; i < max; i++)
            callbacks[i].apply(options, [data, status, $form]);
    };

    // are there files to upload?
    var files = $('input:file', this).fieldValue();
    var found = false;
    for (var j=0; j < files.length; j++)
        if (files[j])
            found = true;

	var multipart = false;
//	var mp = 'multipart/form-data';
//	multipart = ($form.attr('enctype') == mp || $form.attr('encoding') == mp);

    // options.iframe allows user to force iframe mode
   if (options.iframe || found || multipart) {
       // hack to fix Safari hang (thanks to Tim Molendijk for this)
       // see:  http://groups.google.com/group/jquery-dev/browse_thread/thread/36395b7ab510dd5d
       if (options.closeKeepAlive)
           $.get(options.closeKeepAlive, fileUpload);
       else
           fileUpload();
       }
   else
       $.ajax(options);

    // fire 'notify' event
    this.trigger('form-submit-notify', [this, options]);
    return this;


    // private function for handling file uploads (hat tip to YAHOO!)
    function fileUpload() {
        var form = $form[0];

        if ($(':input[name=submit]', form).length) {
            alert('Error: Form elements must not be named "submit".');
            return;
        }

        var opts = $.extend({}, $.ajaxSettings, options);
		var s = $.extend(true, {}, $.extend(true, {}, $.ajaxSettings), opts);

        var id = 'jqFormIO' + (new Date().getTime());
        var $io = $('<iframe id="' + id + '" name="' + id + '" src="about:blank" />');
        var io = $io[0];

        $io.css({ position: 'absolute', top: '-1000px', left: '-1000px' });

        var xhr = { // mock object
            aborted: 0,
            responseText: null,
            responseXML: null,
            status: 0,
            statusText: 'n/a',
            getAllResponseHeaders: function() {},
            getResponseHeader: function() {},
            setRequestHeader: function() {},
            abort: function() {
                this.aborted = 1;
                $io.attr('src','about:blank'); // abort op in progress
            }
        };

        var g = opts.global;
        // trigger ajax global events so that activity/block indicators work like normal
        if (g && ! $.active++) $.event.trigger("ajaxStart");
        if (g) $.event.trigger("ajaxSend", [xhr, opts]);

		if (s.beforeSend && s.beforeSend(xhr, s) === false) {
			s.global && $.active--;
			return;
        }
        if (xhr.aborted)
            return;

        var cbInvoked = 0;
        var timedOut = 0;

        // add submitting element to data if we know it
        var sub = form.clk;
        if (sub) {
            var n = sub.name;
            if (n && !sub.disabled) {
                options.extraData = options.extraData || {};
                options.extraData[n] = sub.value;
                if (sub.type == "image") {
                    options.extraData[name+'.x'] = form.clk_x;
                    options.extraData[name+'.y'] = form.clk_y;
                }
            }
        }

        // take a breath so that pending repaints get some cpu time before the upload starts
        setTimeout(function() {
            // make sure form attrs are set
            var t = $form.attr('target'), a = $form.attr('action');

			// update form attrs in IE friendly way
			form.setAttribute('target',id);
			if (form.getAttribute('method') != 'POST')
				form.setAttribute('method', 'POST');
			if (form.getAttribute('action') != opts.url)
				form.setAttribute('action', opts.url);

            // ie borks in some cases when setting encoding
            if (! options.skipEncodingOverride) {
                $form.attr({
                    encoding: 'multipart/form-data',
                    enctype:  'multipart/form-data'
                });
            }

            // support timout
            if (opts.timeout)
                setTimeout(function() { timedOut = true; cb(); }, opts.timeout);

            // add "extra" data to form if provided in options
            var extraInputs = [];
            try {
                if (options.extraData)
                    for (var n in options.extraData)
                        extraInputs.push(
                            $('<input type="hidden" name="'+n+'" value="'+options.extraData[n]+'" />')
                                .appendTo(form)[0]);

                // add iframe to doc and submit the form
                $io.appendTo('body');
                io.attachEvent ? io.attachEvent('onload', cb) : io.addEventListener('load', cb, false);
                form.submit();
            }
            finally {
                // reset attrs and remove "extra" input elements
				form.setAttribute('action',a);
                t ? form.setAttribute('target', t) : $form.removeAttr('target');
                $(extraInputs).remove();
            }
        }, 10);

        var domCheckCount = 50;

        function cb() {
            if (cbInvoked++) return;

            io.detachEvent ? io.detachEvent('onload', cb) : io.removeEventListener('load', cb, false);

            var ok = true;
            try {
                if (timedOut) throw 'timeout';
                // extract the server response from the iframe
                var data, doc;

                doc = io.contentWindow ? io.contentWindow.document : io.contentDocument ? io.contentDocument : io.document;

				var isXml = opts.dataType == 'xml' || doc.XMLDocument || $.isXMLDoc(doc);
				log('isXml='+isXml);
                if (!isXml && (doc.body == null || doc.body.innerHTML == '')) {
                 	if (--domCheckCount) {
	                    // in some browsers (Opera) the iframe DOM is not always traversable when
	                    // the onload callback fires, so we loop a bit to accommodate
	                    cbInvoked = 0;
	                    setTimeout(cb, 100);
	                    return;
	                }
	                log('Could not access iframe DOM after 50 tries.');
	                return;
	            }

                xhr.responseText = doc.body ? doc.body.innerHTML : null;
                xhr.responseXML = doc.XMLDocument ? doc.XMLDocument : doc;
                xhr.getResponseHeader = function(header){
                    var headers = {'content-type': opts.dataType};
                    return headers[header];
                };

                if (opts.dataType == 'json' || opts.dataType == 'script') {
                    var ta = doc.getElementsByTagName('textarea')[0];
                    xhr.responseText = ta ? ta.value : xhr.responseText;
                }
                else if (opts.dataType == 'xml' && !xhr.responseXML && xhr.responseText != null) {
                    xhr.responseXML = toXml(xhr.responseText);
                }
                data = $.httpData(xhr, opts.dataType);
            }
            catch(e){
                ok = false;
                $.handleError(opts, xhr, 'error', e);
            }

            // ordering of these callbacks/triggers is odd, but that's how $.ajax does it
            if (ok) {
                opts.success(data, 'success');
                if (g) $.event.trigger("ajaxSuccess", [xhr, opts]);
            }
            if (g) $.event.trigger("ajaxComplete", [xhr, opts]);
            if (g && ! --$.active) $.event.trigger("ajaxStop");
            if (opts.complete) opts.complete(xhr, ok ? 'success' : 'error');

            // clean up
            setTimeout(function() {
                $io.remove();
                xhr.responseXML = null;
            }, 100);
        };

        function toXml(s, doc) {
            if (window.ActiveXObject) {
                doc = new ActiveXObject('Microsoft.XMLDOM');
                doc.async = 'false';
                doc.loadXML(s);
            }
            else
                doc = (new DOMParser()).parseFromString(s, 'text/xml');
            return (doc && doc.documentElement && doc.documentElement.tagName != 'parsererror') ? doc : null;
        };
    };
};

/**
 * ajaxForm() provides a mechanism for fully automating form submission.
 *
 * The advantages of using this method instead of ajaxSubmit() are:
 *
 * 1: This method will include coordinates for <input type="image" /> elements (if the element
 *    is used to submit the form).
 * 2. This method will include the submit element's name/value data (for the element that was
 *    used to submit the form).
 * 3. This method binds the submit() method to the form for you.
 *
 * The options argument for ajaxForm works exactly as it does for ajaxSubmit.  ajaxForm merely
 * passes the options argument along after properly binding events for submit elements and
 * the form itself.
 */
$.fn.ajaxForm = function(options) {
    return this.ajaxFormUnbind().bind('submit.form-plugin',function() {
        $(this).ajaxSubmit(options);
        return false;
    }).each(function() {
        // store options in hash
        $(":submit,input:image", this).bind('click.form-plugin',function(e) {
            var form = this.form;
            form.clk = this;
            if (this.type == 'image') {
                if (e.offsetX != undefined) {
                    form.clk_x = e.offsetX;
                    form.clk_y = e.offsetY;
                } else if (typeof $.fn.offset == 'function') { // try to use dimensions plugin
                    var offset = $(this).offset();
                    form.clk_x = e.pageX - offset.left;
                    form.clk_y = e.pageY - offset.top;
                } else {
                    form.clk_x = e.pageX - this.offsetLeft;
                    form.clk_y = e.pageY - this.offsetTop;
                }
            }
            // clear form vars
            setTimeout(function() { form.clk = form.clk_x = form.clk_y = null; }, 10);
        });
    });
};

// ajaxFormUnbind unbinds the event handlers that were bound by ajaxForm
$.fn.ajaxFormUnbind = function() {
    this.unbind('submit.form-plugin');
    return this.each(function() {
        $(":submit,input:image", this).unbind('click.form-plugin');
    });

};

/**
 * formToArray() gathers form element data into an array of objects that can
 * be passed to any of the following ajax functions: $.get, $.post, or load.
 * Each object in the array has both a 'name' and 'value' property.  An example of
 * an array for a simple login form might be:
 *
 * [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
 *
 * It is this array that is passed to pre-submit callback functions provided to the
 * ajaxSubmit() and ajaxForm() methods.
 */
$.fn.formToArray = function(semantic) {
    var a = [];
    if (this.length == 0) return a;

    var form = this[0];
    var els = semantic ? form.getElementsByTagName('*') : form.elements;
    if (!els) return a;
    for(var i=0, max=els.length; i < max; i++) {
        var el = els[i];
        var n = el.name;
        if (!n) continue;

        if (semantic && form.clk && el.type == "image") {
            // handle image inputs on the fly when semantic == true
            if(!el.disabled && form.clk == el) {
            	a.push({name: n, value: $(el).val()});
                a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
            }
            continue;
        }

        var v = $.fieldValue(el, true);
        if (v && v.constructor == Array) {
            for(var j=0, jmax=v.length; j < jmax; j++)
                a.push({name: n, value: v[j]});
        }
        else if (v !== null && typeof v != 'undefined')
            a.push({name: n, value: v});
    }

    if (!semantic && form.clk) {
        // input type=='image' are not found in elements array! handle it here
        var $input = $(form.clk), input = $input[0], n = input.name;
        if (n && !input.disabled && input.type == 'image') {
        	a.push({name: n, value: $input.val()});
            a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
        }
    }
    return a;
};

/**
 * Serializes form data into a 'submittable' string. This method will return a string
 * in the format: name1=value1&amp;name2=value2
 */
$.fn.formSerialize = function(semantic) {
    //hand off to jQuery.param for proper encoding
    return $.param(this.formToArray(semantic));
};

/**
 * Serializes all field elements in the jQuery object into a query string.
 * This method will return a string in the format: name1=value1&amp;name2=value2
 */
$.fn.fieldSerialize = function(successful) {
    var a = [];
    this.each(function() {
        var n = this.name;
        if (!n) return;
        var v = $.fieldValue(this, successful);
        if (v && v.constructor == Array) {
            for (var i=0,max=v.length; i < max; i++)
                a.push({name: n, value: v[i]});
        }
        else if (v !== null && typeof v != 'undefined')
            a.push({name: this.name, value: v});
    });
    //hand off to jQuery.param for proper encoding
    return $.param(a);
};

/**
 * Returns the value(s) of the element in the matched set.  For example, consider the following form:
 *
 *  <form><fieldset>
 *      <input name="A" type="text" />
 *      <input name="A" type="text" />
 *      <input name="B" type="checkbox" value="B1" />
 *      <input name="B" type="checkbox" value="B2"/>
 *      <input name="C" type="radio" value="C1" />
 *      <input name="C" type="radio" value="C2" />
 *  </fieldset></form>
 *
 *  var v = $(':text').fieldValue();
 *  // if no values are entered into the text inputs
 *  v == ['','']
 *  // if values entered into the text inputs are 'foo' and 'bar'
 *  v == ['foo','bar']
 *
 *  var v = $(':checkbox').fieldValue();
 *  // if neither checkbox is checked
 *  v === undefined
 *  // if both checkboxes are checked
 *  v == ['B1', 'B2']
 *
 *  var v = $(':radio').fieldValue();
 *  // if neither radio is checked
 *  v === undefined
 *  // if first radio is checked
 *  v == ['C1']
 *
 * The successful argument controls whether or not the field element must be 'successful'
 * (per http://www.w3.org/TR/html4/interact/forms.html#successful-controls).
 * The default value of the successful argument is true.  If this value is false the value(s)
 * for each element is returned.
 *
 * Note: This method *always* returns an array.  If no valid value can be determined the
 *       array will be empty, otherwise it will contain one or more values.
 */
$.fn.fieldValue = function(successful) {
    for (var val=[], i=0, max=this.length; i < max; i++) {
        var el = this[i];
        var v = $.fieldValue(el, successful);
        if (v === null || typeof v == 'undefined' || (v.constructor == Array && !v.length))
            continue;
        v.constructor == Array ? $.merge(val, v) : val.push(v);
    }
    return val;
};

/**
 * Returns the value of the field element.
 */
$.fieldValue = function(el, successful) {
    var n = el.name, t = el.type, tag = el.tagName.toLowerCase();
    if (typeof successful == 'undefined') successful = true;

    if (successful && (!n || el.disabled || t == 'reset' || t == 'button' ||
        (t == 'checkbox' || t == 'radio') && !el.checked ||
        (t == 'submit' || t == 'image') && el.form && el.form.clk != el ||
        tag == 'select' && el.selectedIndex == -1))
            return null;

    if (tag == 'select') {
        var index = el.selectedIndex;
        if (index < 0) return null;
        var a = [], ops = el.options;
        var one = (t == 'select-one');
        var max = (one ? index+1 : ops.length);
        for(var i=(one ? index : 0); i < max; i++) {
            var op = ops[i];
            if (op.selected) {
				var v = op.value;
				if (!v) // extra pain for IE...
                	v = (op.attributes && op.attributes['value'] && !(op.attributes['value'].specified)) ? op.text : op.value;
                if (one) return v;
                a.push(v);
            }
        }
        return a;
    }
    return el.value;
};

/**
 * Clears the form data.  Takes the following actions on the form's input fields:
 *  - input text fields will have their 'value' property set to the empty string
 *  - select elements will have their 'selectedIndex' property set to -1
 *  - checkbox and radio inputs will have their 'checked' property set to false
 *  - inputs of type submit, button, reset, and hidden will *not* be effected
 *  - button elements will *not* be effected
 */
$.fn.clearForm = function() {
    return this.each(function() {
        $('input,select,textarea', this).clearFields();
    });
};

/**
 * Clears the selected form elements.
 */
$.fn.clearFields = $.fn.clearInputs = function() {
    return this.each(function() {
        var t = this.type, tag = this.tagName.toLowerCase();
        if (t == 'text' || t == 'password' || tag == 'textarea')
            this.value = '';
        else if (t == 'checkbox' || t == 'radio')
            this.checked = false;
        else if (tag == 'select')
            this.selectedIndex = -1;
    });
};

/**
 * Resets the form data.  Causes all form elements to be reset to their original value.
 */
$.fn.resetForm = function() {
    return this.each(function() {
        // guard against an input with the name of 'reset'
        // note that IE reports the reset function as an 'object'
        if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType))
            this.reset();
    });
};

/**
 * Enables or disables any matching elements.
 */
$.fn.enable = function(b) {
    if (b == undefined) b = true;
    return this.each(function() {
        this.disabled = !b;
    });
};

/**
 * Checks/unchecks any matching checkboxes or radio buttons and
 * selects/deselects and matching option elements.
 */
$.fn.selected = function(select) {
    if (select == undefined) select = true;
    return this.each(function() {
        var t = this.type;
        if (t == 'checkbox' || t == 'radio')
            this.checked = select;
        else if (this.tagName.toLowerCase() == 'option') {
            var $sel = $(this).parent('select');
            if (select && $sel[0] && $sel[0].type == 'select-one') {
                // deselect all other options
                $sel.find('option').selected(false);
            }
            this.selected = select;
        }
    });
};

// helper fn for console logging
// set $.fn.ajaxSubmit.debug to true to enable debug logging
function log() {
    if ($.fn.ajaxSubmit.debug && window.console && window.console.log)
        window.console.log('[jquery.form] ' + Array.prototype.join.call(arguments,''));
};

})(jQuery);




/*
* jQuery pager plugin
* Version 1.0 (12/22/2008)
* @requires jQuery v1.2.6 or later
*
* Example at: http://jonpauldavies.github.com/JQuery/Pager/PagerDemo.html
*
* Copyright (c) 2008-2009 Jon Paul Davies
* Dual licensed under the MIT and GPL licenses:
* http://www.opensource.org/licenses/mit-license.php
* http://www.gnu.org/licenses/gpl.html
*
* Read the related blog post and contact the author at http://www.j-dee.com/2008/12/22/jquery-pager-plugin/
*
* This version is far from perfect and doesn't manage it's own state, therefore contributions are more than welcome!
*
* Usage: .pager({ pagenumber: 1, pagecount: 15, buttonClickCallback: PagerClickTest });
*
* Where pagenumber is the visible page number
*       pagecount is the total number of pages to display
*       buttonClickCallback is the method to fire when a pager button is clicked.
*
* buttonClickCallback signiture is PagerClickTest = function(pageclickednumber)
* Where pageclickednumber is the number of the page clicked in the control.
*
* The included Pager.CSS file is a dependancy but can obviously tweaked to your wishes
* Tested in IE6 IE7 Firefox & Safari. Any browser strangeness, please report.
*/
;(function($) {

$.extend($.fn, {
    pager: function(options) {
        var opts = $.extend({}, $.fn.pager.defaults, options);

        return this.each(function() {
			//var target = options.linktarget?options.linktarget:'';
			var href = options.href?options.href:'';
			var rel = options.rel?options.rel:'';
        // empty out the destination element and then render out the pager with the supplied options
            $(this).empty().append($.fn.renderpager(parseInt(options.pagenumber), parseInt(options.pagecount), options.buttonClickCallback,href,rel));

            // specify correct cursor activity
            $('.pages li').mouseover(function() { document.body.style.cursor = "pointer"; }).mouseout(function() { document.body.style.cursor = "auto"; });
        });

    },
    // render and return the pager with the supplied options
    renderpager: function(pagenumber, pagecount, buttonClickCallback,href,rel) {
        // setup $pager to hold render
        var $pager = $('<ul class="pages"></ul>');
        // add in the previous and next buttons
        $pager.append($.fn.renderButton('first', pagenumber, pagecount, buttonClickCallback,href,rel)).append($.fn.renderButton('prev', pagenumber, pagecount, buttonClickCallback,href,rel));

        // pager currently only handles 10 viewable pages ( could be easily parameterized, maybe in next version ) so handle edge cases
        var startPoint = 1;
        var endPoint = 9;

        if (pagenumber > 4) {
            startPoint = pagenumber - 4;
            endPoint = pagenumber + 4;
        }

        if (endPoint > pagecount) {
            startPoint = pagecount - 8;
            endPoint = pagecount;
        }

        if (startPoint < 1) {
            startPoint = 1;
        }
        // loop thru visible pages and render buttons
		if(!href){
			href = $.fn.trimUrl();
		}
		if(!rel){
			rel = $.fn.trimUrl();
		}
        for (var page = startPoint; page <= endPoint; page++) {
            var currentButton = $('<a class="page-number" onClick="return false" href="'+href+'" rel="'+rel+'&page='+page+'">' + (page) + '</a>');
			//currentButton.attr("target",target);
            page == pagenumber ? currentButton.addClass('pgCurrent') : currentButton.click(function() { buttonClickCallback(this.firstChild.data,this); });
            currentButton.appendTo($pager);
        }

        // render in the next and last buttons before returning the whole rendered control back.
        $pager.append($.fn.renderButton('next', pagenumber, pagecount, buttonClickCallback,href,rel)).append($.fn.renderButton('last', pagenumber, pagecount, buttonClickCallback,href,rel));

        return $pager;
    },
	trimUrl: function(){
		var url = location.hash;
		if(/page=/.test(url)){
			url = url.replace(/&page=\d+/, '');
		}
/*		if(/#/.test(url)){
			url = url.replace(/#/, '');
		}*/
		return url;
	},
    // renders and returns a 'specialized' button, ie 'next', 'previous' etc. rather than a page number button
    renderButton: function(buttonLabel, pagenumber, pagecount, buttonClickCallback,href,rel) {

		if(!href){
			href = $.fn.trimUrl();
		}
		if(!rel){
			rel = $.fn.trimUrl();
		}

        var destPage = 1;

        // work out destination page for required button type
        switch (buttonLabel) {
            case "first":
                destPage = 1;
                break;
            case "prev":
                destPage = pagenumber - 1;
                break;
            case "next":
                destPage = pagenumber + 1;
                break;
            case "last":
                destPage = pagecount;
                break;
        }

		var $Button = $('<a class="pgNext" onClick="return false" href="'+href+'" rel="'+rel+'&page='+destPage+'">' + $.fn.displayStr(buttonLabel) + '</a>');
		//$Button.attr("target",target);

        // disable and 'grey' out buttons if not needed.
        if (buttonLabel == "first" || buttonLabel == "prev") {
            pagenumber <= 1 ? $Button.addClass('pgEmpty') : $Button.click(function() { buttonClickCallback(destPage,this); });
        }
        else {
            pagenumber >= pagecount ? $Button.addClass('pgEmpty') : $Button.click(function() { buttonClickCallback(destPage,this); });
        }

        return $Button;
    },
	displayStr: function( buttonLabel ){
		switch (buttonLabel) {
            case "first":
                return '首页';
                break;
            case "prev":
                return '上一页';
                break;
            case "next":
                return '下一页';
                break;
            case "last":
                return '末页';
                break;
        }
	}
});
    // pager defaults. hardly worth bothering with in this case but used as placeholder for expansion in the next version
$.fn.pager.defaults = {
	pagenumber: 1,
	pagecount: 1
};


})(jQuery);
