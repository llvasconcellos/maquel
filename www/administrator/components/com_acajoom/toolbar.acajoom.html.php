<?php
defined('_JEXEC') OR defined('_VALID_MOS') OR die('...Direct Access to this location is not allowed...');
### Copyright (C) 2006-2009 Acajoom Services. All rights reserved.
### http://www.acajoom.com/license.php

if ( ACA_CMSTYPE ) {
	require_once( WPATH_ADMIN . 'toolbar.acajoom15.html.php' );
} else {
	require_once( WPATH_ADMIN . 'toolbar.acajoom10.html.php' );
}//endif

