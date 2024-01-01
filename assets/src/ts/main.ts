document.addEventListener('DOMContentLoaded', function() {


	(window as any).setBootstrapTheme = function(name:string) {
		document.body.parentElement.setAttribute('data-bs-theme', name);
	};


	(window as any).checkToggle = function(checkbox:HTMLInputElement, toggleClass:string, targetSelector:string) {
		const target = document.querySelector(targetSelector) as HTMLElement;
		const checked = checkbox.checked === true;
		console.log("Checked", checked);
		if (checked) {
			target.classList.add(toggleClass);
		}
		else {
			target.classList.remove(toggleClass);
		}
	}


	console.log("Welcome to Mosaic CMS.");

});