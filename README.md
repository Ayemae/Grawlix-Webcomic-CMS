HOW TO INSTALL
---

1. Download the Grawlix files.
   * To download the files most easily, click the big "Code" button near the top of the Github repository, and select "Download ZIP" from the dropdown menu.
2. Create a MySQL database on your site's host, or elsewhere that you can connect to. Note down the database host, name, username, and password.
3. With an FTP client\*, check your site folder and make sure it is empty. Some files, such as .htaccess, might be hidden by default. On your FTP client, make sure you have it set to show hidden files under View, Settings, or Preferences.
4. Open the Grawlix version folder. This folder contains the subfolders \_admin, \_system, etc. Drag all of the contents of the Grawlix version folder into your main site folder.
   * Among these contents is a file called "htaccess.txt". Change the name of this one to ".htaccess".
6. Once you load up the new Grawlix install, it should redirect you to /firstrun. Enter your database credentials, and your user credentials.
7. Grawlix should guide you from there!

If your new Grawlix site is giving you internal server errors, there's a line in `.htaccess` that you may need to uncomment, specifically:
``RewriteBase /``
Again, you may need to 'Show Hidden Files' in order to see the `.htaccess` file.

\*FTP CLIENTS: Some hosts provide a web FTP client, but some free ones you can download include [Cyberduck](https://cyberduck.io/) and [Filezilla](https://filezilla-project.org/).
