function navTo(destination) {
   if(destination != "/comic")
       window.location = destination;
}

document.onkeydown = function(e) {
    const prevPage = "<?=show('comic_url_prev')?>";
    const nextPage = "<?=show('comic_url_next')?>";
   if(!e) e = window.event;
   switch (e.keyCode) {
       case 37: //left arrow
           navTo(prevPage);
           break;
       case 39: //right arrow
           navTo(nextPage);
           break;
   }
}