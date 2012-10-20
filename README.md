Smally
======

Small(y) and basic Php MVC framework for quick prototyping  
<i>sample-app might not be up to date, sorry for that ...</i>

Quick Install
-------------

Smally rely on PHP 5.3+ (with short_tag) behind an Apache server with mod_rewrite.  
<i>Actually other httpd service must be compatible but not tested.</i> 

1. Clone Smally from Github in your Apache folder
> git clone git://github.com/lixus3d/Smally.git  

2. Access to the public folder in your favorite browser  
> http://localhost/Smally/sample-app/public/

3. Done, that's all !

Install for multiple projects
-----------------------------

1. Clone Smally from Github in a library folder in your Apache folder
> mkdir library  
> cd library  
> git clone git://github.com/lixus3d/Smally.git  

2. Copy the sample-app folder to a project name folder in the Apache folder  
> cd Smally  
> cp -r ./sample-app ../../my-project  

3. Edit the library path in my-project boot.php  
> cd ../../my-project  
> vim public/boot.php  
> defined('LIBRARY_PATH') || define('LIBRARY_PATH',ROOT_PATH.<strong>'../library/'</strong>);  

4. Access to the public folder of my-project in your favorite browser  
> http://localhost/my-project/public/  


Todo
----
- Mail possibilities : 
	- Mailer class 
	- Mail spool system
- Form :
	- Add others type of field :
		- color 
		- other value object and objects  
- Validator :
	- Add others type of validator :
		- numeric
		- alpha 
		- alpha and numeric 
- Dao :
	- Add array values store and fetch possibility 
	- Add gestion of join in Criteria
- Metas :
	- A metas "config" file 