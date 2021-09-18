const swipeArea = document.getElementById("swipenav");
let startX = 0; let startY = 0; let distX = 0; let distY = 0; let swipeCompletion = 0;
const comicImg = document.getElementById("comic");
const navArrows = document.getElementById("swipenav-arrow-wrapper");
let archiveEnd = "/comic";
let destination = undefined;

swipeArea.addEventListener('touchstart', function(e){	
	let swipeDist = Math.round(swipeArea.clientWidth * .33);
	let t = e.changedTouches[0]; // reference for first touch point
	startX = parseInt(t.clientX);
	startY = parseInt(t.clientY);

	function navTo(destination) { if(destination) window.location = destination; };
	function reset() { distX = 0; swipeCompletion = 0; comicImg.style.left = "0px";};

	swipeArea.addEventListener('touchmove', function(e2){
		let t = e2.changedTouches[0]; // reference first touch point for touchmove
		//check location of swipe area within viewport; is it zoomed in?
		let bounding = swipeArea.getBoundingClientRect();
		distX = parseInt(t.clientX) - startX;
		distY = parseInt(t.clientY) - startY;
		// only operate the following if there's only one touch point
		if (e2.targetTouches.length === 1 && e2.changedTouches.length === 1 ) {
			let opacityOffset = Math.round(swipeDist*.5);
			if (distX <= 0 && bounding.right <= window.innerWidth) { destination = nextPage;}
			else if (distX > 0 && bounding.left >= 0) {destination = prevPage;}
			else {destination = archiveEnd};
			if (destination != archiveEnd) {
				swipeCompletion = ((Math.abs(distX) - opacityOffset) / opacityOffset);
				navArrows.style.opacity = swipeCompletion;
				if (Math.abs(distY) < (Math.abs(distX)*0.45) && Math.abs(distX) < swipeDist) {
				comicImg.style.left = `${distX}px`;};
			}   
		} else {
			distX = 0;
		}
	}, false);  

	swipeArea.addEventListener('touchend', function(e3){
		//if a link to the next page exists, disable it after touch
		const comicImageLink = comicImg.getElementsByTagName("a")[0];
		if (comicImageLink) { comicImageLink.addEventListener('click', function(clickEv){
			clickEv.preventDefault();});};
		//navigate to destination if there is one and the swipe has been completeled
		if (destination !=archiveEnd && swipeCompletion >= 1) {navTo(destination);}
		//reset swipe values if no redirect
		else {reset();} 
	}, false);
	swipeArea.addEventListener('touchcancel', function(e4){
		reset();
	}, false); 
}, false);
