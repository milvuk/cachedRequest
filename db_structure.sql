# run (import) this script on your MySql server to create necessary database table for caching
CREATE TABLE cached_requests (
    url TEXT,
    response MEDIUMTEXT,
    time_cached DATETIME
) DEFAULT CHARACTER SET utf8 ENGINE=InnoDB;