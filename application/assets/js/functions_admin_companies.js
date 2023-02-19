function verify_acp_request(mode)
{
//	console.log(jQuery('#fileuploader_iframe').contents().find('.files .template-download .preview').html());
	
	var haserror = false;
	jQuery('#vehicle_registration').removeClass('error');
	jQuery('#vehicle_number').removeClass('error');
	jQuery('#vehicle_make').removeClass('error');
	jQuery('#vehicle_model').removeClass('error');
	jQuery('#vehicle_year').removeClass('error');
	jQuery('#request_date').removeClass('error');
	jQuery('#vehicle_value').removeClass('error');
	jQuery('#user_account').removeClass('error');
	jQuery('#usedfor').removeClass('error');
	jQuery('#storeid').removeClass('error');

	
	if (jQuery('#cid').val().length == 0)
	{
		haserror = true;
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please Select Category'});
		return false;
	}

	if (jQuery('#storeid').val() <= 0) {
		haserror = true;
		jQuery('#storeid').addClass('error');
		jQuery.growl.error({ size: 'large', duration: 4000, title: phrase['_error'], message: 'Please Select A Store' });
		
		return false;
	}
	
	if (jQuery('#vehicle_registration').val().length <= 0)
	{
		haserror = true;
		jQuery('#vehicle_registration').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter the vehicle registration'});
		return false;
	}
	if (jQuery('#vehicle_number').val().length <= 0)
	{
		haserror = true;
		jQuery('#vehicle_number').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter the vehicle Number'}); 
		return false;
	}
	if (jQuery('#vehicle_make').val().length <= 0)
	{
		haserror = true;
		jQuery('#vehicle_make').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter the vehicle Make'}); 
		return false;
	}
	if (jQuery('#vehicle_model').val().length <= 0)
	{
		haserror = true;
		jQuery('#vehicle_model').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter the vehicle Model'}); 
		return false;
	}
	if (jQuery('#vehicle_year').val().length <= 0)
	{
		haserror = true;
		jQuery('#vehicle_year').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter the vehicle Year'}); 
		return false;
	}
	if (jQuery('#request_date').val().length <= 0)
	{
		haserror = true;
		jQuery('#request_date').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter the request date'}); 
		return false;
	}
	else
	{
		if (!check_dob(jQuery('#request_date').val()))
		{
			haserror = true;
			jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'The request date does not appear valid.  Format: YYYY-MM-DD.  Minimum year is 1900.'});
			jQuery('#request_date').addClass('error');
			return false;
		}
	}
	if (jQuery('#vehicle_value').val().length <= 0)
	{
		haserror = true;
		jQuery('#vehicle_value').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter the value'}); 
		return false;
	}

	if (jQuery('#user_account').val().length <= 0)
	{
		haserror = true;
		jQuery('#user_account').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter customer account'}); 
		return false;
	}

	if (jQuery('#usedfor').val().length <= 0)
	{
		haserror = true;
		jQuery('#usedfor').addClass('error');
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: 'Please enter used for value'}); 
		return false;
	}
	
	if (jQuery('#fileuploader_iframe').contents().find('.files .template-download .preview').html() == undefined)
	{
		haserror = true;
		jQuery.growl.error({size: 'large', duration: 4000, title: phrase['_error'], message: phrase['_please_upload_atleast_one_image'] });
	    smoothscroll('item-pictures');  
	    return false;
	}

	if (!haserror)
	{
		return true;
	}
	return false;
}

function check_dob(str)
{
	// STRING FORMAT yyyy-mm-dd
	if (str=="" || str==null){return false;}

	// m[1] is year 'YYYY' * m[2] is month 'MM' * m[3] is day 'DD'
	var m = str.match(/(\d{4})-(\d{2})-(\d{2})/);

	// STR IS NOT FIT m IS NOT OBJECT
	if ( m === null || typeof m !== 'object'){return false;}

	// CHECK m TYPE
	if (typeof m !== 'object' && m !== null && m.size!==3){return false;}

	var ret = true;
	var thisYear = new Date().getFullYear();
	var minYear = 1900;

	// YEAR CHECK
	if ( (m[1].length < 4) || m[1] < minYear || m[1] > thisYear){ret = false;}
	// MONTH CHECK
	if ( (m[2].length < 2) || m[2] < 1 || m[2] > 12){ret = false;}
	// DAY CHECK
	//if( (m[3].length < 2) || m[3] < 1 || m[3] > 31){ret = false;}
	// DAY CHECK
	if ( (m[3].length < 2) || m[3] < 1 || ((["04","06","09","11"].indexOf(m[2]) > -1 && m[3] > 30 ) || m[3] > 31)){ret = false;}
	// FEBRUARY CHECK
	if ( (m[2] == 2 && m[3] > 29) || (m[2] == 2 && m[3] > 28 && m[1]%4 != 0)){ret = false;}
	return ret;
}