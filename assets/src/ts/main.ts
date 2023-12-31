document.addEventListener('DOMContentLoaded', function() {


	(window as any).setBootstrapTheme = function(name:string) {
		document.body.parentElement.setAttribute('data-bs-theme', name);
	};


	console.log("Welcome to Mosaic CMS.");

});