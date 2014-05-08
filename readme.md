# Development of wct2014
First, You will need your own environment of wordpress as development project.

## Setup your local project
On your WordPress "theme" directory,

	git clone git@github.org:vwxyutarooo/wct2014.dev.git wct2014  

Move files to your WordPress plugin directory which is in "plugin" folder and enable them.  
Set up gulp if you haven't

	npm install -g gulp

Install node_modules

	npm install

Run gulp and there are some options that I was prepared

	gulp

Open static server' url in your default browser, I supposed that will be used with "Jade".

	gulp begin


##Default WordPress infomations
Default user

* Username: admin
* Password: admin


Default db values

* DB_NAME -> wordpress
* DB_USER -> wordpress
* DB_PASSWORD -> wordpress
* DB_HOST -> localhost
* WP_HOME -> http://2014.wct.dev
* WP_SITEURL -> http://2014.wct.dev
