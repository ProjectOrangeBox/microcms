<?php
/*

	{{#xo:block}}
		do something to me! {{> _partials/example }}
	{{/xo:block}}

	This example actually does nothing but return the block content
*/

$plugin['xo:block'] = function ($options) {
	return $options['fn']($options['_this']); /* parse inter block content */
};
