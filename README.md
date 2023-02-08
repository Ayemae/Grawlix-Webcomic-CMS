Grawlix is a content management system (CMS) for webcomics.

REQUIREMENTS
---

To install Grawlix, you will need a web host with support for:
- MySQL
- PHP 7.0+

A version of Grawlix for PHP 5.x is also available, see the `php5-compatible` branch.

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

HOW TO UPDATE
---

If you already have Grawlix installed, you should be able to just upload the new files to update. However, make sure you don't overwrite the files you've customised, these include:
* `.htaccess`/`htaccess.txt`
* `config.php`, if you overwrite this one, Grawlix won't be able to access the database!

Either don't upload or delete `firstrun.php`, this file is only used for installing Grawlix the first time.
If you're upgrading from version 1.2 or older, run `_admin/_upgrade-to-1.3x.php`, and then delete it when you're done. If you're upgrading from 1.3 or newer, then you don't need to run it and can delete it, or skip uploading it.

If you're using Disqus comments and updating from 1.5 or earlier, you may need to change `assets/snippets/snippet.comments.php` and comment out the line that sets the disqus_identifier to avoid breaking your existing comments. Unfortunately, prior to 1.6, the disqus_identifier wasn't set, causing Disqus to use the URL instead of the page ID, which would cause comments to get misaligned if you deleted pages.

If you run into issues with your archive being displayed incorrectly, go into your archive settings, change the layout to something else, save, then change it back to what you want and save.
