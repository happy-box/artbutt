# Artbutt
Artbutt a fork of the old deerkins project.  It allows users to produce beautiful ART in a web browser to display in an IRC channel.

## Installation
There are probably various places that needs a MySQL connection to connect with deer. You need to supply a db yourself. Import the dumped .sql-files in ``/sql`` to get started.

Everything in ``/deeritor`` should be put on a web server. In ``deer.class.php`` you need to provide your connection details.

You can run the IRC bot via php artbutt.php -c devhax.conf

## Demo
Artbutt can (at least now) be found at http://art.devhax.com/ and is the place where anyone can create art.