const menuItems = document.getElementById('menu').getElementsByTagName('li');
const newSubmenu = document.createElement("ul");
let lstIndptMenuItem = undefined;



// loop over items in main-menu
for (var i=0; i < menuItems.length; i++) {
    let subItem = undefined;
    // Find menu items starting with '-' or '>'
    if (menuItems[i].getElementsByTagName("a")[0].text.slice(0,1) === ('-' || '>')) {
        subItem = menuItems[i];
        // remove '-' or '>' from link text
        menuItems[i].getElementsByTagName("a")[0].text = menuItems[i].getElementsByTagName("a")[0].text.substring(1);
        // if a menuParent has been assigned...
        if (menuParent) {
            // append subitem to menuParent
            menuParent.appendChild(subItem);
        // if a menuParent has NOT been assigned...
        } else {
            // assign menuParent add a new unordered list to it
            menuParent = lstIndptMenuItem.appendChild(newSubmenu.cloneNode(true));
            // append subitem to new unordered list
            menuParent.appendChild(subItem);
        }
    } else {
        lstIndptMenuItem = menuItems[i];
        menuParent = lstIndptMenuItem[1];
    }
};