# Development of wct2014
![theme of wct2014](/screenshot.png)

First, You will need your own environment of wordpress as development project.

## Setup your local project
On your WordPress "theme" directory,

	git clone git@github.org:vwxyutarooo/wct2014.dev.git wct2014

copy files to your WordPress plugin directory which is in "src/plugins" directory.

Install node_modules

	npm install

Run gulp and there are some options that I was prepared

	npm start

## Default WordPress infomations
user

* Username: admin
* Password: admin


Database

* table_prefix -> wp_
* WP_HOME -> http://2014.wct.dev
* WP_SITEURL -> http://2014.wct.dev
