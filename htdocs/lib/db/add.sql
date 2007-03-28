CREATE TABLE host (
   wwwroot varchar(255) NOT NULL PRIMARY KEY,
   deleted integer NOT NULL DEFAULT 0,
   ip_address varchar(39) NOT NULL DEFAULT '',
   name varchar(80) NOT NULL DEFAULT '',
   public_key text NOT NULL DEFAULT '',
   public_key_expires integer NOT NULL DEFAULT 0,
   portno integer NOT NULL DEFAULT 80,
   last_connect_time integer NOT NULL DEFAULT 0,
   applicationid integer NOT NULL DEFAULT 0
);

