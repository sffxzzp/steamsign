Steam Sign
======
A Steam Signature Maker,

for Private Use.

An attempt to practice how php draw a pic.

GL.

Usage
======
PHP needs GD and openttf library.

install=init
-----
This should be used when it firstly deployed.

*This will initialize the SQL that used to storage created signatrue pics.*

steamid={steamUser64ID}
------
Must set. Could get the id at [steam profile](http://steamcommunity.com/my/?xml=1).

size=small
------
If not set. It'll create a bigger picture.

*attention: if signatrue is created. it'll automaticly stored into SQL. and it could only be refreshed after about 22 hours. or manually delete.*

delete={steamUser64ID}
------
see steamid.