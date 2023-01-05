'use strict';

( function ( $, undefined ) {
	var WcPinLoader = {
		$wrapper: {},
		$container: {},
		conter: 0,

		init: function () {
			var _this = this;
		},
	};

	$( function () {
		WcPinLoader.init();
	});

	window.WcPinLoader = WcPinLoader;


	/**
	 * Form serialization helper
	 */
	$.fn.WcPinLoaderSerializeObject = function() {
		var o = {};
		var a = this.serializeArray();
		$.each( a, function() {
			if ( o[this.name] !== undefined ) {
				if ( !o[this.name].push ) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push( this.value || '' );
			} else {
				o[this.name] = this.value || '';
			}
		} );
		return o;
	};
})( jQuery );
