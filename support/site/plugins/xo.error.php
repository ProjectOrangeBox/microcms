<?php
/*

	{{xo.error}}
	{{xo.error status=405}}
	{{xo.error status=404 msg="Oh Darn!"}}
	{{xo.error status=404 msg="Oh Darn!" template="_errors/ohno"}}

*/
$plugin['xo:error'] = function ($options) {
	if (isset($options['hash']['msg'])) {
		c()->data->set('error.msg', $options['hash']['msg']);
	}

	if (isset($options['hash']['status'])) {
		c()->response->setRespondsCode((int) $options['hash']['status']);
	}

	$template = (isset($options['hash']['template'])) ? $options['hash']['template'] : '_errors/error';

	c()->response->setTemplate($template);
};
