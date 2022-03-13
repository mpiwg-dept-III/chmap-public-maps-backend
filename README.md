# public maps backend

This project contains a php file and a postgresql database backup file. 

## Restore the database

After connected a postgresql database server, 
creating a database user and database with the following commands:
```
CREATE USER postgis WITH PASSWORD 'changeme';

CREATE DATABASE "postgis"
    WITH 
    OWNER = postgis
    ENCODING = 'UTF8'
    LC_COLLATE = 'en_US.UTF-8'
    LC_CTYPE = 'en_US.UTF-8';
```

Restore the data with the command below: 
```
psql -d postgis < postgis.backup
```

## Deploy php file

Deploy publicMaps.php to a web server and make sure the php interpreter is enabled.

## Note

Please do not use the default password  of the database user: postgis.


## License

Licensed under the [GNU GPLv3](LICENSE) license.
