# RepoChron - a git and Laravel based versioning and Wayback tool

## About RepoChron

The purpose of this lightweight app is to provide versions of static files (especially JSONs) in an easy human-readable form. This is achieved by using the concept of a wayback machine with standard timestamps. It is the most intuitive way to retrieve the content of a file at any point in time. Using the timestamp as a URL parameter allows for easy citation.  

The app is based on the [Laravel framework](https://laravel.com/) and [git](https://git-scm.com/).  

**Important note:** Even though this build is already working, it is more of a proof of concept and not intended for practical use yet.  

## Installation

* RepoChron requires a Linux Environment!
* clone git repo
* execute "composer install" in project root directory (beware: RepoChron requires PHP8.0)
* set permissions for storage directory
* create .env file
* server setup ([Laravel Deployment](https://laravel.com/docs/8.x/deployment))

* create a directory (e.g. data) on the same level as the project root directory (or anywhere else)
* Customize config/repochron/path to your needs
* execute "git init" in the data directory
* if you have no data for RepoChron to handle create something like "testdirectory" and put a "test.json" with some values in it
* commit the file to git ("git add .", "git commit")
* set a symbolic link to PROJECT-ROOT/public/storage
* check setup by typing "BASE-URL/api/testdirectory/test.json::log" in your Browser

## Future Plans

* Artisan Command Script to setup data directory and initialize git.
* Move all individual parameters to .env
* Add Landing Page and Documentation
* Integrate interface to create and commit new versions of files
* Integrate ElasticSearch and extend API to allow file and fulltext search

## License

Author: [Jan Köster](https://orcid.org/0000-0003-2713-5207)  
RepoChron is open-sourced software licensed under the [GPLv3](http://www.gnu.org/licenses/gpl-3.0.en.html).   
     
The [Laravel framework](https://laravel.com/) is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).    
[git](https://git-scm.com/) is open-sourced software licensed under the [GPLv2](http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html).  
