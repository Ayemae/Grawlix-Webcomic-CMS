<!-- GRAWLIX TEMPLATE: This comes from snippet.swipenav -->

<style>
#swipenav-arrows {
	background-color: black; 
	display: flex;
	justify-content: center;
	align-items: center;
	position: absolute;
	top:0px; 
	right: 0px; 
	left: unset; 
	height: 100%; 
	width: 0px;
	overflow: hidden;
}
#swipenav-arrow-wrapper {
	position: relative;
	width: 90%;
	height: 50%;
	background: radial-gradient(ellipse at center, rgba(255,255,255,0) 0%, rgba(255,255,255,0) 100%, rgba(255,255,255,0) 100%);
}
.swipenav-arrow {
	opacity: 0;
	margin: auto;
	position: absolute;
	top: 0; left: 0; bottom: 0; right: 0;
	width: 0; 
	height: 0; 
	border-top: 40px solid transparent;
	border-bottom: 40px solid transparent;
}
</style>

<div id="swipenav-arrows">
		<div id="swipenav-arrow-wrapper">
			<div id="swipenav-arrow-next" class="swipenav-arrow" style="border-left: 40px solid #e5e5e5;">
			</div>
			<div id="swipenav-arrow-prev" class="swipenav-arrow" style="border-right: 40px solid #e5e5e5;">
			</div>
		</div>
	</div>
<script>


window.addEventListener('load', function(){

	let startX = 0; let startY = 0; let distX = 0; let distY = 0; let arrowOpacity = 0;
	let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0); 
	let fullSwipe = Math.round(vw*.25);
	const arrowBox = document.getElementById("swipenav-arrows");
	const arrowNext = document.getElementById("swipenav-arrow-next");
	const arrowPrev = document.getElementById("swipenav-arrow-prev");
	const pulse = document.getElementById("swipenav-arrow-wrapper");
	const prevPage = "<?=show('comic_url_prev')?>";
	const nextPage = "<?=show('comic_url_next')?>";
	let destination = undefined;
	const holdTime = 775;

	function navTo(destination) {
		if(destination != "/comic")
			window.location = destination;
		};
  
     document.addEventListener('touchstart', function(e){
         let touchobj = e.changedTouches[0]; // reference for where the touch started
         startX = parseInt(touchobj.clientX);
         startY = parseInt(touchobj.clientY);
     }, false);
  
     document.addEventListener('touchmove', function(e){
		let timer;
		let touchobj = e.changedTouches[0]; // reference first touch point for this event
		distX = parseInt(touchobj.clientX) - startX;
		distY = parseInt(touchobj.clientY) - startY;
		let opacityOffset = (Math.abs(distX) - (Math.round(fullSwipe*.5)));
		arrowOpacity = (((opacityOffset * 100 ) / fullSwipe) / 100);
		if (arrowOpacity > 1) {arrowOpacity = 1;}
		if (distX < 0) {
			// Start nav to 'next'
			arrowBox.style.left = `unset`; arrowBox.style.right = `0px`; 
			destination = nextPage;
			if (nextPage != "/comic") {
			arrowPrev.style.opacity = 0; arrowNext.style.opacity = arrowOpacity; 
			};
		}
		else {
			// Start nav to 'previous'
			arrowBox.style.right = `unset`; arrowBox.style.left = `0px`;
			destination = prevPage;
			if (prevPage != "/comic") {
			arrowNext.style.opacity = 0; arrowPrev.style.opacity = arrowOpacity;
			};
		};
		distX = Math.abs(distX); 
		distY = Math.abs(distY);         
		if (distY < (distX*0.5) && distX < fullSwipe) {
			arrowBox.style.width = `${distX}px`;};
		if (arrowOpacity === 1) {timer = window.setTimeout(() => {
			console.log("Timeout fired")
			let pulsePos = 100;
			let pulseOpacity = (1 - (pulsePos / 1000)).toFixed(3);
			let perOfHoldTime = Math.round(holdTime / 100);
			setInterval(() => {
				if (pulsePos > 0) pulsePos--;
				console.log(pulsePos);
				document.getElementById("swipenav-arrow-wrapper").style.background = `radial-gradient(ellipse at center, rgba(255,255,255,0) 0%,rgba(255,255,255,0.8) ${pulsePos}%,rgba(255,255,255,0) 100%)`;
			}, 25);
			navTo(destination);
		}, holdTime);
		} else {window.clearTimeout(timer);};
		document.addEventListener('touchend', function(e){
			distX = 0;
			arrowBox.style.width = "0px";
			arrowOpacity = 0;
			window.clearTimeout(timer);
     	}, false); 
	
	}, false);  
 }, false);
  

</script>