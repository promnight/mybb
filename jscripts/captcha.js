var captcha = {
	init: function()
	{
	},

	refresh: function()
	{
		var imagehash = $('imagehash').value;
		this.spinner = new ActivityIndicator("body", {image: "images/spinner_big.gif"});
		new ajax('xmlhttp.php?action=refresh_captcha&imagehash='+imagehash, {method: 'get', onComplete: function(request) { captcha.refresh_complete(request); }});
		return false;
	},

	refresh_complete: function(request)
	{
		if(request.responseText.match(/<error>(.*)<\/error>/))
		{
			message = request.responseText.match(/<error>(.*)<\/error>/);
			if(!message[1])
			{
				message[1] = "An unknown error occurred.";
			}
			alert('There was an error fetching the new captcha.\n\n'+message[1]);
		}
		else if(request.responseText)
		{
			$('captcha_img').src = "captcha.php?action=regimage&imagehash="+request.responseText;
			$('imagehash').value = request.responseText;
		}
		this.spinner.destroy();
		this.spinner = '';
	}
};